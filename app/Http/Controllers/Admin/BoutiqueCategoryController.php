<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BoutiqueCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BoutiqueCategoryController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('boutique_categories', 'name')],
            'image' => ['required', 'image', 'max:4096'],
        ]);

        $category = BoutiqueCategory::query()->create([
            'name' => trim($validated['name']),
            'slug' => $this->uniqueSlug(trim($validated['name'])),
        ]);

        if ($request->hasFile('image')) {
            $category->update([
                'image_path' => $request->file('image')->store("boutique-categories/{$category->id}", 'public'),
            ]);
        }

        return redirect('/admin/dashboard')->with('success', 'Catégorie boutique créée.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $category = BoutiqueCategory::query()->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('boutique_categories', 'name')->ignore($category->id)],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);

        $name = trim($validated['name']);
        $payload = [
            'name' => $name,
            'slug' => $this->uniqueSlug($name, $category->id),
        ];

        if ($request->hasFile('image')) {
            if (! empty($category->image_path)) {
                Storage::disk('public')->delete($category->image_path);
            }

            $payload['image_path'] = $request->file('image')->store("boutique-categories/{$category->id}", 'public');
        }

        $category->update($payload);

        return redirect('/admin/dashboard')->with('success', 'Catégorie boutique mise à jour.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $category = BoutiqueCategory::query()
            ->withCount('fournisseurs')
            ->findOrFail($id);

        if ((int) $category->fournisseurs_count > 0) {
            return redirect('/admin/dashboard')->with('error', 'Impossible de supprimer une catégorie déjà utilisée par une boutique.');
        }

        if (! empty($category->image_path)) {
            Storage::disk('public')->deleteDirectory("boutique-categories/{$category->id}");
        }

        $category->delete();

        return redirect('/admin/dashboard')->with('success', 'Catégorie boutique supprimée.');
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'categorie-boutique';
        }

        $slug = $base;
        $index = 2;

        while (
            BoutiqueCategory::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base.'-'.$index;
            $index++;
        }

        return $slug;
    }
}
