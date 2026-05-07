@extends('layouts.fournisseur')

@section('content')
<div class="space-y-4">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
        <form method="GET" action="{{ url('/fournisseur/produits') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3 w-full lg:w-auto">
            <div class="md:col-span-2">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-white/50"></i>
                    <input name="q"
                           value="{{ $q }}"
                           placeholder="Rechercher désignation ou référence..."
                           class="w-full rounded-2xl border border-white/10 bg-[var(--frs-card)] pl-11 pr-4 py-3 outline-none focus:border-[var(--frs-primary)]">
                </div>
            </div>

            <div>
                <select name="categorie"
                        class="w-full rounded-2xl border border-white/10 bg-[var(--frs-card)] px-4 py-3 outline-none focus:border-[var(--frs-primary)]">
                    <option value="">Toutes catégories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" @selected($categorie === $cat)>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-3 flex gap-2">
                <button class="flex-1 rounded-2xl px-4 py-3 font-bold text-white"
                        style="background: linear-gradient(135deg, var(--frs-primary), #0A3D7A);">
                    Filtrer
                </button>
                <a href="{{ url('/fournisseur/produits') }}"
                   class="rounded-2xl px-4 py-3 font-bold border border-white/10 hover:bg-white/10">
                    Reset
                </a>
            </div>
        </form>

        <a href="{{ url('/fournisseur/produits/create') }}"
           class="inline-flex items-center justify-center gap-2 rounded-2xl px-4 py-3 font-bold text-white"
           style="background: linear-gradient(135deg, var(--frs-primary), #0A3D7A);">
            <i class="fa-solid fa-plus"></i>
            Nouveau produit
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($produits as $p)
            @php
                $stock = (int) $p->stock;
                $stockBadge = $stock === 0
                    ? ['Rupture', 'bg-red-500/15 text-red-300 border border-red-400/20']
                    : ($stock < 5
                        ? ['Stock faible', 'bg-amber-500/15 text-amber-300 border border-amber-400/20']
                        : ['Disponible', 'bg-emerald-500/15 text-emerald-300 border border-emerald-400/20']);
            @endphp

            <div class="rounded-2xl border border-white/10 bg-[var(--frs-card)] overflow-hidden">
                <div class="relative h-44 bg-black/20">
                    @if($p->image_principale)
                        <img src="{{ $p->image_principale }}" class="h-44 w-full object-cover" alt="">
                    @else
                        <div class="h-44 w-full flex items-center justify-center text-white/40">
                            <i class="fa-solid fa-image text-3xl"></i>
                        </div>
                    @endif

                    <div class="absolute top-3 left-3 flex items-center gap-2">
                        <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $stockBadge[1] }}">{{ $stockBadge[0] }} ({{ $stock }})</span>
                        <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ (int)$p->actif === 1 ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-400/20' : 'bg-red-500/15 text-red-300 border border-red-400/20' }}">
                            {{ (int)$p->actif === 1 ? 'Actif' : 'Inactif' }}
                        </span>
                    </div>
                </div>

                <div class="p-5">
                    <div class="font-extrabold text-lg truncate">{{ $p->designation }}</div>
                    <div class="text-sm text-white/60 mt-1 truncate">{{ $p->reference }} • {{ $p->categorie }}</div>

                    <div class="mt-4 flex items-center justify-between">
                        <div>
                            <div class="text-xs text-white/60">Prix</div>
                            <div class="font-extrabold">{{ number_format((float)$p->pv_1, 2, '.', ' ') }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-white/60">Stock</div>
                            <div class="font-extrabold">{{ $stock }}</div>
                        </div>
                    </div>

                    <div class="mt-5 flex items-center gap-2">
                        <a href="{{ url('/fournisseur/produits/'.$p->id.'/edit') }}"
                           class="flex-1 rounded-xl px-3 py-2 text-sm font-bold border border-white/10 hover:bg-white/10 text-center">
                            Modifier
                        </a>

                        <form method="POST" action="{{ url('/fournisseur/produits/'.$p->id.'/toggle-actif') }}">
                            @csrf
                            <button type="submit"
                                    class="rounded-xl px-3 py-2 text-sm font-bold border border-white/10 hover:bg-white/10"
                                    title="Activer/Désactiver">
                                <i class="fa-solid {{ (int)$p->actif === 1 ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                            </button>
                        </form>

                        <form method="POST" action="{{ url('/fournisseur/produits/'.$p->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    onclick="return confirm('Supprimer ce produit ?')"
                                    class="rounded-xl px-3 py-2 text-sm font-bold border border-red-400/20 text-red-300 hover:bg-red-500/10"
                                    title="Supprimer">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-2xl border border-white/10 bg-[var(--frs-card)] p-10 text-center text-white/60">
                Aucun produit.
            </div>
        @endforelse
    </div>

    <div>
        {{ $produits->links() }}
    </div>
</div>
@endsection
