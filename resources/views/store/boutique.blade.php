@extends('store.layout')

@section('content')
@php
    $isStorefrontMode = (bool) ($storefront_mode ?? false);
    $isCustomDomainMode = (bool) ($custom_domain_mode ?? false);
    $boutiquePageUrl = $boutique_page_url ?? ($isStorefrontMode ? ($boutique->storefront_url ?? url('/boutiques/'.$boutique->id)) : url('/boutiques/'.$boutique->id));
@endphp
<div class="space-y-6">
    <div class="store-panel p-4 sm:p-6">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
            <div>
                @unless($isStorefrontMode)
                    <a href="{{ url('/') }}" class="store-muted text-sm hover:opacity-90">
                        <i class="fa-solid fa-arrow-left-long mr-2"></i>
                        Retour au store
                    </a>
                @endunless
                <div class="mt-3 flex items-start gap-4">
                    @if(($boutique->logo_url ?? '') !== '')
                        <img src="{{ $boutique->logo_url }}"
                             alt=""
                             class="h-14 w-14 rounded-2xl object-cover border flex-shrink-0"
                             style="border-color: var(--store-border); background: #fff;">
                    @else
                        <div class="h-14 w-14 rounded-2xl flex items-center justify-center text-white flex-shrink-0"
                             style="background: linear-gradient(135deg, var(--store-primary), var(--store-primary-dark));">
                            <i class="fa-solid fa-store"></i>
                        </div>
                    @endif
                    <div class="min-w-0">
                        <div class="text-2xl font-extrabold tracking-wide truncate">{{ $boutique->nom_frs }}</div>
                        @if(($boutique->boutiqueCategory?->name ?? '') !== '')
                            <div class="mt-2">
                                <span class="store-badge inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold">
                                    {{ $boutique->boutiqueCategory->name }}
                                </span>
                            </div>
                        @endif
                        <div class="store-muted mt-1 text-sm">{{ $boutique->adresse ?? '—' }}</div>
                        <div class="store-muted mt-1 text-sm">{{ $boutique->telephone ?? '—' }}</div>
                        @if(($boutique->latitude ?? null) && ($boutique->longitude ?? null))
                            <a href="https://www.google.com/maps?q={{ $boutique->latitude }},{{ $boutique->longitude }}"
                               target="_blank"
                               class="store-link mt-1 inline-flex items-center gap-2 text-sm font-semibold hover:underline">
                                <i class="fa-solid fa-location-dot"></i>
                                Voir sur Maps
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ url('/panier') }}"
                   class="store-surface inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold hover:opacity-95">
                    <i class="fa-solid fa-cart-shopping text-[var(--store-primary)]"></i>
                    <span>Panier</span>
                </a>
            </div>
        </div>

        <form method="GET" action="{{ $boutiquePageUrl }}" class="mt-5 grid grid-cols-1 lg:grid-cols-3 gap-3">
            <div class="lg:col-span-2">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 store-muted"></i>
                    <input name="q"
                           value="{{ $q }}"
                           placeholder="Rechercher référence/désignation/catégorie..."
                           class="store-input w-full pl-11 pr-4 py-3">
                </div>
            </div>
            <div>
                <select name="categorie" class="store-input w-full px-4 py-3">
                    <option value="">Toutes catégories</option>
                    @foreach($categories as $c)
                        <option value="{{ $c }}" @selected((string)$selected_categorie === (string)$c)>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <button class="hidden" type="submit">Filter</button>
        </form>

        @if(($categories ?? collect())->count() > 0)
            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ $boutiquePageUrl.'?'.http_build_query(array_filter(['q' => $q])) }}"
                   class="rounded-full px-3 py-1 text-xs font-bold border {{ $selected_categorie === '' ? 'store-chip-active' : 'store-surface store-muted hover:opacity-95' }}">
                    Toutes catégories
                </a>
                @foreach($categories as $category)
                    <a href="{{ $boutiquePageUrl.'?'.http_build_query(array_filter(['q' => $q, 'categorie' => $category])) }}"
                       class="rounded-full px-3 py-1 text-xs font-bold border {{ (string) $selected_categorie === (string) $category ? 'store-chip-active' : 'store-surface store-muted hover:opacity-95' }}">
                        {{ $category }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <div class="text-lg font-extrabold tracking-wide">Produits</div>
            <div class="store-muted text-sm">{{ $produits->total() }} produit(s)</div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2 sm:gap-3">
            @forelse($produits as $p)
                @php
                    $raw = trim((string)($p->image_principale ?? ''));
                    $img = '';
                    if ($raw !== '') {
                        $lower = strtolower($raw);
                        if (str_starts_with($lower, 'http://') || str_starts_with($lower, 'https://')) $img = $raw;
                        elseif (str_starts_with($raw, '/')) $img = url($raw);
                        else $img = url('/'.$raw);
                    }
                @endphp
                <div class="store-panel rounded-xl sm:rounded-2xl overflow-hidden">
                    <a href="{{ $isStorefrontMode ? ($isCustomDomainMode ? url('/produits/'.$p->id) : route('storefront.produit', ['slug' => $boutique->storefront_slug, 'id' => $p->id])) : url('/produits/'.$p->id) }}" class="block">
                        <div class="aspect-square sm:aspect-[4/3] bg-slate-100">
                            @if($img !== '')
                                <img src="{{ $img }}" alt="" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-slate-400">
                                    <i class="fa-regular fa-image text-2xl"></i>
                                </div>
                            @endif
                        </div>
                    </a>
                    <div class="p-2 sm:p-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <a href="{{ $isStorefrontMode ? ($isCustomDomainMode ? url('/produits/'.$p->id) : route('storefront.produit', ['slug' => $boutique->storefront_slug, 'id' => $p->id])) : url('/produits/'.$p->id) }}" class="block font-extrabold text-[13px] sm:text-sm leading-snug hover:underline truncate">
                                    {{ $p->designation }}
                                </a>
                                <div class="store-muted mt-1 text-xs">Ref: {{ $p->reference }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-extrabold text-xs sm:text-sm">{{ number_format((float)$p->prixUnitairePourQuantite($client ?? null, 1), 2, '.', ' ') }} DA</div>
                                <div class="hidden sm:block text-[11px] {{ (int)$p->stock > 0 ? 'text-emerald-700' : 'text-red-600' }}">
                                    {{ (int)$p->stock > 0 ? ('Stock: '.(int)$p->stock) : 'Rupture' }}
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center justify-between gap-2">
                            <span class="store-soft store-muted hidden sm:inline-flex text-[11px] font-bold px-2 py-1 rounded-full">
                                {{ $p->categorie ?: '—' }}
                            </span>
                            <form method="POST" action="{{ url('/panier/add') }}">
                                @csrf
                                <input type="hidden" name="produit_id" value="{{ $p->id }}">
                                <input type="hidden" name="qty" value="1">
                                <button type="submit"
                                        aria-label="Ajouter au panier"
                                        class="inline-flex items-center justify-center gap-2 rounded-xl w-9 h-9 sm:w-auto sm:h-auto sm:px-2.5 sm:py-2 text-xs font-extrabold text-white disabled:opacity-40"
                                        style="background: linear-gradient(135deg, var(--store-primary), var(--store-primary-dark));"
                                        @disabled((int)$p->stock <= 0)>
                                    <i class="fa-solid fa-cart-plus"></i>
                                    <span class="sr-only sm:not-sr-only">Ajouter</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="store-panel col-span-full p-10 text-center store-muted">
                    Aucun produit.
                </div>
            @endforelse
        </div>

        <div class="pt-2">
            {{ $produits->links() }}
        </div>
    </div>
</div>
@endsection
