@php
    $boutique = $boutique ?? null;
    $boutiqueUrl = ($boutique && ($boutique->storefront_url ?? '') !== '')
        ? $boutique->storefront_url
        : ($boutique ? url('/boutiques/'.$boutique->id) : '#');
@endphp

@if($boutique)
    <a href="{{ $boutiqueUrl }}"
       class="store-panel group block w-[220px] min-w-[220px] p-3 transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_16px_30px_rgba(37,99,235,0.16)]">
        <div class="flex items-start gap-2.5">
            @if(($boutique->logo_url ?? '') !== '')
                <img src="{{ $boutique->logo_url }}"
                     alt="{{ $boutique->nom_frs }}"
                     class="h-9 w-9 rounded-xl object-cover border border-[color:var(--store-border)] bg-white flex-shrink-0 shadow-sm">
            @else
                <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-[linear-gradient(135deg,var(--store-primary),var(--store-primary-dark))] shadow-sm">
                    <i class="fa-solid fa-store text-sm text-white"></i>
                </div>
            @endif

            <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <div class="truncate text-sm font-extrabold leading-5 text-[color:var(--store-text)]">{{ $boutique->nom_frs }}</div>
                        <div class="store-muted mt-0.5 text-[11px] leading-4">{{ __('Boutique') }}</div>
                    </div>
                    @if(($boutique->boutiqueCategory?->name ?? '') !== '')
                        <span class="store-badge inline-flex max-w-[8rem] items-center truncate rounded-full px-2 py-0.5 text-[10px] font-bold">
                            {{ $boutique->boutiqueCategory->name }}
                        </span>
                    @endif
                </div>

                <div class="mt-2 space-y-1.5">
                    <div class="store-soft flex items-center gap-1.5 rounded-xl px-2 py-1.5 text-[11px] leading-4 text-[color:var(--store-text)]">
                        <i class="fa-solid fa-location-dot text-[10px] text-[var(--store-primary)]"></i>
                        <span class="truncate">{{ $boutique->adresse ?? '—' }}</span>
                    </div>
                    <div class="store-soft flex items-center gap-1.5 rounded-xl px-2 py-1.5 text-[11px] leading-4 text-[color:var(--store-text)]">
                        <i class="fa-solid fa-phone text-[10px] text-[var(--store-primary)]"></i>
                        <span class="keep-ltr-inline truncate">{{ $boutique->telephone ?? '—' }}</span>
                    </div>
                </div>

                <div class="store-link mt-2.5 inline-flex items-center gap-1.5 text-[11px] font-bold transition group-hover:gap-2">
                    <span>{{ __('Voir produits') }}</span>
                    <i class="fa-solid fa-arrow-right-long text-[10px]"></i>
                </div>
            </div>
        </div>
    </a>
@endif
