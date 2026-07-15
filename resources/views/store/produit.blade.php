@extends('store.layout')

@section('content')
@php
    $backBoutiqueUrl = $back_boutique_url ?? url('/boutiques/'.$produit->id_frs);
@endphp
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ $backBoutiqueUrl }}" class="store-muted text-sm hover:opacity-90">
            <i class="fa-solid fa-arrow-left-long mr-2"></i>
            Retour boutique
        </a>
        <a href="{{ url('/panier') }}"
           class="store-surface inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold hover:opacity-95">
            <i class="fa-solid fa-cart-shopping text-[var(--store-primary)]"></i>
            <span>Panier</span>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="store-panel overflow-hidden">
            <div class="aspect-[4/3] bg-slate-100">
                @if(count($images) > 0)
                    <img src="{{ $images[0] }}" alt="" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-slate-400">
                        <i class="fa-regular fa-image text-4xl"></i>
                    </div>
                @endif
            </div>
            @if(count($images) > 1)
                <div class="store-soft p-4 border-t grid grid-cols-5 gap-2" style="border-color: var(--store-border); border-top-left-radius: 0; border-top-right-radius: 0;">
                    @foreach($images as $u)
                        <img src="{{ $u }}" alt="" class="h-14 w-full object-cover rounded-lg border" style="border-color: var(--store-border);">
                    @endforeach
                </div>
            @endif
        </div>

        <div class="store-panel p-6">
            <div class="text-2xl font-extrabold tracking-wide">{{ $produit->designation }}</div>
            <div class="store-muted mt-1 text-sm">Ref: {{ $produit->reference }}</div>
            <div class="store-muted mt-1 text-sm">Boutique: {{ $produit->fournisseur?->nom_frs ?? '—' }}</div>

            @php
                $initialQty = (int) ($initialQty ?? 1);
                $initialUnit = (float) ($initialUnit ?? $produit->prixUnitairePourQuantite($client ?? null, $initialQty));

                $tiers = $tiers ?? ($produit->relationLoaded('quantityPrices') ? $produit->quantityPrices : $produit->quantityPrices()->get(['quantity_min', 'quantity_max', 'price']))
                    ->map(fn ($t) => [
                        'quantity_min' => (int) $t->quantity_min,
                        'quantity_max' => $t->quantity_max === null ? null : (int) $t->quantity_max,
                        'price' => (float) $t->price,
                    ])
                    ->values()
                    ->all();

                $tierEnabled = (bool) ($tierEnabled ?? ($produit->isTierPricingEnabled() && count($tiers) > 0));
            @endphp

            <div class="mt-4 flex items-center justify-between gap-3">
                <div class="text-2xl font-extrabold">
                    <span id="unitPrice">{{ number_format($initialUnit, 2, '.', ' ') }}</span> DA
                </div>
                <span class="store-soft store-muted text-xs font-bold px-2.5 py-1 rounded-full">
                    {{ $produit->categorie ?: '—' }}
                </span>
            </div>
            <div class="store-muted mt-1 text-xs">
                Total: <span class="font-bold" style="color: var(--store-text);"><span id="totalPrice">{{ number_format($initialUnit * $initialQty, 2, '.', ' ') }}</span> DA</span>
            </div>

            <div class="mt-2 text-sm {{ (int)$produit->stock > 0 ? 'text-emerald-700' : 'text-red-600' }}">
                {{ (int)$produit->stock > 0 ? ('Stock disponible: '.(int)$produit->stock) : 'Rupture de stock' }}
            </div>

            <div class="mt-5 text-sm leading-relaxed" style="color: var(--store-text);">
                {{ trim((string)$produit->description) !== '' ? $produit->description : '—' }}
            </div>

            @if($tierEnabled)
                <div class="store-soft mt-6 p-4">
                    <div class="font-extrabold tracking-wide">Tarifs par quantité</div>
                    <div class="mt-3 space-y-2 text-sm">
                        @foreach($tiers as $t)
                            <div class="flex items-center justify-between gap-3">
                                <div class="store-muted">
                                    @if($t['quantity_max'] === null)
                                        {{ (int)$t['quantity_min'] }}+ pièces
                                    @else
                                        {{ (int)$t['quantity_min'] }}-{{ (int)$t['quantity_max'] }} pièces
                                    @endif
                                </div>
                                <div class="font-extrabold">{{ number_format((float)$t['price'], 2, '.', ' ') }} DA</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mt-6">
                <form method="POST" action="{{ url('/panier/add') }}" class="flex items-center gap-2">
                    @csrf
                    <input type="hidden" name="produit_id" value="{{ $produit->id }}">
                    <input type="number"
                           name="qty"
                           id="qtyInput"
                           min="1"
                           max="{{ max(1, (int)$produit->stock) }}"
                           value="1"
                           class="store-input w-24 px-3 py-2">
                    <button type="submit"
                            class="store-button-primary flex-1 inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2 text-sm font-extrabold disabled:opacity-40"
                            @disabled((int)$produit->stock <= 0)>
                        <i class="fa-solid fa-cart-plus"></i>
                        Ajouter au panier
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="hidden js-store-produit-config"
     data-tier-enabled="{{ $tierEnabled ? '1' : '0' }}"
     data-tiers="{{ e(json_encode($tiers)) }}"
     data-initial-unit="{{ $initialUnit }}"></div>

<script>
    (function () {
        const qtyInput = document.getElementById('qtyInput');
        const unitEl = document.getElementById('unitPrice');
        const totalEl = document.getElementById('totalPrice');
        const configElement = document.querySelector('.js-store-produit-config');

        if (!qtyInput || !unitEl || !totalEl || !configElement) return;

        const enableTier = configElement.dataset.tierEnabled === '1';
        const tiers = JSON.parse(configElement.dataset.tiers || '[]');
        const baseUnit = Number(configElement.dataset.initialUnit || 0);

        function matchTier(qty) {
            if (!enableTier) return null;
            const sorted = [...tiers].sort((a, b) => Number(a.quantity_min) - Number(b.quantity_min));
            for (let i = sorted.length - 1; i >= 0; i--) {
                const t = sorted[i];
                const min = Number(t.quantity_min);
                const max = (t.quantity_max === null || t.quantity_max === '') ? null : Number(t.quantity_max);
                if (qty < min) continue;
                if (max === null || qty <= max) return Number(t.price);
            }
            return null;
        }

        function fmt(v) {
            const n = Number(v);
            if (!Number.isFinite(n)) return '0,00';
            return n.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function update() {
            const qty = Math.max(1, Number(qtyInput.value || 1));
            const unit = matchTier(qty) ?? baseUnit;
            unitEl.textContent = fmt(unit);
            totalEl.textContent = fmt(unit * qty);
        }

        qtyInput.addEventListener('input', update);
        update();
    })();
</script>
@endsection
