@extends('layouts.fournisseur')

@section('content')
@php
    $tiers = ($produit->relationLoaded('quantityPrices') ? $produit->quantityPrices : collect())
        ->map(fn ($t) => [
            'quantity_min' => (int) $t->quantity_min,
            'quantity_max' => $t->quantity_max === null ? null : (int) $t->quantity_max,
            'price' => (float) $t->price,
        ])
        ->values()
        ->all();
    $tierEnabled = $produit->isTierPricingEnabled() && count($tiers) > 0;
@endphp

<div class="max-w-7xl space-y-6">
    <div class="rounded-3xl border border-white/10 bg-[var(--frs-card)] p-5 md:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start gap-4">
                <div class="h-16 w-16 shrink-0 rounded-2xl bg-[var(--frs-primary)]/15 text-[var(--frs-primary)] flex items-center justify-center text-2xl">
                    <i class="fa-solid fa-box-open"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-2xl font-extrabold tracking-wide break-words">{{ $produit->designation }}</div>
                    <div class="mt-1 text-sm text-white/60 break-words">{{ $produit->reference ?: 'Sans reference' }}</div>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-bold {{ $tierEnabled ? 'border-sky-400/20 bg-sky-500/15 text-sky-200' : 'border-white/10 bg-white/5 text-white/70' }}">
                            <i class="fa-solid {{ $tierEnabled ? 'fa-check' : 'fa-xmark' }}"></i>
                            Prix par palier {{ $tierEnabled ? 'activé' : 'désactivé' }}
                        </span>
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-bold {{ (int)($produit->actif ?? 0) === 1 ? 'border-emerald-400/20 bg-emerald-500/15 text-emerald-200' : 'border-red-400/20 bg-red-500/15 text-red-200' }}">
                            {{ (int)($produit->actif ?? 0) === 1 ? 'Actif' : 'Inactif' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ url('/fournisseur/produits/'.$produit->id.'/edit') }}"
                   class="rounded-2xl px-4 py-3 font-bold text-white"
                   style="background: linear-gradient(135deg, var(--frs-primary), #0A3D7A);">
                    Modifier
                </a>
                <a href="{{ url('/fournisseur/produits') }}"
                   class="rounded-2xl px-4 py-3 font-bold border border-white/10 hover:bg-white/10">
                    Retour
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="rounded-3xl border border-sky-400/20 bg-gradient-to-br from-sky-500/20 to-sky-500/5 p-5">
            <div class="text-xs font-bold uppercase tracking-[0.2em] text-sky-200/80">Stock</div>
            <div class="mt-2 text-3xl font-extrabold text-sky-100">{{ (int)$produit->stock }}</div>
            <div class="mt-3 text-xs text-sky-100/70">Quantite disponible.</div>
        </div>
        <div class="rounded-3xl border border-emerald-400/20 bg-gradient-to-br from-emerald-500/20 to-emerald-500/5 p-5">
            <div class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-200/80">PV 1</div>
            <div class="mt-2 text-3xl font-extrabold text-emerald-100">{{ number_format((float)$produit->pv_1, 2, '.', ' ') }}</div>
            <div class="mt-3 text-xs text-emerald-100/70">Prix tarif 1.</div>
        </div>
        <div class="rounded-3xl border border-indigo-400/20 bg-gradient-to-br from-indigo-500/20 to-indigo-500/5 p-5">
            <div class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-200/80">PV 2</div>
            <div class="mt-2 text-3xl font-extrabold text-indigo-100">{{ number_format((float)$produit->pv_2, 2, '.', ' ') }}</div>
            <div class="mt-3 text-xs text-indigo-100/70">Prix tarif 2.</div>
        </div>
        <div class="rounded-3xl border border-amber-400/20 bg-gradient-to-br from-amber-500/20 to-amber-500/5 p-5">
            <div class="text-xs font-bold uppercase tracking-[0.2em] text-amber-200/80">PV 3</div>
            <div class="mt-2 text-3xl font-extrabold text-amber-100">{{ number_format((float)$produit->pv_3, 2, '.', ' ') }}</div>
            <div class="mt-3 text-xs text-amber-100/70">Prix tarif 3.</div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 rounded-3xl border border-white/10 bg-[var(--frs-card)] p-5 md:p-6">
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
                <div class="rounded-3xl border border-white/10 bg-black/20 overflow-hidden">
                    @php
                        $main = trim((string) ($produit->image_principale ?? ''));
                    @endphp
                    <div class="h-72 bg-black/20">
                        @if($main !== '')
                            <img src="{{ $main }}" alt="" class="h-full w-full object-cover">
                        @else
                            <div class="h-full w-full flex items-center justify-center text-white/40">
                                <i class="fa-solid fa-image text-3xl"></i>
                            </div>
                        @endif
                    </div>
                    @if(isset($images) && count($images) > 0)
                        <div class="p-3 border-t border-white/10 grid grid-cols-4 sm:grid-cols-5 gap-2">
                            @foreach($images as $img)
                                @php($u = trim((string)($img->url_principale ?? '')))
                                @if($u !== '')
                                    <img src="{{ $u }}" alt="" class="h-14 w-full object-cover rounded-xl border border-white/10">
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="space-y-4">
                    <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                        <div class="text-xs font-bold uppercase tracking-wide text-white/50">Categorie</div>
                        <div class="mt-2 font-extrabold break-words">{{ $produit->categorie ?: '—' }}</div>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                        <div class="text-xs font-bold uppercase tracking-wide text-white/50">Reference</div>
                        <div class="mt-2 font-extrabold break-words">{{ $produit->reference ?: '—' }}</div>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                        <div class="text-xs font-bold uppercase tracking-wide text-white/50">Description</div>
                        <div class="mt-2 text-sm text-white/75 leading-relaxed break-words">
                            {{ trim((string)$produit->description) !== '' ? $produit->description : '—' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-white/10 bg-[var(--frs-card)] p-5 md:p-6">
            <div class="font-extrabold tracking-wide">Produit Summary</div>
            <div class="mt-5 space-y-3 text-sm">
                <div class="flex items-center justify-between gap-4 rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                    <span class="text-white/60">Produit ID</span>
                    <span class="font-extrabold">#{{ $produit->id }}</span>
                </div>
                <div class="flex items-center justify-between gap-4 rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                    <span class="text-white/60">Abonne only</span>
                    <span class="font-extrabold {{ (int)($produit->abonne_only ?? 0) === 1 ? 'text-amber-300' : 'text-white' }}">
                        {{ (int)($produit->abonne_only ?? 0) === 1 ? 'Oui' : 'Non' }}
                    </span>
                </div>
                <div class="flex items-center justify-between gap-4 rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                    <span class="text-white/60">Tier pricing</span>
                    <span class="font-extrabold {{ $tierEnabled ? 'text-sky-300' : 'text-white/80' }}">
                        {{ $tierEnabled ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="flex items-center justify-between gap-4 rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                    <span class="text-white/60">Paliers</span>
                    <span class="font-extrabold">{{ count($tiers) }}</span>
                </div>
            </div>
        </div>
    </div>

    @if($tierEnabled)
        <div class="rounded-3xl border border-white/10 bg-[var(--frs-card)] p-5 md:p-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="font-extrabold tracking-wide">Tarifs par quantité</div>
                    <div class="text-sm text-white/50">Liste des paliers appliques pour ce produit.</div>
                </div>
                <div class="text-xs text-white/50">{{ count($tiers) }} palier(s)</div>
            </div>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                @foreach($tiers as $t)
                    <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                        <div class="text-xs font-bold uppercase tracking-wide text-white/50">Quantite</div>
                        <div class="mt-2 font-extrabold text-white/85">
                            @if($t['quantity_max'] === null)
                                {{ (int)$t['quantity_min'] }}+ pièces
                            @else
                                {{ (int)$t['quantity_min'] }}-{{ (int)$t['quantity_max'] }} pièces
                            @endif
                        </div>
                        <div class="mt-3 text-xs font-bold uppercase tracking-wide text-white/50">Prix</div>
                        <div class="mt-1 text-xl font-extrabold">{{ number_format((float)$t['price'], 2, '.', ' ') }} DA</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
