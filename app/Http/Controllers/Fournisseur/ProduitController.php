<?php

namespace App\Http\Controllers\Fournisseur;

use App\Http\Controllers\Controller;
use App\Models\Produit;
use App\Models\ProduitImage;
use App\Services\ImageProduitService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProduitController extends Controller
{
    public function index(Request $request): View
    {
        $frsId = (int) session('frs_id');
        $q = trim((string) $request->query('q', ''));
        $categorie = trim((string) $request->query('categorie', ''));

        $categories = Produit::query()
            ->where('id_frs', $frsId)
            ->select('categorie')
            ->distinct()
            ->orderBy('categorie')
            ->pluck('categorie')
            ->filter()
            ->values();

        $produits = Produit::query()
            ->where('id_frs', $frsId)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('designation', 'like', "%{$q}%")
                        ->orWhere('reference', 'like', "%{$q}%");
                });
            })
            ->when($categorie !== '', fn ($query) => $query->where('categorie', $categorie))
            ->orderByDesc('created_at')
            ->paginate(18)
            ->withQueryString();

        return view('fournisseur.produits.index', [
            'title' => 'Mes Produits',
            'q' => $q,
            'categorie' => $categorie,
            'categories' => $categories,
            'produits' => $produits,
        ]);
    }

    public function create(): View
    {
        return view('fournisseur.produits.create', [
            'title' => 'Créer Produit',
            'produit' => null,
            'images' => collect(),
        ]);
    }

    public function store(Request $request, ImageProduitService $imageService): RedirectResponse
    {
        $frsId = (int) session('frs_id');

        $data = $request->validate([
            'reference' => ['required', 'string', 'max:100'],
            'designation' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'prix' => ['required', 'numeric'],
            'stock' => ['required', 'integer', 'min:0'],
            'categorie' => ['required', 'string', 'max:100'],
            'actif' => ['nullable', 'boolean'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'images_order' => ['nullable', 'array'],
            'images_order.*' => ['string'],
            'primary_image' => ['nullable', 'string'],
        ]);

        $produit = Produit::create([
            'id_frs' => $frsId,
            'reference' => $data['reference'],
            'designation' => $data['designation'],
            'description' => $data['description'],
            'prix' => $data['prix'],
            'stock' => $data['stock'],
            'categorie' => $data['categorie'],
            'actif' => (int) ($data['actif'] ?? 0) === 1 ? 1 : 0,
        ]);

        $files = $request->file('images', []);
        if (count($files) > 0) {
            $imageService->storeUploadedImages(
                $produit,
                $frsId,
                $files,
                $data['images_order'] ?? null,
                $data['primary_image'] ?? null
            );
        }

        return redirect()
            ->to("/fournisseur/produits/{$produit->id}/edit")
            ->with('success', 'Produit créé.');
    }

    public function edit(int $id): View
    {
        $frsId = (int) session('frs_id');

        $produit = Produit::query()
            ->where('id_frs', $frsId)
            ->findOrFail($id);

        $images = ProduitImage::query()
            ->where('id_produit', $produit->id)
            ->orderBy('ordre')
            ->get();

        return view('fournisseur.produits.edit', [
            'title' => 'Éditer Produit',
            'produit' => $produit,
            'images' => $images,
        ]);
    }

    public function update(Request $request, int $id, ImageProduitService $imageService): RedirectResponse
    {
        $frsId = (int) session('frs_id');

        $produit = Produit::query()
            ->where('id_frs', $frsId)
            ->findOrFail($id);

        $existingCount = ProduitImage::query()->where('id_produit', $produit->id)->count();

        $data = $request->validate([
            'reference' => ['required', 'string', 'max:100'],
            'designation' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'prix' => ['required', 'numeric'],
            'stock' => ['required', 'integer', 'min:0'],
            'categorie' => ['required', 'string', 'max:100'],
            'actif' => ['nullable', 'boolean'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'delete_images' => ['nullable', 'array'],
            'delete_images.*' => ['integer'],
            'images_order' => ['nullable', 'array'],
            'images_order.*' => ['string'],
            'primary_image' => ['nullable', 'string'],
        ]);

        $produit->update([
            'reference' => $data['reference'],
            'designation' => $data['designation'],
            'description' => $data['description'],
            'prix' => $data['prix'],
            'stock' => $data['stock'],
            'categorie' => $data['categorie'],
            'actif' => (int) ($data['actif'] ?? 0) === 1 ? 1 : 0,
        ]);

        if (! empty($data['delete_images'] ?? [])) {
            $imageService->deleteImages($produit, $frsId, $data['delete_images']);
            $existingCount = ProduitImage::query()->where('id_produit', $produit->id)->count();
        }

        $files = $request->file('images', []);
        $totalAfter = $existingCount + count($files);
        if ($totalAfter > 5) {
            return back()->withErrors(['images' => 'Maximum 5 images par produit.'])->withInput();
        }

        if (count($files) > 0) {
            $imageService->storeUploadedImages(
                $produit,
                $frsId,
                $files,
                $data['images_order'] ?? null,
                $data['primary_image'] ?? null
            );
        } else {
            $orders = $data['images_order'] ?? null;
            if (is_array($orders)) {
                $existing = ProduitImage::query()
                    ->where('id_produit', $produit->id)
                    ->get()
                    ->keyBy(fn ($img) => 'existing:'.$img->id);

                $ordered = [];
                foreach ($orders as $k) {
                    if (isset($existing[$k])) {
                        $ordered[] = $k;
                    }
                }
                foreach ($existing->keys() as $k) {
                    if (! in_array($k, $ordered, true)) {
                        $ordered[] = $k;
                    }
                }

                foreach ($ordered as $i => $k) {
                    $existing[$k]->update(['ordre' => $i]);
                }
            }

            if (! empty($data['primary_image'] ?? null) && str_starts_with($data['primary_image'], 'existing:')) {
                $idImg = (int) str_replace('existing:', '', $data['primary_image']);
                $img = ProduitImage::query()
                    ->where('id_produit', $produit->id)
                    ->where('id', $idImg)
                    ->first();
                if ($img) {
                    $produit->update(['image_principale' => $img->url_principale]);
                }
            }
        }

        return back()->with('success', 'Produit mis à jour.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $frsId = (int) session('frs_id');

        $produit = Produit::query()
            ->where('id_frs', $frsId)
            ->findOrFail($id);

        $produit->delete();

        return redirect()->to('/fournisseur/produits')->with('success', 'Produit supprimé.');
    }

    public function toggleActif(int $id): RedirectResponse
    {
        $frsId = (int) session('frs_id');

        $produit = Produit::query()
            ->where('id_frs', $frsId)
            ->findOrFail($id);

        $produit->actif = (int) $produit->actif === 1 ? 0 : 1;
        $produit->save();

        return back()->with('success', 'Statut produit mis à jour.');
    }
}
