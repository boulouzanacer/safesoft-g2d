<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ProduitCategoriesRequest;
use App\Http\Requests\Api\V1\ProduitIndexRequest;
use App\Models\Client;
use App\Models\Produit;
use App\Services\ClientBoutiqueManager;
use App\Traits\ApiResponseTrait;

class ProduitController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private readonly ClientBoutiqueManager $clientBoutiqueManager)
    {
    }

    public function index(ProduitIndexRequest $request)
    {
        $client = $this->clientBoutiqueManager->resolveAuthenticatedClient($request->user());
        $abonneFournisseurIds = $this->clientBoutiqueManager->abonneFournisseurIds($client);
        $frsId = $request->query('frs_id');
        $categorie = trim((string) $request->query('categorie', ''));
        $search = trim((string) $request->query('search', ''));

        $query = Produit::query()
            ->whereNull('deleted_at')
            ->where('actif', 1)
            ->where(function ($q) use ($abonneFournisseurIds) {
                $q->where('abonne_only', 0);

                if (count($abonneFournisseurIds) > 0) {
                    $q->orWhereIn('id_frs', $abonneFournisseurIds);
                }
            })
            ->with([
                'images' => fn ($q) => $q->orderBy('ordre'),
                'fournisseur:id,nom_frs,actif,is_visible,deleted_at',
                'quantityPrices',
            ])
            ->whereHas('fournisseur', function ($q) use ($abonneFournisseurIds) {
                $q->where('actif', 1)
                    ->whereNull('deleted_at')
                    ->where(function ($sub) use ($abonneFournisseurIds) {
                        $sub->where('is_visible', 1);

                        if (count($abonneFournisseurIds) > 0) {
                            $sub->orWhereIn('id', $abonneFournisseurIds);
                        }
                    });
            });

        if ($frsId) {
            $query->where('id_frs', $frsId);
        }

        if ($categorie !== '') {
            $query->where('categorie', $categorie);
        }

        if ($search !== '') {
            $query->where(function ($sub) use ($search) {
                $sub->where('designation', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        $paginator = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $items = $paginator->getCollection()->map(function (Produit $p) use ($client) {
            $pricingClient = $this->clientBoutiqueManager->fournisseurClientFor($client, (int) $p->id_frs);

            return [
                'id' => $p->id,
                'id_frs' => $p->id_frs,
                'nom_frs' => $p->fournisseur?->nom_frs,
                'reference' => $p->reference,
                'designation' => $p->designation,
                'description' => $p->description,
                'pv_1' => (float) $p->pv_1,
                'pv_2' => (float) $p->pv_2,
                'pv_3' => (float) $p->pv_3,
                'prix' => (float) $p->prixUnitairePourQuantite($pricingClient, 1),
                'stock' => (int) $p->stock,
                'image_principale' => $p->image_principale,
                'categorie' => $p->categorie,
                'abonne_only' => (int) ($p->abonne_only ?? 0),
                'enable_tier_pricing' => $p->isTierPricingEnabled(),
                'quantity_prices' => $p->quantityPrices->map(fn ($t) => [
                    'quantity_min' => (int) $t->quantity_min,
                    'quantity_max' => $t->quantity_max === null ? null : (int) $t->quantity_max,
                    'price' => (float) $t->price,
                ])->values(),
                'actif' => (int) $p->actif,
                'images' => $p->images->map(fn ($img) => [
                    'id' => $img->id,
                    'filename' => $img->filename,
                    'url_principale' => $img->url_principale,
                    'url_thumbnail' => $img->url_thumbnail,
                    'ordre' => (int) $img->ordre,
                ])->values(),
            ];
        })->values();

        return $this->success([
            'items' => $items,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ], 'Liste des produits');
    }

    public function show(int $id)
    {
        $client = $this->clientBoutiqueManager->resolveAuthenticatedClient(request()->user());
        $abonneFournisseurIds = $this->clientBoutiqueManager->abonneFournisseurIds($client);

        $p = Produit::query()
            ->whereNull('deleted_at')
            ->where('actif', 1)
            ->with([
                'images' => fn ($q) => $q->orderBy('ordre'),
                'fournisseur:id,nom_frs,actif,is_visible,deleted_at',
                'quantityPrices',
            ])
            ->whereHas('fournisseur', function ($q) use ($abonneFournisseurIds) {
                $q->where('actif', 1)
                    ->whereNull('deleted_at')
                    ->where(function ($sub) use ($abonneFournisseurIds) {
                        $sub->where('is_visible', 1);

                        if (count($abonneFournisseurIds) > 0) {
                            $sub->orWhereIn('id', $abonneFournisseurIds);
                        }
                    });
            })
            ->find($id);

        if (! $p || ! $p->fournisseur) {
            return $this->notFound();
        }

        if (! in_array((int) $p->id_frs, $abonneFournisseurIds, true) && (int) ($p->abonne_only ?? 0) === 1) {
            return $this->notFound();
        }

        $pricingClient = $this->clientBoutiqueManager->fournisseurClientFor($client, (int) $p->id_frs);

        return $this->success([
            'id' => $p->id,
            'id_frs' => $p->id_frs,
            'nom_frs' => $p->fournisseur->nom_frs,
            'reference' => $p->reference,
            'designation' => $p->designation,
            'description' => $p->description,
            'pv_1' => (float) $p->pv_1,
            'pv_2' => (float) $p->pv_2,
            'pv_3' => (float) $p->pv_3,
            'prix' => (float) $p->prixUnitairePourQuantite($pricingClient, 1),
            'stock' => (int) $p->stock,
            'image_principale' => $p->image_principale,
            'categorie' => $p->categorie,
            'abonne_only' => (int) ($p->abonne_only ?? 0),
            'enable_tier_pricing' => $p->isTierPricingEnabled(),
            'quantity_prices' => $p->quantityPrices->map(fn ($t) => [
                'quantity_min' => (int) $t->quantity_min,
                'quantity_max' => $t->quantity_max === null ? null : (int) $t->quantity_max,
                'price' => (float) $t->price,
            ])->values(),
            'actif' => (int) $p->actif,
            'images' => $p->images->map(fn ($img) => [
                'id' => $img->id,
                'filename' => $img->filename,
                'url_principale' => $img->url_principale,
                'url_thumbnail' => $img->url_thumbnail,
                'ordre' => (int) $img->ordre,
            ])->values(),
        ], 'Détail produit');
    }

    public function categories(ProduitCategoriesRequest $request)
    {
        $client = $this->clientBoutiqueManager->resolveAuthenticatedClient($request->user());
        $abonneFournisseurIds = $this->clientBoutiqueManager->abonneFournisseurIds($client);
        $frsId = $request->query('frs_id');

        $q = Produit::query()
            ->whereNull('deleted_at')
            ->where('actif', 1)
            ->where(function ($q2) use ($abonneFournisseurIds) {
                $q2->where('abonne_only', 0);

                if (count($abonneFournisseurIds) > 0) {
                    $q2->orWhereIn('id_frs', $abonneFournisseurIds);
                }
            })
            ->whereHas('fournisseur', function ($q2) use ($abonneFournisseurIds) {
                $q2->where('actif', 1)
                    ->whereNull('deleted_at')
                    ->where(function ($sub) use ($abonneFournisseurIds) {
                        $sub->where('is_visible', 1);

                        if (count($abonneFournisseurIds) > 0) {
                            $sub->orWhereIn('id', $abonneFournisseurIds);
                        }
                    });
            });

        if ($frsId) {
            $q->where('id_frs', $frsId);
        }

        $cats = $q->distinct()
            ->orderBy('categorie')
            ->pluck('categorie')
            ->filter()
            ->values();

        return $this->success($cats, 'Catégories');
    }
}
