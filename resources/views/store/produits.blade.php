@extends('store.layout')

@section('content')
<div class="space-y-6">
    <div class="store-panel p-4 sm:p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <div class="text-2xl font-extrabold tracking-wide">Tous les produits</div>
                <div class="store-muted mt-1 text-sm">Recherche produit avec filtre par catégorie de boutique.</div>
            </div>
            <a href="{{ url('/') }}" class="store-link text-sm font-semibold hover:underline">
                Retour accueil
            </a>
        </div>

        <form method="GET" action="{{ url('/produits') }}" class="mt-5 grid grid-cols-1 lg:grid-cols-[1fr_auto] gap-3">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 store-muted"></i>
                <input name="q"
                       value="{{ $q }}"
                       placeholder="Rechercher un produit ou une boutique..."
                       class="store-input w-full pl-11 pr-4 py-3">
            </div>
            <button class="hidden" type="submit">Rechercher</button>
        </form>

        @include('store.partials.boutique-category-grid', [
            'categories' => $boutique_categories ?? collect(),
            'selectedCategoryId' => $selected_boutique_category,
            'currentUrl' => url('/produits'),
            'query' => $q,
            'title' => 'Catégories boutiques',
            'subtitle' => 'Choisissez une catégorie de boutique pour filtrer les produits de façon plus visuelle.',
        ])
    </div>

    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <div class="text-lg font-extrabold tracking-wide">Résultats</div>
            <div class="store-muted text-sm">{{ $produits->total() }} produit(s)</div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2 sm:gap-3">
            @forelse($produits as $produit)
                @include('store.partials.product-card', ['produit' => $produit, 'client' => $client])
            @empty
                <div class="store-panel col-span-full p-10 text-center store-muted">
                    Aucun produit trouvé.
                </div>
            @endforelse
        </div>

        <div class="pt-2">
            {{ $produits->links() }}
        </div>
    </div>
</div>
@endsection
