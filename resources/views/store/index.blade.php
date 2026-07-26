@extends('store.layout')

@section('content')
<div class="space-y-6">
    <div class="store-panel p-4 sm:p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <div class="text-2xl font-extrabold tracking-wide">{{ __('Boutiques & Produits') }}</div>
                <div class="store-muted mt-1 text-sm">
                    @if(($client ?? null))
                        {{ $client->display_name }}
                        <span class="mx-2 opacity-40">•</span>
                        <span class="font-semibold">{{ $client->type_client }}</span>
                    @else
                        {{ __('Recherchez une boutique ou découvrez des produits selon la catégorie de boutique.') }}
                    @endif
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <div class="store-soft px-4 py-3">
                    <div class="store-muted text-xs">{{ __('Panier') }}</div>
                    <div class="font-extrabold">{{ $cart_count }} {{ __('produit(s)') }}</div>
                </div>
                <div class="store-soft px-4 py-3">
                    <div class="store-muted text-xs">{{ __('Total') }}</div>
                    <div class="font-extrabold">{{ number_format((float) $cart_total, 2, '.', ' ') }} DA</div>
                </div>
            </div>
        </div>

        <form method="GET" action="{{ url('/') }}" class="mt-5">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 store-muted"></i>
                <input name="q"
                       value="{{ $q }}"
                       placeholder="{{ __('Rechercher une boutique ou un produit...') }}"
                       class="store-input w-full pl-11 pr-4 py-3">
            </div>
            <button class="hidden" type="submit">{{ __('Rechercher') }}</button>
        </form>

        @include('store.partials.boutique-category-grid', [
            'categories' => $boutique_categories ?? collect(),
            'selectedCategoryId' => $selected_boutique_category,
            'currentUrl' => url('/'),
            'query' => $q,
            'title' => __('Catégories boutiques'),
            'subtitle' => __('Choisissez une catégorie visuellement pour affiner les boutiques et produits affichés.'),
        ])
    </div>

    <div class="space-y-3">
        <div class="flex items-center justify-between gap-3">
            <div>
                <div class="text-lg font-extrabold tracking-wide">{{ __('Boutiques') }}</div>
                <div class="store-muted text-sm">{{ __('Aperçu rapide des boutiques disponibles') }}</div>
            </div>
            <a href="{{ url('/boutiques').'?'.http_build_query(array_filter(['q' => $q, 'categorie_boutique' => $selected_boutique_category])) }}"
               class="store-link text-sm font-semibold hover:underline">
                {{ __('Afficher tous') }}
            </a>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-3">
            @forelse($boutiques_preview as $boutique)
                @include('store.partials.boutique-card', ['boutique' => $boutique])
            @empty
                <div class="store-panel col-span-full p-10 text-center store-muted">
                    {{ __('Aucune boutique trouvée.') }}
                </div>
            @endforelse
        </div>
    </div>

    <div class="space-y-3">
        <div class="flex items-center justify-between gap-3">
            <div>
                <div class="text-lg font-extrabold tracking-wide">{{ __('Produits') }}</div>
                <div class="store-muted text-sm">{{ __('Sélection aléatoire des produits des boutiques correspondantes') }}</div>
            </div>
            <a href="{{ url('/produits').'?'.http_build_query(array_filter(['q' => $q, 'categorie_boutique' => $selected_boutique_category])) }}"
               class="store-link text-sm font-semibold hover:underline">
                {{ __('Afficher tous') }}
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2 sm:gap-3">
            @forelse($produits as $produit)
                @include('store.partials.product-card', ['produit' => $produit, 'client' => $client])
            @empty
                <div class="store-panel col-span-full p-10 text-center store-muted">
                    {{ __('Aucun produit trouvé.') }}
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
