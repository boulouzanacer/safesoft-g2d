@php
    $produit = $produit ?? null;
    $client = $client ?? null;
    $raw = trim((string) ($produit?->image_principale ?? ''));
    $img = '';

    if ($raw !== '') {
        $lower = strtolower($raw);
        if (str_starts_with($lower, 'http://') || str_starts_with($lower, 'https://')) {
            $img = $raw;
        } elseif (str_starts_with($raw, '/')) {
            $img = url($raw);
        } else {
            $img = url('/'.$raw);
        }
    }
@endphp

@if($produit)
    <div class="store-panel rounded-xl sm:rounded-2xl overflow-hidden">
        <a href="{{ url('/produits/'.$produit->id) }}" class="block">
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
                <div class="min-w-0">
                    <a href="{{ url('/produits/'.$produit->id) }}" class="block font-extrabold text-[13px] sm:text-sm leading-snug hover:underline truncate">
                        {{ $produit->designation }}
                    </a>
                    <div class="store-muted mt-1 text-xs">Ref: {{ $produit->reference }}</div>
                    <div class="store-muted mt-1 text-xs hidden sm:block">Boutique: {{ $produit->fournisseur?->nom_frs ?? '—' }}</div>
                </div>
                <div class="text-right">
                    <div class="font-extrabold text-xs sm:text-sm">{{ number_format((float) $produit->prixUnitairePourQuantite($client, 1), 2, '.', ' ') }} DA</div>
                    <div class="hidden sm:block text-[11px] {{ (int) $produit->stock > 0 ? 'text-emerald-700' : 'text-red-600' }}">
                        {{ (int) $produit->stock > 0 ? ('Stock: '.(int) $produit->stock) : 'Rupture' }}
                    </div>
                </div>
            </div>

            <div class="mt-3 flex items-center justify-between gap-2">
                <div class="hidden sm:flex flex-wrap gap-2">
                    @if(($produit->fournisseur?->boutiqueCategory?->name ?? '') !== '')
                        <span class="store-badge text-[11px] font-bold px-2 py-1 rounded-full">
                            {{ $produit->fournisseur->boutiqueCategory->name }}
                        </span>
                    @endif
                    @if(($produit->categorie ?? '') !== '')
                        <span class="store-soft store-muted text-[11px] font-bold px-2 py-1 rounded-full">
                            {{ $produit->categorie }}
                        </span>
                    @endif
                </div>

                <form method="POST" action="{{ url('/panier/add') }}">
                    @csrf
                    <input type="hidden" name="produit_id" value="{{ $produit->id }}">
                    <input type="hidden" name="qty" value="1">
                    <button type="submit"
                            aria-label="Ajouter au panier"
                            class="inline-flex items-center justify-center gap-2 rounded-xl w-9 h-9 sm:w-auto sm:h-auto sm:px-2.5 sm:py-2 text-xs font-extrabold text-white disabled:opacity-40"
                            style="background: linear-gradient(135deg, var(--store-primary), var(--store-primary-dark));"
                            @disabled((int) $produit->stock <= 0)>
                        <i class="fa-solid fa-cart-plus"></i>
                        <span class="sr-only sm:not-sr-only">Ajouter</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
@endif
