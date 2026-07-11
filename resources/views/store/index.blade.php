@extends('store.layout')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-[var(--store-card)] p-4 sm:p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <div class="text-2xl font-extrabold tracking-wide">Boutiques & Produits</div>
                <div class="mt-1 text-sm text-slate-600">
                    @if(($client ?? null))
                        {{ $client->prenom }} {{ $client->nom }}
                        <span class="mx-2 text-slate-300">•</span>
                        <span class="font-semibold text-slate-900">{{ $client->type_client }}</span>
                    @else
                        Recherchez une boutique ou découvrez des produits selon la catégorie de boutique.
                    @endif
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <div class="text-xs text-slate-500">Panier</div>
                    <div class="font-extrabold">{{ $cart_count }} produit(s)</div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <div class="text-xs text-slate-500">Total</div>
                    <div class="font-extrabold">{{ number_format((float) $cart_total, 2, '.', ' ') }} DA</div>
                </div>
            </div>
        </div>

        <form method="GET" action="{{ url('/') }}" class="mt-5">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input name="q"
                       value="{{ $q }}"
                       placeholder="Rechercher une boutique ou un produit..."
                       class="w-full rounded-2xl border border-slate-200 bg-white pl-11 pr-4 py-3 outline-none focus:border-[var(--store-primary)]">
            </div>
            <button class="hidden" type="submit">Rechercher</button>
        </form>

        @if(($boutique_categories ?? collect())->count() > 0)
            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ url('/').'?'.http_build_query(array_filter(['q' => $q])) }}"
                   class="rounded-full px-3 py-1 text-xs font-bold border {{ ! $selected_boutique_category ? 'border-[var(--store-primary)] bg-blue-50 text-blue-700' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    Toutes les catégories
                </a>
                @foreach($boutique_categories as $category)
                    <a href="{{ url('/').'?'.http_build_query(array_filter(['q' => $q, 'categorie_boutique' => $category->id])) }}"
                       class="rounded-full px-3 py-1 text-xs font-bold border {{ (int) $selected_boutique_category === (int) $category->id ? 'border-[var(--store-primary)] bg-blue-50 text-blue-700' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <div class="space-y-3">
        <div class="flex items-center justify-between gap-3">
            <div>
                <div class="text-lg font-extrabold tracking-wide">Boutiques</div>
                <div class="text-sm text-slate-500">Aperçu rapide des boutiques disponibles</div>
            </div>
            <a href="{{ url('/boutiques').'?'.http_build_query(array_filter(['q' => $q, 'categorie_boutique' => $selected_boutique_category])) }}"
               class="text-sm font-semibold text-[var(--store-primary)] hover:underline">
                Afficher tous
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            @forelse($boutiques_preview as $boutique)
                @include('store.partials.boutique-card', ['boutique' => $boutique])
            @empty
                <div class="col-span-full rounded-2xl border border-slate-200 bg-[var(--store-card)] p-10 text-center text-slate-600">
                    Aucune boutique trouvée.
                </div>
            @endforelse
        </div>
    </div>

    <div class="space-y-3">
        <div class="flex items-center justify-between gap-3">
            <div>
                <div class="text-lg font-extrabold tracking-wide">Produits</div>
                <div class="text-sm text-slate-500">Sélection aléatoire des produits des boutiques correspondantes</div>
            </div>
            <a href="{{ url('/produits').'?'.http_build_query(array_filter(['q' => $q, 'categorie_boutique' => $selected_boutique_category])) }}"
               class="text-sm font-semibold text-[var(--store-primary)] hover:underline">
                Afficher tous
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2 sm:gap-3">
            @forelse($produits as $produit)
                @include('store.partials.product-card', ['produit' => $produit, 'client' => $client])
            @empty
                <div class="col-span-full rounded-2xl border border-slate-200 bg-[var(--store-card)] p-10 text-center text-slate-600">
                    Aucun produit trouvé.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
