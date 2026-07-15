@extends('store.layout')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-[var(--store-card)] p-4 sm:p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <div class="text-2xl font-extrabold tracking-wide">Toutes les boutiques</div>
                <div class="mt-1 text-sm text-slate-600">Recherche par nom de boutique et filtre par catégorie de boutique.</div>
            </div>
            <a href="{{ url('/') }}" class="text-sm font-semibold text-[var(--store-primary)] hover:underline">
                Retour accueil
            </a>
        </div>

        <form method="GET" action="{{ url('/boutiques') }}" class="mt-5 grid grid-cols-1 lg:grid-cols-[1fr_auto] gap-3">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input name="q"
                       value="{{ $q }}"
                       placeholder="Rechercher une boutique..."
                       class="w-full rounded-2xl border border-slate-200 bg-white pl-11 pr-4 py-3 outline-none focus:border-[var(--store-primary)]">
            </div>
            <button class="hidden" type="submit">Rechercher</button>
        </form>

        @include('store.partials.boutique-category-grid', [
            'categories' => $boutique_categories ?? collect(),
            'selectedCategoryId' => $selected_boutique_category,
            'currentUrl' => url('/boutiques'),
            'query' => $q,
            'title' => 'Filtrer par catégorie',
            'subtitle' => 'Sélectionnez une catégorie de boutique pour afficher une liste plus claire et ciblée.',
        ])
    </div>

    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <div class="text-lg font-extrabold tracking-wide">Résultats</div>
            <div class="text-sm text-slate-500">{{ $boutiques->total() }} boutique(s)</div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            @forelse($boutiques as $boutique)
                @include('store.partials.boutique-card', ['boutique' => $boutique])
            @empty
                <div class="col-span-full rounded-2xl border border-slate-200 bg-[var(--store-card)] p-10 text-center text-slate-600">
                    Aucune boutique trouvée.
                </div>
            @endforelse
        </div>

        <div class="pt-2">
            {{ $boutiques->links() }}
        </div>
    </div>
</div>
@endsection
