@extends('store.layout')

@section('content')
<div class="space-y-6">
    <div class="store-panel p-4 sm:p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <div class="text-2xl font-extrabold tracking-wide">Boutiques & Produits</div>
                <div class="store-muted mt-1 text-sm">
                    @if(($client ?? null))
                        {{ $client->prenom }} {{ $client->nom }}
                        <span class="mx-2 opacity-40">•</span>
                        <span class="font-semibold">{{ $client->type_client }}</span>
                    @else
                        Recherchez une boutique ou découvrez des produits selon la catégorie de boutique.
                    @endif
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <div class="store-soft px-4 py-3">
                    <div class="store-muted text-xs">Panier</div>
                    <div class="font-extrabold">{{ $cart_count }} produit(s)</div>
                </div>
                <div class="store-soft px-4 py-3">
                    <div class="store-muted text-xs">Total</div>
                    <div class="font-extrabold">{{ number_format((float) $cart_total, 2, '.', ' ') }} DA</div>
                </div>
            </div>
        </div>

        <form method="GET" action="{{ url('/') }}" class="mt-5">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 store-muted"></i>
                <input name="q"
                       value="{{ $q }}"
                       placeholder="Rechercher une boutique ou un produit..."
                       class="store-input w-full pl-11 pr-4 py-3">
            </div>
            <button class="hidden" type="submit">Rechercher</button>
        </form>

        @include('store.partials.boutique-category-grid', [
            'categories' => $boutique_categories ?? collect(),
            'selectedCategoryId' => $selected_boutique_category,
            'currentUrl' => url('/'),
            'query' => $q,
            'title' => 'Catégories boutiques',
            'subtitle' => 'Choisissez une catégorie visuellement pour affiner les boutiques et produits affichés.',
        ])
    </div>

    <div class="space-y-3">
        <div class="flex items-center justify-between gap-3">
            <div>
                <div class="text-lg font-extrabold tracking-wide">Boutiques</div>
                <div class="store-muted text-sm">Aperçu rapide des boutiques disponibles</div>
            </div>
            <a href="{{ url('/boutiques').'?'.http_build_query(array_filter(['q' => $q, 'categorie_boutique' => $selected_boutique_category])) }}"
               class="store-link text-sm font-semibold hover:underline">
                Afficher tous
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            @forelse($boutiques_preview as $boutique)
                @include('store.partials.boutique-card', ['boutique' => $boutique])
            @empty
                <div class="store-panel col-span-full p-10 text-center store-muted">
                    Aucune boutique trouvée.
                </div>
            @endforelse
        </div>
    </div>

    <div class="space-y-3">
        <div class="flex items-center justify-between gap-3">
            <div>
                <div class="text-lg font-extrabold tracking-wide">Produits</div>
                <div class="store-muted text-sm">Sélection aléatoire des produits des boutiques correspondantes</div>
            </div>
            <a href="{{ url('/produits').'?'.http_build_query(array_filter(['q' => $q, 'categorie_boutique' => $selected_boutique_category])) }}"
               class="store-link text-sm font-semibold hover:underline">
                Afficher tous
            </a>
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
    </div>
</div>
@endsection
