@extends('store.layout')

@section('content')
@php
    $continueUrl = (bool) ($storefront_mode ?? false)
        ? ($storefront_home_url ?: url('/'))
        : url('/');
    $productBaseUrl = null;

    if ((bool) ($storefront_mode ?? false) && (bool) ($custom_domain_mode ?? false)) {
        $productBaseUrl = url('/produits/__PRODUCT__');
    } elseif ((bool) ($storefront_mode ?? false) && ($storefront_boutique?->storefront_slug ?? '') !== '') {
        $productBaseUrl = route('storefront.produit', ['slug' => $storefront_boutique->storefront_slug, 'id' => '__PRODUCT__']);
    }
@endphp
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="text-2xl font-extrabold tracking-wide">Panier</div>
            <div class="store-muted mt-1 text-sm">
                @if($boutique)
                    Boutique: <span class="font-semibold" style="color: var(--store-text);">{{ $boutique->nom_frs }}</span>
                @else
                    —
                @endif
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ $continueUrl }}"
               class="store-surface inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold hover:opacity-95">
                <i class="fa-solid fa-store text-[var(--store-primary)]"></i>
                Continuer
            </a>
            @if(count($items) > 0)
                <form method="POST" action="{{ url('/panier/clear') }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold border border-red-200 bg-red-50 text-red-700 hover:bg-red-100">
                        <i class="fa-solid fa-trash-can"></i>
                        Vider
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(count($items) === 0)
        <div class="store-panel p-10 text-center store-muted">
            Votre panier est vide.
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="lg:col-span-2 space-y-3">
                @foreach($items as $it)
                    @php($p = $it['produit'])
                    <div class="store-panel p-4 flex items-start gap-4">
                        <a href="{{ $productBaseUrl ? str_replace('__PRODUCT__', (string) $p->id, $productBaseUrl) : url('/produits/'.$p->id) }}" class="h-20 w-28 rounded-xl overflow-hidden border flex-shrink-0" style="border-color: var(--store-border); background: var(--store-card-soft);">
                            @if(($it['image'] ?? '') !== '')
                                <img src="{{ $it['image'] }}" alt="" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-slate-400">
                                    <i class="fa-regular fa-image"></i>
                                </div>
                            @endif
                        </a>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <a href="{{ $productBaseUrl ? str_replace('__PRODUCT__', (string) $p->id, $productBaseUrl) : url('/produits/'.$p->id) }}" class="font-extrabold hover:underline block truncate">
                                        {{ $p->designation }}
                                    </a>
                                    <div class="store-muted mt-1 text-sm">Ref: {{ $p->reference }}</div>
                                    <div class="store-muted mt-1 text-xs">{{ number_format((float)$it['prix_unitaire'], 2, '.', ' ') }} DA</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-extrabold">{{ number_format((float)$it['line_total'], 2, '.', ' ') }} DA</div>
                                    <div class="store-muted text-xs">Stock: {{ (int)$p->stock }}</div>
                                </div>
                            </div>

                            <div class="mt-3 flex items-center justify-between gap-2">
                                <form method="POST" action="{{ url('/panier/update') }}" class="flex items-center gap-2">
                                    @csrf
                                    <input type="hidden" name="produit_id" value="{{ $p->id }}">
                                    <input type="number"
                                           name="qty"
                                           min="1"
                                           max="{{ max(1, (int)$p->stock) }}"
                                           value="{{ (int)$it['qty'] }}"
                                           class="store-input w-24 px-3 py-2">
                                    <button type="submit"
                                            class="store-surface rounded-xl px-3 py-2 text-sm font-bold hover:opacity-95">
                                        Mettre à jour
                                    </button>
                                </form>

                                <form method="POST" action="{{ url('/panier/remove') }}">
                                    @csrf
                                    <input type="hidden" name="produit_id" value="{{ $p->id }}">
                                    <button type="submit"
                                            class="rounded-xl px-3 py-2 text-sm font-bold border border-red-200 bg-red-50 text-red-700 hover:bg-red-100">
                                        Supprimer
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="store-panel p-6 h-fit">
                <div class="text-lg font-extrabold tracking-wide">Récapitulatif</div>
                <div class="store-muted mt-4 flex items-center justify-between">
                    <span>Total</span>
                    <span class="font-extrabold" style="color: var(--store-text);">{{ number_format((float)$total, 2, '.', ' ') }} DA</span>
                </div>

                <div class="mt-5">
                    <a href="{{ url('/checkout') }}"
                       class="store-button-primary w-full inline-flex items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-extrabold">
                        <i class="fa-solid fa-lock"></i>
                        Commander
                    </a>
                </div>

                <div class="store-muted mt-3 text-xs">
                    Les commandes sont créées pour une seule boutique à la fois.
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
