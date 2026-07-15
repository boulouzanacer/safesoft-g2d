@php
    $boutique = $boutique ?? null;
    $boutiqueUrl = ($boutique && ($boutique->storefront_url ?? '') !== '')
        ? $boutique->storefront_url
        : ($boutique ? url('/boutiques/'.$boutique->id) : '#');
@endphp

@if($boutique)
    <a href="{{ $boutiqueUrl }}"
       class="rounded-2xl border border-slate-200 bg-[var(--store-card)] p-5 hover:bg-slate-50 transition">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="text-lg font-extrabold truncate">{{ $boutique->nom_frs }}</div>
                <div class="mt-2 flex flex-wrap gap-2">
                    @if(($boutique->boutiqueCategory?->name ?? '') !== '')
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold border border-sky-200 bg-sky-50 text-sky-700">
                            {{ $boutique->boutiqueCategory->name }}
                        </span>
                    @endif
                </div>
                <div class="mt-3 text-sm text-slate-600">{{ $boutique->adresse ?? '—' }}</div>
                <div class="mt-1 text-sm text-slate-600">{{ $boutique->telephone ?? '—' }}</div>
            </div>
            @if(($boutique->logo_url ?? '') !== '')
                <img src="{{ $boutique->logo_url }}"
                     alt=""
                     class="h-10 w-10 rounded-xl object-cover border border-slate-200 bg-white flex-shrink-0">
            @else
                <div class="h-10 w-10 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background: linear-gradient(135deg, var(--store-primary), #0A3D7A);">
                    <i class="fa-solid fa-store text-white"></i>
                </div>
            @endif
        </div>

        <div class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-[var(--store-primary)]">
            Voir produits
            <i class="fa-solid fa-arrow-right-long"></i>
        </div>
    </a>
@endif
