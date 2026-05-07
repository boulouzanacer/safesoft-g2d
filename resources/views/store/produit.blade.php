@extends('store.layout')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ url('/boutiques/'.$produit->id_frs) }}" class="text-sm text-slate-500 hover:text-slate-900">
            <i class="fa-solid fa-arrow-left-long mr-2"></i>
            Retour boutique
        </a>
        <a href="{{ url('/panier') }}"
           class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold border border-slate-200 bg-white hover:bg-slate-50">
            <i class="fa-solid fa-cart-shopping text-[var(--store-primary)]"></i>
            <span>Panier</span>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="rounded-2xl border border-slate-200 bg-[var(--store-card)] overflow-hidden">
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
                <div class="p-4 border-t border-slate-200 grid grid-cols-5 gap-2 bg-slate-50">
                    @foreach($images as $u)
                        <img src="{{ $u }}" alt="" class="h-14 w-full object-cover rounded-lg border border-slate-200">
                    @endforeach
                </div>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-200 bg-[var(--store-card)] p-6">
            <div class="text-2xl font-extrabold tracking-wide">{{ $produit->designation }}</div>
            <div class="mt-1 text-sm text-slate-600">Ref: {{ $produit->reference }}</div>
            <div class="mt-1 text-sm text-slate-600">Boutique: {{ $produit->fournisseur?->nom_frs ?? '—' }}</div>

            <div class="mt-4 flex items-center justify-between gap-3">
                <div class="text-2xl font-extrabold">{{ number_format((float)$produit->prix, 2, '.', ' ') }} DA</div>
                <span class="text-xs font-bold px-2.5 py-1 rounded-full border border-slate-200 bg-slate-50 text-slate-600">
                    {{ $produit->categorie ?: '—' }}
                </span>
            </div>

            <div class="mt-2 text-sm {{ (int)$produit->stock > 0 ? 'text-emerald-700' : 'text-red-600' }}">
                {{ (int)$produit->stock > 0 ? ('Stock disponible: '.(int)$produit->stock) : 'Rupture de stock' }}
            </div>

            <div class="mt-5 text-sm text-slate-700 leading-relaxed">
                {{ trim((string)$produit->description) !== '' ? $produit->description : '—' }}
            </div>

            <div class="mt-6">
                <form method="POST" action="{{ url('/panier/add') }}" class="flex items-center gap-2">
                    @csrf
                    <input type="hidden" name="produit_id" value="{{ $produit->id }}">
                    <input type="number"
                           name="qty"
                           min="1"
                           max="{{ max(1, (int)$produit->stock) }}"
                           value="1"
                           class="w-24 rounded-xl border border-slate-200 bg-white px-3 py-2 outline-none focus:border-[var(--store-primary)]">
                    <button type="submit"
                            class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2 text-sm font-extrabold text-white disabled:opacity-40"
                            style="background: linear-gradient(135deg, var(--store-primary), #0A3D7A);"
                            @disabled((int)$produit->stock <= 0)>
                        <i class="fa-solid fa-cart-plus"></i>
                        Ajouter au panier
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
