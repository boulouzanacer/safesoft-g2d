@extends('store.layout')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="text-2xl font-extrabold tracking-wide">Panier</div>
            <div class="mt-1 text-sm text-slate-600">
                @if($boutique)
                    Boutique: <span class="font-semibold text-slate-900">{{ $boutique->nom_frs }}</span>
                @else
                    —
                @endif
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ url('/') }}"
               class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold border border-slate-200 bg-white hover:bg-slate-50">
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
        <div class="rounded-2xl border border-slate-200 bg-[var(--store-card)] p-10 text-center text-slate-600">
            Votre panier est vide.
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="lg:col-span-2 space-y-3">
                @foreach($items as $it)
                    @php($p = $it['produit'])
                    <div class="rounded-2xl border border-slate-200 bg-[var(--store-card)] p-4 flex items-start gap-4">
                        <a href="{{ url('/produits/'.$p->id) }}" class="h-20 w-28 rounded-xl overflow-hidden border border-slate-200 bg-slate-100 flex-shrink-0">
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
                                    <a href="{{ url('/produits/'.$p->id) }}" class="font-extrabold hover:underline block truncate">
                                        {{ $p->designation }}
                                    </a>
                                    <div class="mt-1 text-sm text-slate-600">Ref: {{ $p->reference }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ number_format((float)$p->prixPourClient($client ?? null), 2, '.', ' ') }} DA</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-extrabold">{{ number_format((float)$it['line_total'], 2, '.', ' ') }} DA</div>
                                    <div class="text-xs text-slate-500">Stock: {{ (int)$p->stock }}</div>
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
                                           class="w-24 rounded-xl border border-slate-200 bg-white px-3 py-2 outline-none focus:border-[var(--store-primary)]">
                                    <button type="submit"
                                            class="rounded-xl px-3 py-2 text-sm font-bold border border-slate-200 bg-white hover:bg-slate-50">
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

            <div class="rounded-2xl border border-slate-200 bg-[var(--store-card)] p-6 h-fit">
                <div class="text-lg font-extrabold tracking-wide">Récapitulatif</div>
                <div class="mt-4 flex items-center justify-between text-slate-600">
                    <span>Total</span>
                    <span class="font-extrabold text-slate-900">{{ number_format((float)$total, 2, '.', ' ') }} DA</span>
                </div>

                <div class="mt-5">
                    <a href="{{ url('/checkout') }}"
                       class="w-full inline-flex items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-extrabold text-white"
                       style="background: linear-gradient(135deg, var(--store-primary), #0A3D7A);">
                        <i class="fa-solid fa-lock"></i>
                        Commander
                    </a>
                </div>

                <div class="mt-3 text-xs text-slate-500">
                    Les commandes sont créées pour une seule boutique à la fois.
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
