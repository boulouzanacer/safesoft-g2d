<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\PmeClientUpsertRequest;
use App\Http\Requests\Api\V1\PmeFournisseurInfoRequest;
use App\Http\Requests\Api\V1\PmeStoreProduitRequest;
use App\Http\Requests\Api\V1\PmeStoreFournisseurRequest;
use App\Http\Requests\Api\V1\PmeSyncClientsRequest;
use App\Http\Requests\Api\V1\PmeSyncProduitsRequest;
use App\Models\Client;
use App\Models\Cmd1;
use App\Models\Cmd2;
use App\Models\Fournisseur;
use App\Models\Produit;
use App\Traits\ApiResponseTrait;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PmeController extends Controller
{
    use ApiResponseTrait;

    private function formatPmeProduct(Produit $produit): array
    {
        return [
            'id' => (int) $produit->id,
            'id_frs' => (int) $produit->id_frs,
            'synced_pme' => (int) ($produit->synced_pme ?? 0),
            'reference' => $produit->reference,
            'designation' => $produit->designation,
            'description' => $produit->description,
            'pv_1' => (float) $produit->pv_1,
            'pv_2' => (float) $produit->pv_2,
            'pv_3' => (float) $produit->pv_3,
            'stock' => (int) $produit->stock,
            'image_principale' => $produit->image_principale,
            'categorie' => $produit->categorie,
            'abonne_only' => (int) ($produit->abonne_only ?? 0),
            'enable_tier_pricing' => $produit->isTierPricingEnabled(),
            'quantity_prices' => $produit->quantityPrices->map(fn ($tier) => [
                'quantity_min' => (int) $tier->quantity_min,
                'quantity_max' => $tier->quantity_max === null ? null : (int) $tier->quantity_max,
                'price' => (float) $tier->price,
            ])->values(),
            'actif' => (int) $produit->actif,
            'images' => $produit->images->map(fn ($image) => [
                'id' => (int) $image->id,
                'filename' => $image->filename,
                'url_principale' => $image->url_principale,
                'url_thumbnail' => $image->url_thumbnail,
                'ordre' => (int) $image->ordre,
            ])->values(),
            'created_at' => optional($produit->created_at)?->toISOString(),
            'updated_at' => optional($produit->updated_at)?->toISOString(),
        ];
    }

    private function normalizeProductQuantityPrices(array $tiers): array
    {
        $normalized = [];

        foreach ($tiers as $row) {
            if (! is_array($row)) {
                continue;
            }

            $min = (int) ($row['quantity_min'] ?? 0);
            $maxRaw = $row['quantity_max'] ?? null;
            $max = ($maxRaw === null || $maxRaw === '') ? null : (int) $maxRaw;
            $price = (float) ($row['price'] ?? 0);

            $normalized[] = [
                'quantity_min' => $min,
                'quantity_max' => $max,
                'price' => $price,
            ];
        }

        usort($normalized, fn ($a, $b) => $a['quantity_min'] <=> $b['quantity_min']);

        return $normalized;
    }

    private function resolveProductData(array $validated, int $frsId, ?Produit $existing = null): array
    {
        $pv1 = array_key_exists('pv_1', $validated) ? (float) $validated['pv_1'] : (float) ($validated['prix'] ?? 0);

        return [
            'id_frs' => $frsId,
            'synced_pme' => 1,
            'reference' => $validated['reference'],
            'designation' => $validated['designation'],
            'description' => (string) ($validated['description'] ?? ($existing?->description ?? '')),
            'pv_1' => $pv1,
            'pv_2' => array_key_exists('pv_2', $validated) ? (float) $validated['pv_2'] : (array_key_exists('pv_1', $validated) ? $pv1 : (float) ($validated['prix'] ?? 0)),
            'pv_3' => array_key_exists('pv_3', $validated) ? (float) $validated['pv_3'] : (array_key_exists('pv_1', $validated) ? $pv1 : (float) ($validated['prix'] ?? 0)),
            'stock' => (int) $validated['stock'],
            'categorie' => $validated['categorie'],
            'abonne_only' => (int) ($validated['abonne_only'] ?? 0) === 1 ? 1 : 0,
            'enable_tier_pricing' => (int) ($validated['enable_tier_pricing'] ?? 0) === 1 ? 1 : 0,
            'actif' => array_key_exists('actif', $validated) ? ((int) $validated['actif'] === 1 ? 1 : 0) : 1,
        ];
    }

    private function saveProductQuantityPrices(Produit $produit, array $validated): void
    {
        $produit->quantityPrices()->delete();

        if ((int) ($validated['enable_tier_pricing'] ?? 0) !== 1) {
            return;
        }

        $tiers = $this->normalizeProductQuantityPrices($validated['quantity_prices'] ?? []);

        foreach ($tiers as $tier) {
            $produit->quantityPrices()->create([
                'quantity_min' => $tier['quantity_min'],
                'quantity_max' => $tier['quantity_max'],
                'price' => $tier['price'],
            ]);
        }
    }

    private function upsertPmeProduct(array $validated, int $frsId): array
    {
        $existing = Produit::query()
            ->where('id_frs', $frsId)
            ->where('reference', $validated['reference'])
            ->first();

        $created = false;

        $produit = DB::transaction(function () use ($validated, $frsId, $existing, &$created) {
            $payload = $this->resolveProductData($validated, $frsId, $existing);

            if ($existing) {
                $existing->update($payload);
                $produit = $existing;
            } else {
                $produit = Produit::create($payload);
                $created = true;
            }

            $this->saveProductQuantityPrices($produit, $validated);

            return $produit;
        });

        $produit->load([
            'images' => fn ($query) => $query->orderBy('ordre'),
            'quantityPrices',
        ]);

        return [$produit, $created];
    }

    private function formatClient(Client $client): array
    {
        $client->loadMissing(['wilaya', 'commune']);

        return [
            'id' => (int) $client->id,
            'code_client' => $client->code_client,
            'nom' => $client->nom,
            'prenom' => $client->prenom,
            'email' => $client->email,
            'telephone' => $client->telephone,
            'adresse' => $client->adresse,
            'id_wilaya' => (int) $client->id_wilaya,
            'nom_wilaya' => $client->wilaya?->WILAYA,
            'id_commune' => (int) $client->id_commune,
            'nom_commune' => $client->commune?->COMMUNE,
            'type_client' => (string) $client->type_client,
            'tarif' => (int) ($client->tarif ?? 1),
            'actif' => (int) $client->actif,
            'synced_pme' => (int) ($client->synced_pme ?? 0),
            'created_at' => optional($client->created_at)?->toISOString(),
            'updated_at' => optional($client->updated_at)?->toISOString(),
        ];
    }

    private function resolveClientData(array $validated, int $frsId, ?Client $existing = null): array
    {
        $payload = [
            'id_frs' => $frsId,
        ];

        foreach ([
            'code_client',
            'nom',
            'prenom',
            'email',
            'telephone',
            'adresse',
            'id_wilaya',
            'id_commune',
            'type_client',
            'tarif',
            'synced_pme',
            'actif',
        ] as $field) {
            if (array_key_exists($field, $validated)) {
                $payload[$field] = $validated[$field];
            }
        }

        if (array_key_exists('password', $validated) && filled($validated['password'])) {
            $payload['password'] = str_starts_with($validated['password'], '$')
                ? $validated['password']
                : Hash::make($validated['password']);
        }

        // Clients created or updated from the PME integration are always abonnés.
        $payload['type_client'] = 'abonne';

        if (! $existing) {
            $payload['adresse'] = $payload['adresse'] ?? '';
            $payload['tarif'] = (int) ($payload['tarif'] ?? 1);
            $payload['synced_pme'] = (int) ($payload['synced_pme'] ?? 1);
            $payload['actif'] = (int) ($payload['actif'] ?? 1);
            $payload['email_verified_at'] = now();
        } else {
            if (array_key_exists('tarif', $payload)) {
                $payload['tarif'] = (int) $payload['tarif'];
            }
            if (array_key_exists('synced_pme', $payload)) {
                $payload['synced_pme'] = (int) $payload['synced_pme'];
            }
            if (array_key_exists('actif', $payload)) {
                $payload['actif'] = (int) $payload['actif'];
            }
            if (array_key_exists('email', $payload) && ! $existing->email_verified_at) {
                $payload['email_verified_at'] = now();
            }
        }

        return $payload;
    }

    public function storeClient(PmeClientUpsertRequest $request)
    {
        $frs = $request->attributes->get('fournisseur');
        $client = Client::create($this->resolveClientData($request->validated(), (int) $frs->id));

        return $this->success($this->formatClient($client), 'Client cree', 201);
    }

    public function storeFournisseur(PmeStoreFournisseurRequest $request)
    {
        $validated = $request->validated();
        $defaultPassword = '12345678';

        try {
            $fournisseur = Fournisseur::query()->create([
                'nom_frs' => $validated['nom_boutique'],
                'boutique_category_id' => (int) $validated['boutique_category_id'],
                'email' => mb_strtolower(trim((string) $validated['email'])),
                'password' => Hash::make($defaultPassword),
                'telephone' => $validated['telephone'],
                'adresse' => '',
                'id_wilaya' => (int) $validated['code_wilaya'],
                'id_commune' => (int) $validated['code_commune'],
                'actif' => 1,
                'expires_at' => now()->addMonth()->toDateString(),
            ]);
        } catch (UniqueConstraintViolationException $exception) {
            return $this->error('Validation échouée', [
                'email' => ['Cet email existe deja pour une boutique.'],
            ], 422);
        }

        return $this->success([
            'id' => (int) $fournisseur->id,
            'nom_boutique' => $fournisseur->nom_frs,
            'boutique_category_id' => (int) $fournisseur->boutique_category_id,
            'boutique_category_name' => $fournisseur->boutiqueCategory?->name,
            'email' => $fournisseur->email,
            'telephone' => $fournisseur->telephone,
            'code_wilaya' => (int) $fournisseur->id_wilaya,
            'code_commune' => (int) $fournisseur->id_commune,
            'actif' => (int) $fournisseur->actif,
            'date_expiration' => optional($fournisseur->expires_at)?->format('Y-m-d'),
            'password_par_defaut' => $defaultPassword,
            'pme_token' => $fournisseur->token,
        ], 'Boutique creee', 201);
    }

    public function fournisseurInfo(PmeFournisseurInfoRequest $request)
    {
        $validated = $request->validated();
        $email = mb_strtolower(trim((string) $validated['email']));

        $fournisseur = Fournisseur::query()
            ->where('email', $email)
            ->first();

        if (! $fournisseur || ! Hash::check($validated['password'], $fournisseur->password)) {
            return $this->unauthorized('Identifiants invalides');
        }

        $fournisseur->syncExpirationStatus();

        if ($fournisseur->isExpired()) {
            return $this->forbidden('Compte expire. Veuillez contacter l administrateur.');
        }

        if ((int) $fournisseur->actif !== 1) {
            return $this->forbidden('Compte desactive');
        }

        return $this->success([
            'id' => (int) $fournisseur->id,
            'nom_boutique' => $fournisseur->nom_frs,
            'boutique_category_id' => (int) $fournisseur->boutique_category_id,
            'boutique_category_name' => $fournisseur->boutiqueCategory?->name,
            'email' => $fournisseur->email,
            'telephone' => $fournisseur->telephone,
            'adresse' => $fournisseur->adresse,
            'code_wilaya' => (int) $fournisseur->id_wilaya,
            'code_commune' => (int) $fournisseur->id_commune,
            'actif' => (int) $fournisseur->actif,
            'date_expiration' => optional($fournisseur->expires_at)?->format('Y-m-d'),
            'pme_token' => $fournisseur->token,
            'logo_url' => $fournisseur->logo_url,
            'is_visible' => (int) ($fournisseur->is_visible ?? 1),
        ], 'Boutique trouvee');
    }

    public function showClient(Request $request, int $id)
    {
        $frs = $request->attributes->get('fournisseur');
        $client = Client::query()
            ->where('id_frs', $frs->id)
            ->find($id);

        if (! $client) {
            return $this->notFound('Client introuvable');
        }

        return $this->success($this->formatClient($client), 'Client PME');
    }

    public function updateClient(PmeClientUpsertRequest $request, int $id)
    {
        $frs = $request->attributes->get('fournisseur');
        $client = Client::query()
            ->where('id_frs', $frs->id)
            ->find($id);

        if (! $client) {
            return $this->notFound('Client introuvable');
        }

        $client->update($this->resolveClientData($request->validated(), (int) $frs->id, $client));
        $client->refresh();

        return $this->success($this->formatClient($client), 'Client mis a jour');
    }

    public function destroyClient(Request $request, int $id)
    {
        $frs = $request->attributes->get('fournisseur');
        $client = Client::query()
            ->where('id_frs', $frs->id)
            ->find($id);

        if (! $client) {
            return $this->notFound('Client introuvable');
        }

        $client->delete();

        return $this->success([
            'id' => $id,
            'deleted' => true,
        ], 'Client supprime');
    }

    public function exportCommandesCsv(Request $request)
    {
        $frs = $request->attributes->get('fournisseur');
        $synced = $request->query('synced', '0');
        $syncedValue = $synced === '1' ? 1 : 0;

        $filename = 'commandes_frs_'.$frs->id.'_synced_'.$syncedValue.'_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($frs, $syncedValue) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'commande_id',
                'client_id',
                'date_cmd',
                'statut',
                'montant_total',
                'adresse_livraison',
                'id_wilaya',
                'id_commune',
                'notes',
                'synced_pme',
                'produit_id',
                'reference',
                'designation',
                'quantite',
                'prix_unitaire',
                'sous_total',
            ], ';');

            $rows = Cmd1::query()
                ->leftJoin('cmd2', 'cmd2.id_cmd', '=', 'cmd1.id')
                ->leftJoin('produit', 'produit.id', '=', 'cmd2.id_produit')
                ->select([
                    'cmd1.id as commande_id',
                    'cmd1.id_client',
                    'cmd1.date_cmd',
                    'cmd1.statut',
                    'cmd1.montant_total',
                    'cmd1.adresse_livraison',
                    'cmd1.id_wilaya',
                    'cmd1.id_commune',
                    'cmd1.notes',
                    'cmd1.synced_pme',
                    'cmd2.id_produit',
                    'cmd2.quantite',
                    'cmd2.prix_unitaire',
                    'cmd2.sous_total',
                    'produit.reference as produit_reference',
                    'produit.designation as produit_designation',
                ])
                ->where('cmd1.id_frs', $frs->id)
                ->where('cmd1.synced_pme', $syncedValue)
                ->orderByDesc('cmd1.date_cmd')
                ->orderByDesc('cmd1.id')
                ->cursor();

            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->commande_id,
                    $r->id_client,
                    (string) $r->date_cmd,
                    (string) $r->statut,
                    (string) $r->montant_total,
                    (string) $r->adresse_livraison,
                    $r->id_wilaya,
                    $r->id_commune,
                    (string) ($r->notes ?? ''),
                    $r->synced_pme,
                    $r->id_produit,
                    (string) ($r->produit_reference ?? ''),
                    (string) ($r->produit_designation ?? ''),
                    $r->quantite,
                    (string) ($r->prix_unitaire ?? ''),
                    (string) ($r->sous_total ?? ''),
                ], ';');
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function syncClients(PmeSyncClientsRequest $request)
    {
        $frs = $request->attributes->get('fournisseur');
        $validated = $request->validated();
        $payload = $validated['clients'];
        $syncedPme = (int) ($validated['synced'] ?? 1) === 1 ? 1 : 0;

        $inserted = 0;
        $updated = 0;
        $failed = [];

        foreach ($payload as $item) {
            try {
                $hashed = $item['password'];
                if (! str_starts_with($hashed, '$')) {
                    $hashed = Hash::make($hashed);
                }

                $existing = Client::query()
                    ->where('id_frs', $frs->id)
                    ->where('code_client', $item['code_client'])
                    ->first();

                if (! $existing) {
                    $existing = Client::query()
                        ->where('id_frs', $frs->id)
                        ->where('email', $item['email'])
                        ->orderByRaw("CASE WHEN type_client = 'simple' THEN 0 ELSE 1 END")
                        ->orderByDesc('id')
                        ->first();
                }

                $data = [
                    'code_client' => $item['code_client'],
                    'nom' => $item['nom'],
                    'prenom' => $item['prenom'],
                    'email' => $item['email'],
                    'password' => $hashed,
                    'telephone' => $item['telephone'] ?? null,
                    'adresse' => '',
                    'id_wilaya' => (int) $item['id_wilaya'],
                    'id_commune' => (int) $item['id_commune'],
                    'type_client' => 'abonne',
                    'tarif' => (int) ($item['tarif'] ?? 1),
                    'id_frs' => $frs->id,
                    'synced_pme' => $syncedPme,
                    'actif' => 1,
                    'email_verified_at' => now(),
                ];

                if ($existing) {
                    $existing->update($data);
                    $updated++;
                } else {
                    Client::create($data);
                    $inserted++;
                }
            } catch (\Throwable $e) {
                $failed[] = [
                    'code_client' => $item['code_client'],
                    'email' => $item['email'],
                    'error' => 'Erreur lors de la synchronisation',
                ];
            }
        }

        return $this->success([
            'nb_inseres' => $inserted,
            'nb_mis_a_jour' => $updated,
            'nb_erreurs' => count($failed),
            'erreurs' => $failed,
        ], 'Sync clients terminé');
    }

    public function clients(Request $request)
    {
        $frs = $request->attributes->get('fournisseur');
        $synced = $request->query('synced');
        $typeClient = trim((string) $request->query('type_client', ''));

        $items = Client::query()
            ->where('id_frs', $frs->id)
            ->with(['wilaya', 'commune'])
            ->when(in_array((string) $synced, ['0', '1'], true), fn ($q) => $q->where('synced_pme', (int) $synced))
            ->when(in_array($typeClient, ['simple', 'abonne'], true), fn ($q) => $q->where('type_client', $typeClient))
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->limit(500)
            ->get()
            ->map(fn (Client $client) => $this->formatClient($client))
            ->values();

        return $this->success($items, 'Clients PME');
    }

    public function produits(Request $request)
    {
        $frs = $request->attributes->get('fournisseur');
        $categorie = trim((string) $request->query('categorie', ''));
        $search = trim((string) $request->query('search', ''));
        $actif = $request->query('actif');
        $abonneOnly = $request->query('abonne_only');
        $synced = $request->query('synced');
        $perPage = min(max((int) $request->query('per_page', 50), 1), 100);

        $paginator = Produit::query()
            ->where('id_frs', $frs->id)
            ->whereNull('deleted_at')
            ->with([
                'images' => fn ($query) => $query->orderBy('ordre'),
                'quantityPrices',
            ])
            ->when(in_array((string) $synced, ['0', '1'], true), fn ($query) => $query->where('synced_pme', (int) $synced))
            ->when(in_array((string) $actif, ['0', '1'], true), fn ($query) => $query->where('actif', (int) $actif))
            ->when(in_array((string) $abonneOnly, ['0', '1'], true), fn ($query) => $query->where('abonne_only', (int) $abonneOnly))
            ->when($categorie !== '', fn ($query) => $query->where('categorie', $categorie))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('designation', 'like', "%{$search}%")
                        ->orWhere('reference', 'like', "%{$search}%")
                        ->orWhere('categorie', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $items = $paginator->getCollection()
            ->map(fn (Produit $produit) => $this->formatPmeProduct($produit))
            ->values();

        return $this->success([
            'items' => $items,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ], 'Produits PME');
    }

    public function storeProduit(PmeStoreProduitRequest $request)
    {
        $frs = $request->attributes->get('fournisseur');
        [$produit, $created] = $this->upsertPmeProduct($request->validated(), (int) $frs->id);

        return $this->success(
            $this->formatPmeProduct($produit),
            $created ? 'Produit cree' : 'Produit mis a jour',
            $created ? 201 : 200
        );
    }

    public function syncProduits(PmeSyncProduitsRequest $request)
    {
        $frs = $request->attributes->get('fournisseur');
        $items = $request->validated()['produits'];

        $inserted = 0;
        $updated = 0;

        DB::transaction(function () use ($frs, $items, &$inserted, &$updated) {
            foreach ($items as $item) {
                [, $created] = $this->upsertPmeProduct($item, (int) $frs->id);
                $created ? $inserted++ : $updated++;
            }
        });

        return $this->success([
            'nb_inseres' => $inserted,
            'nb_mis_a_jour' => $updated,
        ], 'Sync produits terminé');
    }

    public function markProduitSynced(Request $request, int $id)
    {
        $frs = $request->attributes->get('fournisseur');

        $produit = Produit::query()
            ->where('id_frs', $frs->id)
            ->whereNull('deleted_at')
            ->find($id);

        if (! $produit) {
            return $this->notFound('Produit introuvable');
        }

        $produit->update(['synced_pme' => 1]);
        $produit->refresh();

        return $this->success([
            'id' => (int) $produit->id,
            'reference' => $produit->reference,
            'synced_pme' => (int) $produit->synced_pme,
        ], 'Produit synchronise');
    }

    public function syncFournisseur(Request $request)
    {
        $frs = $request->attributes->get('fournisseur');

        $data = $request->validate([
            'nom_frs' => ['nullable', 'string', 'max:255'],
            'boutique_category_id' => ['nullable', 'integer', 'exists:boutique_categories,id'],
            'telephone' => ['nullable', 'string', 'max:255'],
            'adresse' => ['nullable', 'string'],
            'id_wilaya' => ['nullable', 'integer', 'exists:wilaya,ID_WILAYA'],
            'id_commune' => ['nullable', 'integer', 'exists:commune,ID_COMMUNE'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_logo' => ['nullable', 'boolean'],
            'is_visible' => ['nullable', 'boolean'],
        ]);

        $payload = [];
        foreach (['nom_frs', 'boutique_category_id', 'telephone', 'adresse', 'id_wilaya', 'id_commune', 'latitude', 'longitude', 'is_visible'] as $key) {
            if (array_key_exists($key, $data)) {
                if ($key === 'is_visible') {
                    $payload[$key] = (int) $data[$key] === 1 ? 1 : 0;
                } else {
                    $payload[$key] = $data[$key];
                }
            }
        }

        if ((int) ($data['remove_logo'] ?? 0) === 1) {
            if (! empty($frs->logo_path)) {
                Storage::disk('public')->delete($frs->logo_path);
            }
            $payload['logo_path'] = null;
        }

        if ($request->hasFile('logo')) {
            if (! empty($frs->logo_path)) {
                Storage::disk('public')->delete($frs->logo_path);
            }
            $ext = strtolower((string) $request->file('logo')->getClientOriginalExtension());
            if ($ext === '') {
                $ext = 'jpg';
            }
            $path = $request->file('logo')->storeAs(
                "frs/{$frs->id}",
                'logo_'.now()->timestamp.'.'.$ext,
                'public'
            );
            $payload['logo_path'] = $path;
        }

        if (count($payload) > 0) {
            $frs->update($payload);
        }

        $frs->refresh();

        return $this->success([
            'id' => (int) $frs->id,
            'nom_frs' => $frs->nom_frs,
            'boutique_category_id' => (int) $frs->boutique_category_id,
            'boutique_category_name' => $frs->boutiqueCategory?->name,
            'telephone' => $frs->telephone,
            'adresse' => $frs->adresse,
            'id_wilaya' => (int) $frs->id_wilaya,
            'id_commune' => (int) $frs->id_commune,
            'latitude' => $frs->latitude,
            'longitude' => $frs->longitude,
            'logo_url' => $frs->logo_url,
            'is_visible' => (int) ($frs->is_visible ?? 1),
        ], 'Sync boutique termine');
    }

    public function commandes(Request $request)
    {
        $frs = $request->attributes->get('fournisseur');
        $synced = $request->query('synced', '0');
        $syncedValue = $synced === '1' ? 1 : 0;

        $commandes = Cmd1::query()
            ->where('id_frs', $frs->id)
            ->where('synced_pme', $syncedValue)
            ->with(['client', 'wilaya', 'commune'])
            ->orderByDesc('date_cmd')
            ->limit(200)
            ->get();

        $ids = $commandes->pluck('id')->all();

        $lignes = [];
        if (count($ids) > 0) {
            $rows = Cmd2::query()
                ->leftJoin('produit', 'produit.id', '=', 'cmd2.id_produit')
                ->select([
                    'cmd2.id_cmd',
                    'cmd2.id_produit',
                    'cmd2.quantite',
                    'cmd2.prix_unitaire',
                    'cmd2.sous_total',
                    'produit.reference as produit_reference',
                    'produit.designation as produit_designation',
                ])
                ->whereIn('cmd2.id_cmd', $ids)
                ->orderBy('cmd2.id_cmd')
                ->orderBy('cmd2.id')
                ->get();

            foreach ($rows as $r) {
                $lignes[$r->id_cmd][] = [
                    'id_cmd' => (int) $r->id_cmd,
                    'id_produit' => (int) $r->id_produit,
                    'reference' => $r->produit_reference,
                    'designation' => $r->produit_designation,
                    'quantite' => (int) $r->quantite,
                    'prix_unitaire' => (float) $r->prix_unitaire,
                    'sous_total' => (float) $r->sous_total,
                ];
            }
        }

        $items = $commandes->map(function (Cmd1 $c) use ($lignes) {
            return [
                'id' => $c->id,
                'id_client' => (int) $c->id_client,
                'nom_client' => $c->client?->nom,
                'date_cmd' => (string) $c->date_cmd,
                'statut' => (string) $c->statut,
                'montant_total' => (float) $c->montant_total,
                'adresse_livraison' => $c->adresse_livraison,
                'id_wilaya' => (int) $c->id_wilaya,
                'nom_wilaya' => $c->wilaya?->WILAYA,
                'id_commune' => (int) $c->id_commune,
                'nom_commune' => $c->commune?->COMMUNE,
                'notes' => $c->notes,
                'synced_pme' => (int) $c->synced_pme,
                'lignes' => $lignes[$c->id] ?? [],
            ];
        })->values();

        return $this->success($items, 'Commandes PME');
    }

    public function markSynced(Request $request, int $id)
    {
        $frs = $request->attributes->get('fournisseur');

        $cmd = Cmd1::query()
            ->where('id', $id)
            ->where('id_frs', $frs->id)
            ->first();

        if (! $cmd) {
            return $this->notFound();
        }

        $cmd->update(['synced_pme' => 1]);

        return $this->success([
            'id' => $cmd->id,
            'synced_pme' => 1,
        ], 'Commande marquée synchronisée');
    }
}
