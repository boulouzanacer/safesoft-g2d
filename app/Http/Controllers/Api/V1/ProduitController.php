<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ProduitCategoriesRequest;
use App\Http\Requests\Api\V1\ProduitIndexRequest;
use App\Models\Produit;
use App\Traits\ApiResponseTrait;

class ProduitController extends Controller
{
    use ApiResponseTrait;

    public function index(ProduitIndexRequest $request)
    {
        $frsId = $request->query('frs_id');
        $categorie = trim((string) $request->query('categorie', ''));
        $search = trim((string) $request->query('search', ''));

        $query = Produit::query()
            ->whereNull('deleted_at')
            ->where('actif', 1)
            ->with([
                'images' => fn ($q) => $q->orderBy('ordre'),
                'fournisseur:id,nom_frs,actif,deleted_at',
            ]);

        if ($frsId) {
            $query->where('id_frs', $frsId);
        } else {
            $query->whereHas('fournisseur', fn ($q) => $q->where('actif', 1)->whereNull('deleted_at'));
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

        $items = $paginator->getCollection()->map(function (Produit $p) {
            return [
                'id' => $p->id,
                'id_frs' => $p->id_frs,
                'nom_frs' => $p->fournisseur?->nom_frs,
                'reference' => $p->reference,
                'designation' => $p->designation,
                'description' => $p->description,
                'prix' => (float) $p->prix,
                'stock' => (int) $p->stock,
                'image_principale' => $p->image_principale,
                'categorie' => $p->categorie,
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
        $p = Produit::query()
            ->whereNull('deleted_at')
            ->where('actif', 1)
            ->with([
                'images' => fn ($q) => $q->orderBy('ordre'),
                'fournisseur:id,nom_frs,actif,deleted_at',
            ])
            ->find($id);

        if (! $p || ! $p->fournisseur || (int) $p->fournisseur->actif !== 1 || $p->fournisseur->deleted_at) {
            return $this->notFound();
        }

        return $this->success([
            'id' => $p->id,
            'id_frs' => $p->id_frs,
            'nom_frs' => $p->fournisseur->nom_frs,
            'reference' => $p->reference,
            'designation' => $p->designation,
            'description' => $p->description,
            'prix' => (float) $p->prix,
            'stock' => (int) $p->stock,
            'image_principale' => $p->image_principale,
            'categorie' => $p->categorie,
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
        $frsId = $request->query('frs_id');

        $q = Produit::query()
            ->whereNull('deleted_at')
            ->where('actif', 1);

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
