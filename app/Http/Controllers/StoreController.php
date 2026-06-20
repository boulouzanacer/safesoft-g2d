<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Cmd1;
use App\Models\Cmd2;
use App\Models\Commune;
use App\Models\Fournisseur;
use App\Models\Produit;
use App\Models\Wilaya;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StoreController extends Controller
{
    private function currentClient(): ?Client
    {
        if (session('role') !== 'client' || ! session()->has('client_id')) {
            return null;
        }

        return Client::query()->find((int) session('client_id'));
    }

    private function tarifForClient(?Client $client): int
    {
        if (! $client || (string) $client->type_client !== 'abonne') {
            return 1;
        }

        $t = (int) ($client->tarif ?? 1);
        if ($t < 1 || $t > 3) {
            $t = 1;
        }

        return $t;
    }

    private function resolveUrl(?string $raw): string
    {
        $v = trim((string) $raw);
        if ($v === '') {
            return '';
        }

        $lower = strtolower($v);
        if (str_starts_with($lower, 'http://') || str_starts_with($lower, 'https://')) {
            return $v;
        }

        if (str_starts_with($v, '//')) {
            return request()->getScheme().':'.$v;
        }

        if (str_starts_with($v, '/')) {
            return url($v);
        }

        return url('/'.$v);
    }

    private function cart(): array
    {
        $cart = session('cart', []);
        return is_array($cart) ? $cart : [];
    }

    private function fournisseurClientMap(?Client $client): array
    {
        static $cache = [];

        if (! $client) {
            return [];
        }

        $key = (string) $client->id;
        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        $map = [];

        if ((string) $client->type_client === 'abonne' && $client->id_frs) {
            $map[(int) $client->id_frs] = $client;
        }

        if ((string) $client->type_client === 'simple') {
            $supplierClients = Client::query()
                ->where('email', $client->email)
                ->where('type_client', 'abonne')
                ->whereNotNull('id_frs')
                ->where('actif', 1)
                ->get();

            foreach ($supplierClients as $supplierClient) {
                $map[(int) $supplierClient->id_frs] = $supplierClient;
            }
        }

        return $cache[$key] = $map;
    }

    private function fournisseurClientFor(?Client $client, int $frsId): ?Client
    {
        if ($frsId <= 0) {
            return null;
        }

        $map = $this->fournisseurClientMap($client);

        return $map[$frsId] ?? null;
    }

    private function abonneFournisseurIds(?Client $client): array
    {
        return array_map('intval', array_keys($this->fournisseurClientMap($client)));
    }

    private function isAbonneFor(?Client $client, int $frsId): bool
    {
        return $this->fournisseurClientFor($client, $frsId) !== null;
    }

    private function cartFournisseurId(): ?int
    {
        $id = session('cart_frs_id');
        if ($id === null || $id === '') {
            return null;
        }
        return (int) $id;
    }

    private function setCart(array $cart, ?int $frsId): void
    {
        session(['cart' => $cart]);
        if ($frsId) {
            session(['cart_frs_id' => $frsId]);
        } else {
            session()->forget('cart_frs_id');
        }
    }

    private function resolveOrderClient(Client $client, int $frsId): Client
    {
        if ((string) $client->type_client === 'abonne' && (int) ($client->id_frs ?? 0) === $frsId) {
            return $client;
        }

        $supplierClient = Client::findForFournisseurByEmail($frsId, (string) $client->email);

        $payload = [
            'nom' => $client->nom,
            'prenom' => $client->prenom,
            'email' => $client->email,
            'password' => $client->password,
            'telephone' => $client->telephone,
            'adresse' => $client->adresse,
            'id_wilaya' => (int) $client->id_wilaya,
            'id_commune' => (int) $client->id_commune,
            'id_frs' => $frsId,
            'actif' => 1,
        ];

        if ($supplierClient) {
            $supplierClient->update($payload);

            return $supplierClient->fresh();
        }

        return Client::create($payload + [
            'type_client' => 'simple',
            'tarif' => 1,
            'email_verified_at' => $client->email_verified_at ?? now(),
        ]);
    }

    private function cartSummary(): array
    {
        $client = $this->currentClient();
        $abonneFournisseurIds = $this->abonneFournisseurIds($client);
        $cart = $this->cart();
        $ids = array_keys($cart);
        $ids = array_map('intval', $ids);
        $ids = array_values(array_filter($ids, fn ($v) => $v > 0));

        if (count($ids) === 0) {
            return ['items' => [], 'total' => 0.0, 'frs' => null];
        }

        $products = Produit::query()
            ->whereNull('deleted_at')
            ->where('actif', 1)
            ->where(function ($q) use ($abonneFournisseurIds) {
                $q->where('abonne_only', 0);

                if (count($abonneFournisseurIds) > 0) {
                    $q->orWhereIn('id_frs', $abonneFournisseurIds);
                }
            })
            ->whereIn('id', $ids)
            ->with(['fournisseur:id,nom_frs,actif,is_visible,deleted_at', 'quantityPrices'])
            ->get()
            ->keyBy('id');

        $items = [];
        $total = 0.0;

        foreach ($ids as $id) {
            $p = $products->get($id);
            if (! $p || ! $p->fournisseur || (int) $p->fournisseur->actif !== 1 || $p->fournisseur->deleted_at) {
                unset($cart[$id]);
                continue;
            }

            $isVisible = (int) ($p->fournisseur->is_visible ?? 1) === 1;
            if (! $isVisible && ! in_array((int) $p->fournisseur->id, $abonneFournisseurIds, true)) {
                unset($cart[$id]);
                continue;
            }

            $qty = (int) ($cart[$id] ?? 0);
            if ($qty <= 0) {
                unset($cart[$id]);
                continue;
            }

            $qty = min($qty, (int) $p->stock);
            if ($qty <= 0) {
                unset($cart[$id]);
                continue;
            }

            $cart[$id] = $qty;

            $pricingClient = $this->fournisseurClientFor($client, (int) $p->id_frs);
            $prixUnitaire = (float) $p->prixUnitairePourQuantite($pricingClient, $qty);
            $line = $prixUnitaire * $qty;
            $total += $line;

            $items[] = [
                'produit' => $p,
                'qty' => $qty,
                'line_total' => $line,
                'prix_unitaire' => $prixUnitaire,
                'image' => $this->resolveUrl($p->image_principale),
            ];
        }

        $frs = null;
        $frsId = $this->cartFournisseurId();
        if ($frsId) {
            $allowInvisible = in_array((int) $frsId, $abonneFournisseurIds, true);
            $frs = Fournisseur::query()
                ->where('id', $frsId)
                ->where('actif', 1)
                ->when(! $allowInvisible, fn ($q) => $q->where('is_visible', 1))
                ->whereNull('deleted_at')
                ->first(['id', 'nom_frs', 'logo_path', 'adresse', 'telephone', 'id_wilaya', 'id_commune', 'latitude', 'longitude']);
        }

        $this->setCart($cart, $frsId);

        return ['items' => $items, 'total' => $total, 'frs' => $frs];
    }

    public function index(Request $request): View
    {
        $client = $this->currentClient();
        $abonneFournisseurIds = $this->abonneFournisseurIds($client);
        $forcedFournisseurId = ($client && $client->type_client === 'abonne' && $client->id_frs)
            ? (int) $client->id_frs
            : null;

        $q = trim((string) $request->query('q', ''));
        $f = $request->query('f');
        $categorie = trim((string) $request->query('categorie', ''));

        $fournisseurId = $forcedFournisseurId ?: (($f !== null && $f !== '') ? (int) $f : null);

        $boutiques = Fournisseur::query()
            ->where('actif', 1)
            ->when(! $forcedFournisseurId, function ($q) use ($abonneFournisseurIds) {
                $q->where(function ($sub) use ($abonneFournisseurIds) {
                    $sub->where('is_visible', 1);

                    if (count($abonneFournisseurIds) > 0) {
                        $sub->orWhereIn('id', $abonneFournisseurIds);
                    }
                });
            })
            ->whereNull('deleted_at')
            ->when($fournisseurId, fn ($q) => $q->where('id', $fournisseurId))
            ->orderBy('nom_frs')
            ->get(['id', 'nom_frs', 'logo_path', 'telephone', 'adresse', 'id_wilaya', 'id_commune', 'latitude', 'longitude']);

        $produitsQuery = Produit::query()
            ->whereNull('deleted_at')
            ->where('actif', 1)
            ->where(function ($q) use ($abonneFournisseurIds) {
                $q->where('abonne_only', 0);

                if (count($abonneFournisseurIds) > 0) {
                    $q->orWhereIn('id_frs', $abonneFournisseurIds);
                }
            })
            ->with(['fournisseur:id,nom_frs,actif,is_visible,deleted_at', 'quantityPrices'])
            ->whereHas('fournisseur', function ($q) use ($forcedFournisseurId, $abonneFournisseurIds) {
                $q->where('actif', 1)
                    ->whereNull('deleted_at')
                    ->where(function ($sub) use ($forcedFournisseurId, $abonneFournisseurIds) {
                        $sub->where('is_visible', 1);
                        if ($forcedFournisseurId) {
                            $sub->orWhere('id', $forcedFournisseurId);
                        } elseif (count($abonneFournisseurIds) > 0) {
                            $sub->orWhereIn('id', $abonneFournisseurIds);
                        }
                    });
            })
            ->when($fournisseurId, fn ($q) => $q->where('id_frs', $fournisseurId))
            ->when($categorie !== '', fn ($q2) => $q2->where('categorie', $categorie))
            ->when($q !== '', function ($q2) use ($q) {
                $q2->where(function ($sub) use ($q) {
                    $sub->where('designation', 'like', "%{$q}%")
                        ->orWhere('reference', 'like', "%{$q}%")
                        ->orWhere('categorie', 'like', "%{$q}%");
                });
            });

        $catsQuery = Produit::query()
            ->whereNull('deleted_at')
            ->where('actif', 1)
            ->where(function ($q) use ($abonneFournisseurIds) {
                $q->where('abonne_only', 0);

                if (count($abonneFournisseurIds) > 0) {
                    $q->orWhereIn('id_frs', $abonneFournisseurIds);
                }
            })
            ->whereHas('fournisseur', function ($q) use ($forcedFournisseurId, $abonneFournisseurIds) {
                $q->where('actif', 1)
                    ->whereNull('deleted_at')
                    ->where(function ($sub) use ($forcedFournisseurId, $abonneFournisseurIds) {
                        $sub->where('is_visible', 1);
                        if ($forcedFournisseurId) {
                            $sub->orWhere('id', $forcedFournisseurId);
                        } elseif (count($abonneFournisseurIds) > 0) {
                            $sub->orWhereIn('id', $abonneFournisseurIds);
                        }
                    });
            })
            ->when($fournisseurId, fn ($q2) => $q2->where('id_frs', $fournisseurId))
            ->distinct()
            ->orderBy('categorie')
            ->pluck('categorie')
            ->filter()
            ->values();

        $produits = $produitsQuery
            ->orderByDesc('created_at')
            ->paginate(18)
            ->withQueryString();

        $cartSummary = $this->cartSummary();

        return view('store.index', [
            'title' => 'Boutiques & Produits',
            'client' => $client,
            'forced_fournisseur_id' => $forcedFournisseurId,
            'boutiques' => $boutiques,
            'produits' => $produits,
            'categories' => $catsQuery,
            'selected_f' => $fournisseurId,
            'selected_categorie' => $categorie,
            'q' => $q,
            'cart_total' => $cartSummary['total'],
            'cart_count' => count($cartSummary['items']),
        ]);
    }

    public function boutique(int $id, Request $request): View
    {
        $client = $this->currentClient();
        $abonneFournisseurIds = $this->abonneFournisseurIds($client);
        if ($client && $client->type_client === 'abonne' && $client->id_frs && (int) $client->id_frs !== $id) {
            abort(403);
        }

        $boutique = Fournisseur::query()
            ->where('id', $id)
            ->where('actif', 1)
            ->when(! in_array($id, $abonneFournisseurIds, true), fn ($q) => $q->where('is_visible', 1))
            ->whereNull('deleted_at')
            ->firstOrFail();

        $q = trim((string) $request->query('q', ''));
        $categorie = trim((string) $request->query('categorie', ''));

        $cats = Produit::query()
            ->whereNull('deleted_at')
            ->where('actif', 1)
            ->where('id_frs', $id)
            ->when(! in_array($id, $abonneFournisseurIds, true), fn ($q2) => $q2->where('abonne_only', 0))
            ->distinct()
            ->orderBy('categorie')
            ->pluck('categorie')
            ->filter()
            ->values();

        $produits = Produit::query()
            ->whereNull('deleted_at')
            ->where('actif', 1)
            ->where('id_frs', $id)
            ->when(! in_array($id, $abonneFournisseurIds, true), fn ($q2) => $q2->where('abonne_only', 0))
            ->with('quantityPrices')
            ->when($categorie !== '', fn ($q2) => $q2->where('categorie', $categorie))
            ->when($q !== '', function ($q2) use ($q) {
                $q2->where(function ($sub) use ($q) {
                    $sub->where('designation', 'like', "%{$q}%")
                        ->orWhere('reference', 'like', "%{$q}%")
                        ->orWhere('categorie', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(18)
            ->withQueryString();

        $cartSummary = $this->cartSummary();

        return view('store.boutique', [
            'title' => $boutique->nom_frs,
            'client' => $client,
            'boutique' => $boutique,
            'produits' => $produits,
            'categories' => $cats,
            'selected_categorie' => $categorie,
            'q' => $q,
            'cart_total' => $cartSummary['total'],
            'cart_count' => count($cartSummary['items']),
        ]);
    }

    public function produit(int $id): View
    {
        $client = $this->currentClient();

        $p = Produit::query()
            ->whereNull('deleted_at')
            ->where('actif', 1)
            ->with(['images' => fn ($q) => $q->orderBy('ordre'), 'fournisseur:id,nom_frs,actif,is_visible,deleted_at', 'quantityPrices'])
            ->findOrFail($id);

        $abonneForProduct = $this->isAbonneFor($client, (int) $p->id_frs);

        if (! $p->fournisseur || (int) $p->fournisseur->actif !== 1 || (int) ($p->fournisseur->is_visible ?? 1) !== 1 || $p->fournisseur->deleted_at) {
            $allowed = $abonneForProduct;
            if (! $allowed) {
                abort(404);
            }
        }

        if ($client && $client->type_client === 'abonne' && $client->id_frs && (int) $client->id_frs !== (int) $p->id_frs) {
            abort(403);
        }

        if (! $abonneForProduct) {
            if ((int) ($p->abonne_only ?? 0) === 1) {
                abort(404);
            }
        }

        $images = [];
        $main = $this->resolveUrl($p->image_principale);
        if ($main !== '') {
            $images[] = $main;
        }
        foreach ($p->images as $img) {
            $u = $this->resolveUrl($img->url_principale);
            if ($u !== '') {
                $images[] = $u;
            }
        }
        $images = array_values(array_unique($images));

        $cartSummary = $this->cartSummary();

        $tierModels = $p->relationLoaded('quantityPrices')
            ? $p->quantityPrices
            : $p->quantityPrices()->get(['quantity_min', 'quantity_max', 'price']);

        $tiers = $tierModels
            ->map(fn ($t) => [
                'quantity_min' => (int) $t->quantity_min,
                'quantity_max' => $t->quantity_max === null ? null : (int) $t->quantity_max,
                'price' => (float) $t->price,
            ])
            ->values()
            ->all();

        $tierEnabled = $p->isTierPricingEnabled() && count($tiers) > 0;
        $initialQty = 1;
        $initialUnit = (float) $p->prixUnitairePourQuantite($this->fournisseurClientFor($client, (int) $p->id_frs), $initialQty);

        return view('store.produit', [
            'title' => $p->designation,
            'client' => $client,
            'produit' => $p,
            'images' => $images,
            'tiers' => $tiers,
            'tierEnabled' => $tierEnabled,
            'initialQty' => $initialQty,
            'initialUnit' => $initialUnit,
            'cart_total' => $cartSummary['total'],
            'cart_count' => count($cartSummary['items']),
        ]);
    }

    public function panier(): View
    {
        $client = $this->currentClient();
        $summary = $this->cartSummary();

        return view('store.panier', [
            'title' => 'Panier',
            'client' => $client,
            'items' => $summary['items'],
            'total' => $summary['total'],
            'boutique' => $summary['frs'],
        ]);
    }

    public function panierAdd(Request $request): RedirectResponse
    {
        $client = $this->currentClient();
        $data = $request->validate([
            'produit_id' => ['required', 'integer', 'min:1'],
            'qty' => ['nullable', 'integer', 'min:1'],
        ]);

        $qty = isset($data['qty']) ? (int) $data['qty'] : 1;

        $p = Produit::query()
            ->whereNull('deleted_at')
            ->where('actif', 1)
            ->with(['fournisseur:id,actif,is_visible,deleted_at'])
            ->findOrFail((int) $data['produit_id']);

        if (! $p->fournisseur || (int) $p->fournisseur->actif !== 1 || $p->fournisseur->deleted_at) {
            return back()->with('error', 'Produit indisponible.');
        }

        $allowedInvisible = $this->isAbonneFor($client, (int) $p->id_frs);
        if ((int) ($p->fournisseur->is_visible ?? 1) !== 1 && ! $allowedInvisible) {
            return back()->with('error', 'Produit indisponible.');
        }

        if (! $this->isAbonneFor($client, (int) $p->id_frs)) {
            if ((int) ($p->abonne_only ?? 0) === 1) {
                return back()->with('error', 'Produit réservé aux abonnés.');
            }
        }

        if ((int) $p->stock <= 0) {
            return back()->with('error', 'Produit en rupture de stock.');
        }

        $cart = $this->cart();
        $currentFrsId = $this->cartFournisseurId();
        $newFrsId = (int) $p->id_frs;

        if ($currentFrsId && $currentFrsId !== $newFrsId) {
            $cart = [];
            $currentFrsId = null;
            session()->flash('info', 'Le panier a été vidé car vous avez changé de boutique.');
        }

        $existing = (int) ($cart[$p->id] ?? 0);
        $next = min($existing + $qty, (int) $p->stock);
        $cart[$p->id] = $next;
        $this->setCart($cart, $newFrsId);

        return back()->with('success', 'Ajouté au panier.');
    }

    public function panierUpdate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'produit_id' => ['required', 'integer', 'min:1'],
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        $cart = $this->cart();
        $id = (int) $data['produit_id'];

        if (! array_key_exists($id, $cart)) {
            return back();
        }

        $p = Produit::query()
            ->whereNull('deleted_at')
            ->where('actif', 1)
            ->find($id);

        if (! $p) {
            unset($cart[$id]);
            $this->setCart($cart, $this->cartFournisseurId());
            return back();
        }

        $qty = min((int) $data['qty'], (int) $p->stock);
        if ($qty <= 0) {
            unset($cart[$id]);
        } else {
            $cart[$id] = $qty;
        }

        $frsId = $this->cartFournisseurId();
        if (count($cart) === 0) {
            $frsId = null;
        }
        $this->setCart($cart, $frsId);

        return back();
    }

    public function panierRemove(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'produit_id' => ['required', 'integer', 'min:1'],
        ]);

        $cart = $this->cart();
        $id = (int) $data['produit_id'];
        unset($cart[$id]);

        $frsId = $this->cartFournisseurId();
        if (count($cart) === 0) {
            $frsId = null;
        }
        $this->setCart($cart, $frsId);

        return back();
    }

    public function panierClear(): RedirectResponse
    {
        session()->forget(['cart', 'cart_frs_id']);
        return back();
    }

    public function checkout(): RedirectResponse|View
    {
        $client = $this->currentClient();
        if (! $client) {
            session(['url.intended' => url('/checkout')]);
            return redirect()->to('/login')->with('error', 'Connectez-vous pour continuer.');
        }

        $summary = $this->cartSummary();
        if (count($summary['items']) === 0) {
            return redirect()->to('/panier')->with('error', 'Votre panier est vide.');
        }

        $wilayas = Wilaya::query()->orderBy('ID_WILAYA')->get(['ID_WILAYA', 'WILAYA']);
        $selectedWilaya = (int) ($client->id_wilaya ?? 0);
        if ($selectedWilaya <= 0) {
            $selectedWilaya = (int) ($wilayas->first()?->ID_WILAYA ?? 1);
        }

        $communes = Commune::query()
            ->where('ID_WILAYA', $selectedWilaya)
            ->orderBy('COMMUNE')
            ->get(['ID_COMMUNE', 'COMMUNE', 'ID_WILAYA']);

        return view('store.checkout', [
            'title' => 'Finaliser la commande',
            'client' => $client,
            'items' => $summary['items'],
            'total' => $summary['total'],
            'boutique' => $summary['frs'],
            'wilayas' => $wilayas,
            'communes' => $communes,
            'selected_wilaya' => $selectedWilaya,
        ]);
    }

    public function checkoutStore(Request $request): RedirectResponse
    {
        $client = $this->currentClient();
        if (! $client) {
            session(['url.intended' => url('/checkout')]);
            return redirect()->to('/login')->with('error', 'Connectez-vous pour continuer.');
        }

        $data = $request->validate([
            'adresse_livraison' => ['required', 'string', 'max:255'],
            'id_wilaya' => ['required', 'integer', 'exists:wilaya,ID_WILAYA'],
            'id_commune' => ['required', 'integer', 'exists:commune,ID_COMMUNE'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $summary = $this->cartSummary();
        if (count($summary['items']) === 0) {
            return redirect()->to('/panier')->with('error', 'Votre panier est vide.');
        }

        $frsId = $this->cartFournisseurId();
        if (! $frsId && $client->type_client === 'abonne' && $client->id_frs) {
            $frsId = (int) $client->id_frs;
        }

        if (! $frsId) {
            $first = $summary['items'][0]['produit'] ?? null;
            $frsId = $first ? (int) $first->id_frs : null;
        }

        if (! $frsId) {
            return redirect()->to('/panier')->with('error', 'Impossible de déterminer la boutique.');
        }

        if ($client->type_client === 'abonne' && $client->id_frs && (int) $client->id_frs !== $frsId) {
            return redirect()->to('/panier')->with('error', 'Commande non autorisée.');
        }

        $result = DB::transaction(function () use ($client, $summary, $frsId, $data) {
            $frs = Fournisseur::query()
                ->where('id', $frsId)
                ->where('actif', 1)
                ->when(! $this->isAbonneFor($client, (int) $frsId), fn ($q) => $q->where('is_visible', 1))
                ->whereNull('deleted_at')
                ->first();

            if (! $frs) {
                throw new \RuntimeException('Fournisseur introuvable.');
            }

            $montantTotal = 0.0;
            $lines = [];

            foreach ($summary['items'] as $it) {
                /** @var Produit $p */
                $p = $it['produit'];
                $qty = (int) $it['qty'];

                $pdb = Produit::query()
                    ->where('id', $p->id)
                    ->where('id_frs', $frs->id)
                    ->whereNull('deleted_at')
                    ->where('actif', 1)
                    ->lockForUpdate()
                    ->first();

                if (! $pdb) {
                    throw new \RuntimeException("Produit {$p->id} introuvable.");
                }

                if (! $this->isAbonneFor($client, (int) $frs->id) && (int) ($pdb->abonne_only ?? 0) === 1) {
                    throw new \RuntimeException("Produit {$pdb->id} réservé aux abonnés.");
                }

                if ((int) $pdb->stock < $qty) {
                    throw new \RuntimeException("Stock insuffisant pour {$pdb->designation}.");
                }

                $prixUnitaire = (float) $pdb->prixUnitairePourQuantite($this->fournisseurClientFor($client, (int) $frs->id), $qty);
                $lineTotal = $prixUnitaire * $qty;
                $montantTotal += $lineTotal;

                $lines[] = [
                    'id_produit' => (int) $pdb->id,
                    'quantite' => $qty,
                    'prix_unitaire' => $prixUnitaire,
                    'sous_total' => $lineTotal,
                ];

                $pdb->update(['stock' => (int) $pdb->stock - $qty]);
            }

            $orderClient = $this->resolveOrderClient($client, (int) $frs->id);

            $cmd = Cmd1::create([
                'id_client' => (int) $orderClient->id,
                'id_frs' => (int) $frs->id,
                'date_cmd' => Carbon::now(),
                'statut' => 'en_attente',
                'montant_total' => $montantTotal,
                'adresse_livraison' => $data['adresse_livraison'],
                'id_wilaya' => (int) $data['id_wilaya'],
                'id_commune' => (int) $data['id_commune'],
                'notes' => $data['notes'] ?? null,
                'synced_pme' => 0,
            ]);

            foreach ($lines as $l) {
                Cmd2::create([
                    'id_cmd' => (int) $cmd->id,
                    'id_produit' => (int) $l['id_produit'],
                    'quantite' => (int) $l['quantite'],
                    'prix_unitaire' => (float) $l['prix_unitaire'],
                    'sous_total' => (float) $l['sous_total'],
                ]);
            }

            return $cmd;
        });

        session()->forget(['cart', 'cart_frs_id']);

        return redirect()->to('/mes-commandes/'.$result->id)->with('success', 'Commande créée.');
    }

    public function mesCommandes(): RedirectResponse|View
    {
        $client = $this->currentClient();
        if (! $client) {
            session(['url.intended' => url('/mes-commandes')]);
            return redirect()->to('/login')->with('error', 'Connectez-vous pour continuer.');
        }

        $commandes = Cmd1::query()
            ->leftJoin('frs', 'frs.id', '=', 'cmd1.id_frs')
            ->select(['cmd1.*', 'frs.nom_frs as frs_nom'])
            ->where('cmd1.id_client', $client->id)
            ->orderByDesc('cmd1.date_cmd')
            ->paginate(15);

        return view('store.commandes.index', [
            'title' => 'Mes commandes',
            'client' => $client,
            'commandes' => $commandes,
        ]);
    }

    public function commandeShow(int $id): RedirectResponse|View
    {
        $client = $this->currentClient();
        if (! $client) {
            session(['url.intended' => url('/mes-commandes/'.$id)]);
            return redirect()->to('/login')->with('error', 'Connectez-vous pour continuer.');
        }

        $commande = Cmd1::query()
            ->leftJoin('frs', 'frs.id', '=', 'cmd1.id_frs')
            ->select(['cmd1.*', 'frs.nom_frs as frs_nom'])
            ->where('cmd1.id_client', $client->id)
            ->where('cmd1.id', $id)
            ->firstOrFail();

        $lignes = Cmd2::query()
            ->leftJoin('produit', 'produit.id', '=', 'cmd2.id_produit')
            ->select([
                'cmd2.*',
                'produit.designation as produit_designation',
                'produit.reference as produit_reference',
                'produit.image_principale as produit_image',
            ])
            ->where('cmd2.id_cmd', $commande->id)
            ->orderBy('cmd2.id')
            ->get()
            ->map(function ($l) {
                $l->produit_image_url = $this->resolveUrl($l->produit_image ?? '');
                return $l;
            });

        return view('store.commandes.show', [
            'title' => 'Commande #'.$commande->id,
            'client' => $client,
            'commande' => $commande,
            'lignes' => $lignes,
        ]);
    }
}
