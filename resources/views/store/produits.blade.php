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
            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ url('/produits').'?'.http_build_query(array_filter(['q' => $q])) }}"
                   class="rounded-full px-3 py-1 text-xs font-bold border {{ ! $selected_boutique_category ? 'border-[var(--store-primary)] bg-blue-50 text-blue-700' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    Toutes les catégories
                </a>
                @foreach($boutique_categories as $category)
                    <a href="{{ url('/produits').'?'.http_build_query(array_filter(['q' => $q, 'categorie_boutique' => $category->id])) }}"
                       class="rounded-full px-3 py-1 text-xs font-bold border {{ (int) $selected_boutique_category === (int) $category->id ? 'border-[var(--store-primary)] bg-blue-50 text-blue-700' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
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
