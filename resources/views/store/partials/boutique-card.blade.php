@php
    $boutique = $boutique ?? null;
    $boutiqueUrl = ($boutique && ($boutique->storefront_url ?? '') !== '')
        ? $boutique->storefront_url
        : ($boutique ? url('/boutiques/'.$boutique->id) : '#');
@endphp

@if($boutique)
    <a href="{{ $boutiqueUrl }}"
       class="store-panel block p-5 transition hover:-translate-y-0.5">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="text-lg font-extrabold truncate">{{ $boutique->nom_frs }}</div>
                <div class="mt-2 flex flex-wrap gap-2">
                    @if(($boutique->boutiqueCategory?->name ?? '') !== '')
                        <span class="store-badge inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold">
                            {{ $boutique->boutiqueCategory->name }}
                        </span>
                    @endif
                </div>
                <div class="store-muted mt-3 text-sm">{{ $boutique->adresse ?? '—' }}</div>
                <div class="store-muted mt-1 text-sm">{{ $boutique->telephone ?? '—' }}</div>
            </div>
            @if(($boutique->logo_url ?? '') !== '')
                <img src="{{ $boutique->logo_url }}"
                     alt=""
                     class="h-10 w-10 rounded-xl object-cover border bg-white flex-shrink-0"
                     style="border-color: var(--store-border);">
            @else
                <div class="h-10 w-10 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background: linear-gradient(135deg, var(--store-primary), var(--store-primary-dark));">
                    <i class="fa-solid fa-store text-white"></i>
                </div>
            @endif
        </div>

        <div class="store-link mt-4 inline-flex items-center gap-2 text-sm font-semibold">
            Voir produits
            <i class="fa-solid fa-arrow-right-long"></i>
        </div>
    </a>
@endif
