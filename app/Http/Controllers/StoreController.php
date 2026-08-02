<?php

namespace App\Http\Controllers;

use App\Models\BoutiqueCategory;
use App\Models\Client;
use App\Models\Cmd1;
use App\Models\Cmd2;
use App\Models\Commune;
use App\Models\Fournisseur;
use App\Models\Produit;
use App\Models\Wilaya;
use App\Services\ClientBoutiqueManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StoreController extends Controller
{
    public function __construct(private readonly ClientBoutiqueManager $clientBoutiqueManager)
    {
    }

    private function currentClient(): ?Client
    {
        if (session('role') !== 'client' || ! session()->has('client_id')) {
            return null;
        }

        $client = Client::query()->find((int) session('client_id'));
        $globalClient = $this->clientBoutiqueManager->resolveAuthenticatedClient($client);

        if ($globalClient && $client && (int) $globalClient->id !== (int) $client->id) {
            session(['client_id' => (int) $globalClient->id]);
        }

        return $globalClient;
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
        return $this->clientBoutiqueManager->fournisseurClientMap($client);
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
        return $this->clientBoutiqueManager->abonneFournisseurIds($client);
    }

    private function isAbonneFor(?Client $client, int $frsId): bool
    {
        return (string) ($this->fournisseurClientFor($client, $frsId)?->type_client ?? '') === 'abonne';
    }

    private function relatedClientIds(?Client $client): array
    {
        return $this->clientBoutiqueManager->relatedClientIds($client);
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

    private function resolveOrderClient(Client $client, int $frsId): array
    {
        return [
            'client' => $this->clientBoutiqueManager->resolveOrderClient($client, $frsId),
            'session_client_id' => (int) $client->id,
        ];
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
            if (! $isVisible && ! $this->storefrontAllowsInvisible($client, (int) $p->fournisseur->id)) {
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
            $allowInvisible = $this->storefrontAllowsInvisible($client, (int) $frsId);
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

    private function publicBoutiqueCategories()
    {
        return BoutiqueCategory::query()
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'image_path']);
    }

    private function activateStorefront(Fournisseur $boutique): void
    {
        session(['storefront_frs_id' => (int) $boutique->id]);
    }

    private function clearStorefront(): void
    {
        session()->forget('storefront_frs_id');
    }

    private function storefrontBoutiqueFromSession(?Client $client): ?Fournisseur
    {
        $frsId = (int) session('storefront_frs_id', 0);
        if ($frsId <= 0) {
            return null;
        }

        return Fournisseur::query()
            ->where('id', $frsId)
            ->where('actif', 1)
            ->whereNull('deleted_at')
            ->first(['id', 'nom_frs', 'storefront_slug', 'logo_path', 'telephone', 'adresse', 'id_wilaya', 'id_commune', 'latitude', 'longitude']);
    }

    private function requestStorefrontDomainBoutique(): ?Fournisseur
    {
        $boutique = request()->attributes->get('custom_storefront_boutique');

        return $boutique instanceof Fournisseur ? $boutique : null;
    }

    private function boutiqueStorefrontHomeUrl(Fournisseur $boutique, ?string $mode = null): string
    {
        if ($mode === 'domain') {
            return url('/');
        }

        if ($mode === 'market') {
            return url('/boutiques/'.$boutique->id);
        }

        return trim((string) ($boutique->storefront_url ?? '')) !== ''
            ? $boutique->storefront_url
            : url('/boutiques/'.$boutique->id);
    }

    private function productStorefrontUrl(Fournisseur $boutique, int $productId, ?string $mode = null): string
    {
        if ($mode === 'domain') {
            return url('/produits/'.$productId);
        }

        if ($mode === 'market') {
            return url('/produits/'.$productId);
        }

        return trim((string) ($boutique->storefront_slug ?? '')) !== ''
            ? route('storefront.produit', ['slug' => $boutique->storefront_slug, 'id' => $productId])
            : url('/produits/'.$productId);
    }

    private function currentStorefrontState(?Client $client, ?Fournisseur $boutique = null): array
    {
        $customDomainBoutique = $this->requestStorefrontDomainBoutique();
        if ($customDomainBoutique && (! $boutique || (int) $boutique->id === (int) $customDomainBoutique->id)) {
            return [
                'boutique' => $customDomainBoutique,
                'mode' => 'domain',
                'home_url' => $this->boutiqueStorefrontHomeUrl($customDomainBoutique, 'domain'),
            ];
        }

        $sessionBoutique = $this->storefrontBoutiqueFromSession($client);
        if ($sessionBoutique && (! $boutique || (int) $boutique->id === (int) $sessionBoutique->id)) {
            return [
                'boutique' => $sessionBoutique,
                'mode' => 'slug',
                'home_url' => $this->boutiqueStorefrontHomeUrl($sessionBoutique, 'slug'),
            ];
        }

        return [
            'boutique' => null,
            'mode' => null,
            'home_url' => '',
        ];
    }

    private function storefrontAllowsInvisible(?Client $client, int $fournisseurId): bool
    {
        if (in_array($fournisseurId, $this->abonneFournisseurIds($client), true)) {
            return true;
        }

        $state = $this->currentStorefrontState($client);

        return (($state['boutique'] ?? null) instanceof Fournisseur)
            && (int) $state['boutique']->id === $fournisseurId;
    }

    private function storeView(string $view, array $data = [], ?Fournisseur $storefrontBoutique = null, ?string $storefrontMode = null): View
    {
        $client = $data['client'] ?? null;
        $client = $client instanceof Client ? $client : $this->currentClient();
        $themeBoutique = $data['theme_boutique'] ?? null;
        $themeBoutique = $themeBoutique instanceof Fournisseur ? $themeBoutique : null;

        if (! $storefrontMode) {
            $state = $this->currentStorefrontState($client, $storefrontBoutique);
            $storefrontBoutique = $state['boutique'];
            $storefrontMode = $state['mode'];
            $storefrontHomeUrl = $state['home_url'];
        } else {
            $storefrontHomeUrl = $storefrontBoutique
                ? $this->boutiqueStorefrontHomeUrl($storefrontBoutique, $storefrontMode)
                : '';
        }

        $resolvedThemeBoutique = $storefrontBoutique ?? $themeBoutique;
        $storefrontThemeKey = $resolvedThemeBoutique?->storefrontThemeKey() ?? Fournisseur::DEFAULT_STOREFRONT_THEME;
        $storefrontThemeConfig = $resolvedThemeBoutique?->storefrontThemeConfig()
            ?? Fournisseur::storefrontThemeOptions()[Fournisseur::DEFAULT_STOREFRONT_THEME];

        return view($view, $data + [
            'storefront_mode' => $storefrontBoutique !== null,
            'storefront_mode_type' => $storefrontMode,
            'custom_domain_mode' => $storefrontMode === 'domain',
            'storefront_boutique' => $storefrontBoutique,
            'storefront_home_url' => $storefrontHomeUrl,
            'store_theme_boutique' => $resolvedThemeBoutique,
            'storefront_theme_key' => $storefrontThemeKey,
            'storefront_theme_config' => $storefrontThemeConfig,
        ]);
    }

    private function selectedBoutiqueCategoryId(Request $request): ?int
    {
        $value = (int) $request->query('categorie_boutique', 0);

        return $value > 0 ? $value : null;
    }

    private function publicBoutiquesQuery(?Client $client): Builder
    {
        $abonneFournisseurIds = $this->abonneFournisseurIds($client);

        return Fournisseur::query()
            ->with([
                'boutiqueCategory:id,name',
                'customDomains' => fn ($query) => $query
                    ->where('is_active', 1)
                    ->whereNotNull('verified_at')
                    ->orderByDesc('is_primary')
                    ->orderBy('domain'),
            ])
            ->where('actif', 1)
            ->whereNull('deleted_at')
            ->where(function ($sub) use ($abonneFournisseurIds) {
                $sub->where('is_visible', 1);

                if (count($abonneFournisseurIds) > 0) {
                    $sub->orWhereIn('id', $abonneFournisseurIds);
                }
            })
            ;
    }

    private function publicProductsQuery(?Client $client): Builder
    {
        $abonneFournisseurIds = $this->abonneFournisseurIds($client);

        return Produit::query()
            ->whereNull('deleted_at')
            ->where('actif', 1)
            ->where(function ($query) use ($abonneFournisseurIds) {
                $query->where('abonne_only', 0);

                if (count($abonneFournisseurIds) > 0) {
                    $query->orWhereIn('id_frs', $abonneFournisseurIds);
                }
            })
            ->with([
                'fournisseur:id,nom_frs,boutique_category_id,actif,is_visible,deleted_at',
                'fournisseur.boutiqueCategory:id,name',
                'quantityPrices',
            ])
            ->whereHas('fournisseur', function ($query) use ($abonneFournisseurIds) {
                $query->where('actif', 1)
                    ->whereNull('deleted_at')
                    ->where(function ($sub) use ($abonneFournisseurIds) {
                        $sub->where('is_visible', 1);

                        if (count($abonneFournisseurIds) > 0) {
                            $sub->orWhereIn('id', $abonneFournisseurIds);
                        }
                    });
            });
    }

    private function storefrontBoutiqueOrFail(?Client $client, string $slug): Fournisseur
    {
        return Fournisseur::query()
            ->with('boutiqueCategory:id,name')
            ->where('storefront_slug', $slug)
            ->where('actif', 1)
            ->whereNull('deleted_at')
            ->firstOrFail();
    }

    private function renderStorefrontBoutiquePage(Fournisseur $boutique, Request $request, ?Client $client, string $mode): View
    {
        if ($mode !== 'market') {
            $this->activateStorefront($boutique);
        }

        $abonneFournisseurIds = $this->abonneFournisseurIds($client);
        $q = trim((string) $request->query('q', ''));
        $categorie = trim((string) $request->query('categorie', ''));

        $cats = Produit::query()
            ->whereNull('deleted_at')
            ->where('actif', 1)
            ->where('id_frs', $boutique->id)
            ->when(! in_array((int) $boutique->id, $abonneFournisseurIds, true), fn ($query) => $query->where('abonne_only', 0))
            ->distinct()
            ->orderBy('categorie')
            ->pluck('categorie')
            ->filter()
            ->values();

        $produits = Produit::query()
            ->whereNull('deleted_at')
            ->where('actif', 1)
            ->where('id_frs', $boutique->id)
            ->when(! in_array((int) $boutique->id, $abonneFournisseurIds, true), fn ($query) => $query->where('abonne_only', 0))
            ->with('quantityPrices')
            ->when($categorie !== '', fn ($query) => $query->where('categorie', $categorie))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('designation', 'like', "%{$q}%")
                        ->orWhere('reference', 'like', "%{$q}%")
                        ->orWhere('categorie', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(18)
            ->withQueryString();

        $cartSummary = $this->cartSummary();

        $layoutBoutique = $mode === 'market' ? null : $boutique;
        $layoutMode = $mode === 'market' ? null : $mode;

        return $this->storeView('store.boutique', [
            'title' => $boutique->nom_frs,
            'client' => $client,
            'boutique' => $boutique,
            'theme_boutique' => $boutique,
            'header_brand_boutique' => $boutique,
            'header_brand_url' => $this->boutiqueStorefrontHomeUrl($boutique, $mode),
            'produits' => $produits,
            'categories' => $cats,
            'selected_categorie' => $categorie,
            'boutique_page_url' => $this->boutiqueStorefrontHomeUrl($boutique, $mode),
            'q' => $q,
            'cart_total' => $cartSummary['total'],
            'cart_count' => count($cartSummary['items']),
        ], $layoutBoutique, $layoutMode);
    }

    private function renderStorefrontProductPage(Fournisseur $boutique, int $productId, ?Client $client, string $mode): View
    {
        if ($mode !== 'market') {
            $this->activateStorefront($boutique);
        }

        $p = Produit::query()
            ->whereNull('deleted_at')
            ->where('actif', 1)
            ->where('id_frs', $boutique->id)
            ->with(['images' => fn ($query) => $query->orderBy('ordre'), 'fournisseur:id,nom_frs,storefront_slug,actif,is_visible,deleted_at', 'quantityPrices'])
            ->findOrFail($productId);

        $abonneForProduct = $this->isAbonneFor($client, (int) $p->id_frs);
        if (! $abonneForProduct && (int) ($p->abonne_only ?? 0) === 1) {
            abort(404);
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

        $layoutBoutique = $mode === 'market' ? null : $boutique;
        $layoutMode = $mode === 'market' ? null : $mode;

        return $this->storeView('store.produit', [
            'title' => $p->designation,
            'client' => $client,
            'produit' => $p,
            'theme_boutique' => $boutique,
            'header_brand_boutique' => $boutique,
            'header_brand_url' => $this->boutiqueStorefrontHomeUrl($boutique, $mode),
            'images' => $images,
            'tiers' => $tiers,
            'tierEnabled' => $tierEnabled,
            'back_boutique_url' => $this->boutiqueStorefrontHomeUrl($boutique, $mode),
            'initialQty' => $initialQty,
            'initialUnit' => $initialUnit,
            'cart_total' => $cartSummary['total'],
            'cart_count' => count($cartSummary['items']),
        ], $layoutBoutique, $layoutMode);
    }

    public function index(Request $request): View
    {
        $client = $this->currentClient();
        if ($customDomainBoutique = $this->requestStorefrontDomainBoutique()) {
            return $this->renderStorefrontBoutiquePage($customDomainBoutique, $request, $client, 'domain');
        }

        $this->clearStorefront();
        $q = trim((string) $request->query('q', ''));
        $boutiqueCategoryId = $this->selectedBoutiqueCategoryId($request);

        $boutiques = $this->publicBoutiquesQuery($client)
            ->when($boutiqueCategoryId, fn ($query) => $query->where('boutique_category_id', $boutiqueCategoryId))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nom_frs', 'like', "%{$q}%")
                        ->orWhere('telephone', 'like', "%{$q}%")
                        ->orWhere('adresse', 'like', "%{$q}%");
                });
            })
            ->orderBy('nom_frs')
            ->limit(8)
            ->get(['id', 'nom_frs', 'storefront_slug', 'boutique_category_id', 'logo_path', 'telephone', 'adresse', 'id_wilaya', 'id_commune', 'latitude', 'longitude']);

        $produits = $this->publicProductsQuery($client)
            ->when($boutiqueCategoryId, function ($query) use ($boutiqueCategoryId) {
                $query->whereHas('fournisseur', fn ($sub) => $sub->where('boutique_category_id', $boutiqueCategoryId));
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('designation', 'like', "%{$q}%")
                        ->orWhere('reference', 'like', "%{$q}%")
                        ->orWhereHas('fournisseur', fn ($supplier) => $supplier->where('nom_frs', 'like', "%{$q}%"));
                });
            })
            ->inRandomOrder()
            ->limit(12)
            ->get();

        $cartSummary = $this->cartSummary();

        return $this->storeView('store.index', [
            'title' => 'Boutiques & Produits',
            'client' => $client,
            'boutiques_preview' => $boutiques,
            'produits' => $produits,
            'boutique_categories' => $this->publicBoutiqueCategories(),
            'selected_boutique_category' => $boutiqueCategoryId,
            'q' => $q,
            'cart_total' => $cartSummary['total'],
            'cart_count' => count($cartSummary['items']),
        ]);
    }

    public function boutiques(Request $request): View
    {
        $client = $this->currentClient();
        if ($customDomainBoutique = $this->requestStorefrontDomainBoutique()) {
            return $this->renderStorefrontBoutiquePage($customDomainBoutique, $request, $client, 'domain');
        }

        $this->clearStorefront();
        $q = trim((string) $request->query('q', ''));
        $boutiqueCategoryId = $this->selectedBoutiqueCategoryId($request);

        $boutiques = $this->publicBoutiquesQuery($client)
            ->when($boutiqueCategoryId, fn ($query) => $query->where('boutique_category_id', $boutiqueCategoryId))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nom_frs', 'like', "%{$q}%")
                        ->orWhere('telephone', 'like', "%{$q}%")
                        ->orWhere('adresse', 'like', "%{$q}%");
                });
            })
            ->orderBy('nom_frs')
            ->paginate(16)
            ->withQueryString();

        $cartSummary = $this->cartSummary();

        return $this->storeView('store.boutiques', [
            'title' => 'Toutes les boutiques',
            'client' => $client,
            'boutiques' => $boutiques,
            'boutique_categories' => $this->publicBoutiqueCategories(),
            'selected_boutique_category' => $boutiqueCategoryId,
            'q' => $q,
            'cart_total' => $cartSummary['total'],
            'cart_count' => count($cartSummary['items']),
        ]);
    }

    public function produits(Request $request): View
    {
        $client = $this->currentClient();
        if ($customDomainBoutique = $this->requestStorefrontDomainBoutique()) {
            return $this->renderStorefrontBoutiquePage($customDomainBoutique, $request, $client, 'domain');
        }

        $this->clearStorefront();
        $q = trim((string) $request->query('q', ''));
        $boutiqueCategoryId = $this->selectedBoutiqueCategoryId($request);

        $produits = $this->publicProductsQuery($client)
            ->when($boutiqueCategoryId, function ($query) use ($boutiqueCategoryId) {
                $query->whereHas('fournisseur', fn ($sub) => $sub->where('boutique_category_id', $boutiqueCategoryId));
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('designation', 'like', "%{$q}%")
                        ->orWhere('reference', 'like', "%{$q}%")
                        ->orWhereHas('fournisseur', fn ($supplier) => $supplier->where('nom_frs', 'like', "%{$q}%"));
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $cartSummary = $this->cartSummary();

        return $this->storeView('store.produits', [
            'title' => 'Tous les produits',
            'client' => $client,
            'produits' => $produits,
            'boutique_categories' => $this->publicBoutiqueCategories(),
            'selected_boutique_category' => $boutiqueCategoryId,
            'q' => $q,
            'cart_total' => $cartSummary['total'],
            'cart_count' => count($cartSummary['items']),
        ]);
    }

    public function boutique(int $id, Request $request): View
    {
        $this->clearStorefront();
        $client = $this->currentClient();
        if ($customDomainBoutique = $this->requestStorefrontDomainBoutique()) {
            return $this->renderStorefrontBoutiquePage($customDomainBoutique, $request, $client, 'domain');
        }

        $abonneFournisseurIds = $this->abonneFournisseurIds($client);

        $boutique = Fournisseur::query()
            ->with('boutiqueCategory:id,name')
            ->where('id', $id)
            ->where('actif', 1)
            ->when(! in_array($id, $abonneFournisseurIds, true), fn ($q) => $q->where('is_visible', 1))
            ->whereNull('deleted_at')
            ->firstOrFail();

        return $this->renderStorefrontBoutiquePage($boutique, $request, $client, 'market');
    }

    public function storefrontBoutique(string $slug, Request $request): View
    {
        $client = $this->currentClient();
        $boutique = $this->storefrontBoutiqueOrFail($client, $slug);

        return $this->renderStorefrontBoutiquePage($boutique, $request, $client, 'slug');
    }

    public function produit(int $id): View
    {
        $client = $this->currentClient();
        if ($customDomainBoutique = $this->requestStorefrontDomainBoutique()) {
            return $this->renderStorefrontProductPage($customDomainBoutique, $id, $client, 'domain');
        }

        $p = Produit::query()
            ->whereNull('deleted_at')
            ->where('actif', 1)
            ->with(['images' => fn ($q) => $q->orderBy('ordre'), 'fournisseur:id,nom_frs,actif,is_visible,deleted_at', 'quantityPrices'])
            ->findOrFail($id);

        $abonneForProduct = $this->isAbonneFor($client, (int) $p->id_frs);
        $storefrontAllowsInvisible = $this->storefrontAllowsInvisible($client, (int) $p->id_frs);

        if (! $p->fournisseur || (int) $p->fournisseur->actif !== 1 || $p->fournisseur->deleted_at || ((int) ($p->fournisseur->is_visible ?? 1) !== 1 && ! $storefrontAllowsInvisible)) {
            $allowed = $abonneForProduct || $storefrontAllowsInvisible;
            if (! $allowed) {
                abort(404);
            }
        }

        if (! $abonneForProduct) {
            if ((int) ($p->abonne_only ?? 0) === 1) {
                abort(404);
            }
        }

        $state = $this->currentStorefrontState($client, $p->fournisseur);
        if (($state['boutique'] ?? null) instanceof Fournisseur) {
            return $this->renderStorefrontProductPage($state['boutique'], $id, $client, (string) $state['mode']);
        }

        return $this->renderStorefrontProductPage($p->fournisseur, $id, $client, 'market');
    }

    public function storefrontProduit(string $slug, int $id): View
    {
        $client = $this->currentClient();
        $boutique = $this->storefrontBoutiqueOrFail($client, $slug);

        return $this->renderStorefrontProductPage($boutique, $id, $client, 'slug');
    }

    public function panier(): View
    {
        $client = $this->currentClient();
        $summary = $this->cartSummary();
        $state = $this->currentStorefrontState($client);

        return $this->storeView('store.panier', [
            'title' => 'Panier',
            'client' => $client,
            'items' => $summary['items'],
            'total' => $summary['total'],
            'boutique' => $summary['frs'],
        ], $state['boutique'], $state['mode']);
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
        if ((int) ($p->fournisseur->is_visible ?? 1) !== 1 && ! $this->storefrontAllowsInvisible($client, (int) $p->id_frs)) {
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

        $state = $this->currentStorefrontState($client);

        return $this->storeView('store.checkout', [
            'title' => 'Finaliser la commande',
            'client' => $client,
            'items' => $summary['items'],
            'total' => $summary['total'],
            'boutique' => $summary['frs'],
            'wilayas' => $wilayas,
            'communes' => $communes,
            'selected_wilaya' => $selectedWilaya,
        ], $state['boutique'], $state['mode']);
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
            'tele_livraison' => ['required', 'string', 'max:30'],
            'id_wilaya' => ['required', 'integer', 'exists:wilaya,ID_WILAYA'],
            'id_commune' => ['required', 'integer', 'exists:commune,ID_COMMUNE'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $summary = $this->cartSummary();
        if (count($summary['items']) === 0) {
            return redirect()->to('/panier')->with('error', 'Votre panier est vide.');
        }

        $frsId = $this->cartFournisseurId();
        if (! $frsId) {
            $first = $summary['items'][0]['produit'] ?? null;
            $frsId = $first ? (int) $first->id_frs : null;
        }

        if (! $frsId) {
            return redirect()->to('/panier')->with('error', 'Impossible de déterminer la boutique.');
        }

        $result = DB::transaction(function () use ($client, $summary, $frsId, $data) {
            $frs = Fournisseur::query()
                ->where('id', $frsId)
                ->where('actif', 1)
                ->when(! $this->storefrontAllowsInvisible($client, (int) $frsId), fn ($q) => $q->where('is_visible', 1))
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
            }

            $orderClientResult = $this->resolveOrderClient($client, (int) $frs->id);
            /** @var Client $orderClient */
            $orderClient = $orderClientResult['client'];

            $cmd = Cmd1::create([
                'id_client' => (int) $orderClient->id,
                'id_frs' => (int) $frs->id,
                'date_cmd' => Carbon::now(),
                'statut' => 'en_attente',
                'montant_total' => $montantTotal,
                'adresse_livraison' => $data['adresse_livraison'],
                'tele_livraison' => $data['tele_livraison'],
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

            return [
                'commande' => $cmd,
                'session_client_id' => (int) ($orderClientResult['session_client_id'] ?? $client->id),
            ];
        });

        session()->forget(['cart', 'cart_frs_id']);
        session(['client_id' => (int) ($result['session_client_id'] ?? $client->id)]);

        return redirect()->to('/mes-commandes/'.$result['commande']->id)->with('success', 'Commande créée.');
    }

    public function profil(): RedirectResponse|View
    {
        $client = $this->currentClient();
        if (! $client) {
            session(['url.intended' => url('/profil')]);
            return redirect()->to('/login')->with('error', 'Connectez-vous pour continuer.');
        }

        $client->loadMissing([
            'fournisseur:id,nom_frs,logo_path',
            'wilaya:ID_WILAYA,WILAYA',
            'commune:ID_COMMUNE,COMMUNE',
        ]);

        $profileTabs = collect();
        $relatedClientIds = collect($this->fournisseurClientMap($client))
            ->map(fn (Client $item) => (int) $item->id)
            ->filter(fn (int $id) => $id > 0)
            ->values();

        if ($relatedClientIds->isNotEmpty()) {
            $relatedClients = Client::query()
                ->with('fournisseur:id,nom_frs,logo_path')
                ->whereIn('id', $relatedClientIds->all())
                ->get();

            $profileTabs = $relatedClients
                ->groupBy(fn (Client $item) => (int) $item->id_frs)
                ->map(function ($group, $frsId) {
                    /** @var \Illuminate\Support\Collection<int, Client> $group */
                    $representative = $group
                        ->sortByDesc(fn (Client $item) => [
                            (string) $item->type_client === 'abonne' ? 1 : 0,
                            (int) $item->id,
                        ])
                        ->first();

                    return [
                        'key' => 'frs-'.$frsId,
                        'fournisseur_name' => $representative?->fournisseur?->nom_frs ?: ('Fournisseur #'.$frsId),
                        'fournisseur_logo_url' => (string) ($representative?->fournisseur?->logo_url ?? ''),
                        'type_client' => (string) ($representative->type_client ?? 'simple'),
                        'tarif' => max(1, min(3, (int) ($representative->tarif ?? 1))),
                        'code_client' => (string) ($representative->code_client ?? ''),
                        'synced_pme' => (int) ($representative->synced_pme ?? 0),
                        'achat_client' => (float) $group->sum(fn (Client $item) => (float) ($item->achat_client ?? 0)),
                        'versement_client' => (float) $group->sum(fn (Client $item) => (float) ($item->versement_client ?? 0)),
                        'solde_client' => (float) $group->sum(fn (Client $item) => (float) ($item->solde_client ?? 0)),
                    ];
                })
                ->values();
        }

        if ($profileTabs->isEmpty()) {
            $profileTabs = collect([[
                'key' => 'default',
                'fournisseur_name' => $client->fournisseur?->nom_frs ?: 'Compte principal',
                'fournisseur_logo_url' => (string) ($client->fournisseur?->logo_url ?? ''),
                'type_client' => (string) ($client->type_client ?? 'simple'),
                'tarif' => max(1, min(3, (int) ($client->tarif ?? 1))),
                'code_client' => (string) ($client->code_client ?? ''),
                'synced_pme' => (int) ($client->synced_pme ?? 0),
                'achat_client' => (float) ($client->achat_client ?? 0),
                'versement_client' => (float) ($client->versement_client ?? 0),
                'solde_client' => (float) ($client->solde_client ?? 0),
            ]]);
        }

        $state = $this->currentStorefrontState($client);

        return $this->storeView('store.profil', [
            'title' => 'Mon profil',
            'client' => $client,
            'profile_tabs' => $profileTabs,
        ], $state['boutique'], $state['mode']);
    }

    public function mesCommandes(): RedirectResponse|View
    {
        $client = $this->currentClient();
        if (! $client) {
            session(['url.intended' => url('/mes-commandes')]);
            return redirect()->to('/login')->with('error', 'Connectez-vous pour continuer.');
        }

        $clientIds = $this->relatedClientIds($client);

        $commandes = Cmd1::query()
            ->leftJoin('frs', 'frs.id', '=', 'cmd1.id_frs')
            ->select(['cmd1.*', 'frs.nom_frs as frs_nom'])
            ->whereIn('cmd1.id_client', $clientIds)
            ->orderByDesc('cmd1.date_cmd')
            ->paginate(15);

        $state = $this->currentStorefrontState($client);

        return $this->storeView('store.commandes.index', [
            'title' => 'Mes commandes',
            'client' => $client,
            'commandes' => $commandes,
        ], $state['boutique'], $state['mode']);
    }

    public function commandeShow(int $id): RedirectResponse|View
    {
        $client = $this->currentClient();
        if (! $client) {
            session(['url.intended' => url('/mes-commandes/'.$id)]);
            return redirect()->to('/login')->with('error', 'Connectez-vous pour continuer.');
        }

        $clientIds = $this->relatedClientIds($client);

        $commande = Cmd1::query()
            ->leftJoin('frs', 'frs.id', '=', 'cmd1.id_frs')
            ->select(['cmd1.*', 'frs.nom_frs as frs_nom'])
            ->whereIn('cmd1.id_client', $clientIds)
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

        $state = $this->currentStorefrontState($client);

        return $this->storeView('store.commandes.show', [
            'title' => 'Commande #'.$commande->id,
            'client' => $client,
            'commande' => $commande,
            'lignes' => $lignes,
        ], $state['boutique'], $state['mode']);
    }
}
