@extends('store.layout')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-[var(--store-card)] p-4 sm:p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <div class="text-2xl font-extrabold tracking-wide">Tous les produits</div>
                <div class="mt-1 text-sm text-slate-600">Recherche produit avec filtre par catégorie de boutique.</div>
            </div>
            <a href="{{ url('/') }}" class="text-sm font-semibold text-[var(--store-primary)] hover:underline">
                Retour accueil
            </a>
        </div>

        <form method="GET" action="{{ url('/produits') }}" class="mt-5 grid grid-cols-1 lg:grid-cols-[1fr_auto] gap-3">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input name="q"
                       value="{{ $q }}"
                       placeholder="Rechercher un produit ou une boutique..."
                       class="w-full rounded-2xl border border-slate-200 bg-white pl-11 pr-4 py-3 outline-none focus:border-[var(--store-primary)]">
            </div>
            <button class="hidden" type="submit">Rechercher</button>
        </form>

        @if(($boutique_categories ?? collect())->count() > 0)
            <div class="mt-4 flex flex-wrap gap-3">
                <a href="{{ url('/produits').'?'.http_build_query(array_filter(['q' => $q])) }}"
                   class="inline-flex items-center gap-2 rounded-2xl px-3 py-2 text-xs font-bold border {{ ! $selected_boutique_category ? 'border-[var(--store-primary)] bg-blue-50 text-blue-700' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-slate-100 text-slate-500">
                        <i class="fa-solid fa-grid-2"></i>
                    </span>
                    Toutes les catégories
                </a>
                @foreach($boutique_categories as $category)
                    <a href="{{ url('/produits').'?'.http_build_query(array_filter(['q' => $q, 'categorie_boutique' => $category->id])) }}"
                       class="inline-flex items-center gap-2 rounded-2xl px-3 py-2 text-xs font-bold border {{ (int) $selected_boutique_category === (int) $category->id ? 'border-[var(--store-primary)] bg-blue-50 text-blue-700' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <span class="h-8 w-8 overflow-hidden rounded-xl border border-slate-200 bg-slate-100">
                            @if($category->image_url)
                                <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="h-full w-full object-cover">
                            @else
                                <span class="flex h-full w-full items-center justify-center text-slate-400">
                                    <i class="fa-solid fa-image"></i>
                                </span>
                            @endif
                        </span>
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <div class="text-lg font-extrabold tracking-wide">Résultats</div>
            <div class="text-sm text-slate-500">{{ $produits->total() }} produit(s)</div>
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

        <div class="pt-2">
            {{ $produits->links() }}
        </div>
    </div>
</div>
@endsection
