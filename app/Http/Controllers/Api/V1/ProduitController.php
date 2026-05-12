<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ProduitCategoriesRequest;
use App\Http\Requests\Api\V1\ProduitIndexRequest;
use App\Models\Client;
use App\Models\Produit;
use App\Traits\ApiResponseTrait;

class ProduitController extends Controller
{
    use ApiResponseTrait;

    public function index(ProduitIndexRequest $request)
    {
        $client = $request->user();
        $isAbonne = $client instanceof Client && (string) $client->type_client === 'abonne';

        $forcedFrsId = $isAbonne && $client->id_frs ? (int) $client->id_frs : null;
        $frsId = $forcedFrsId ?: $request->query('frs_id');
        $categorie = trim((string) $request->query('categorie', ''));
        $search = trim((string) $request->query('search', ''));

        $query = Produit::query()
            ->whereNull('deleted_at')
            ->where('actif', 1)
            ->when(! $isAbonne, fn ($q) => $q->where('abonne_only', 0))
            ->with([
                'images' => fn ($q) => $q->orderBy('ordre'),
                'fournisseur:id,nom_frs,actif,is_visible,deleted_at',
                'quantityPrices',
            ])
            ->whereHas('fournisseur', function ($q) use ($forcedFrsId) {
                $q->where('actif', 1)
                    ->whereNull('deleted_at')
                    ->where(function ($sub) use ($forcedFrsId) {
                        $sub->where('is_visible', 1);
                        if ($forcedFrsId) {
                            $sub->orWhere('id', $forcedFrsId);
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
                'prix' => (float) $p->prixUnitairePourQuantite($client instanceof Client ? $client : null, 1),
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
        $client = request()->user();
        $isAbonne = $client instanceof Client && (string) $client->type_client === 'abonne';

        $p = Produit::query()
            ->whereNull('deleted_at')
            ->where('actif', 1)
            ->when(! $isAbonne, fn ($q) => $q->where('abonne_only', 0))
            ->when($isAbonne && $client->id_frs, fn ($q) => $q->where('id_frs', (int) $client->id_frs))
            ->with([
                'images' => fn ($q) => $q->orderBy('ordre'),
                'fournisseur:id,nom_frs,actif,is_visible,deleted_at',
                'quantityPrices',
            ])
            ->whereHas('fournisseur', function ($q) use ($isAbonne, $client) {
                $forcedFrsId = $isAbonne && $client->id_frs ? (int) $client->id_frs : null;
                $q->where('actif', 1)
                    ->whereNull('deleted_at')
                    ->where(function ($sub) use ($forcedFrsId) {
                        $sub->where('is_visible', 1);
                        if ($forcedFrsId) {
                            $sub->orWhere('id', $forcedFrsId);
                        }
                    });
            })
            ->find($id);

        if (! $p || ! $p->fournisseur) {
            return $this->notFound();
        }

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
            'prix' => (float) $p->prixUnitairePourQuantite($client instanceof Client ? $client : null, 1),
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
        $client = $request->user();
        $isAbonne = $client instanceof Client && (string) $client->type_client === 'abonne';
        $frsId = $request->query('frs_id');

        $q = Produit::query()
            ->whereNull('deleted_at')
            ->where('actif', 1)
            ->when(! $isAbonne, fn ($q2) => $q2->where('abonne_only', 0))
            ->whereHas('fournisseur', function ($q2) use ($isAbonne, $client) {
                $forcedFrsId = $isAbonne && $client->id_frs ? (int) $client->id_frs : null;
                $q2->where('actif', 1)
                    ->whereNull('deleted_at')
                    ->where(function ($sub) use ($forcedFrsId) {
                        $sub->where('is_visible', 1);
                        if ($forcedFrsId) {
                            $sub->orWhere('id', $forcedFrsId);
                        }
                    });
            });

        if ($frsId) {
            $q->where('id_frs', $frsId);
        } elseif ($isAbonne && $client->id_frs) {
            $q->where('id_frs', (int) $client->id_frs);
        }

        $cats = $q->distinct()
            ->orderBy('categorie')
            ->pluck('categorie')
            ->filter()
            ->values();

        return $this->success($cats, 'Catégories');
    }
}
