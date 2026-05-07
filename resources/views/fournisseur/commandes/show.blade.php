@extends('layouts.fournisseur')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <div class="text-2xl font-extrabold tracking-wide">Commande #{{ $commande->id }}</div>
            <div class="text-sm text-white/60">{{ \Illuminate\Support\Carbon::parse($commande->date_cmd)->format('d/m/Y H:i') }}</div>
        </div>
        <a href="{{ url('/fournisseur/commandes') }}"
           class="rounded-2xl px-4 py-3 font-bold border border-white/10 hover:bg-white/10">
            Retour
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
        <div class="xl:col-span-2 rounded-2xl border border-white/10 bg-[var(--frs-card)] p-5">
            <div class="font-extrabold tracking-wide mb-3">Client</div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                    <div class="text-white/60">Nom</div>
                    <div class="font-bold mt-1">{{ $client?->prenom }} {{ $client?->nom }}</div>
                </div>
                <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                    <div class="text-white/60">Email</div>
                    <div class="font-bold mt-1">{{ $client?->email }}</div>
                </div>
                <div class="rounded-2xl border border-white/10 bg-black/20 p-4 md:col-span-2">
                    <div class="text-white/60">Adresse livraison</div>
                    <div class="font-bold mt-1">{{ $commande->adresse_livraison }}</div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-white/10 bg-[var(--frs-card)] p-5">
            <div class="font-extrabold tracking-wide mb-3">Statut</div>

            @php
                $steps = ['en_attente', 'confirmee', 'expediee', 'livree'];
                $current = (string) $commande->statut;
                $currentIndex = array_search($current, $steps, true);
            @endphp

            <div class="space-y-3">
                @foreach($steps as $i => $s)
                    @php
                        $done = $currentIndex !== false && $i <= $currentIndex;
                        $isCurrent = $current === $s;
                    @endphp
                    <div class="flex items-center gap-3">
                        <div class="h-9 w-9 rounded-xl flex items-center justify-center border"
                             style="{{ $done ? 'background: linear-gradient(135deg, var(--frs-primary), #0A3D7A); border-color: transparent;' : 'background: rgba(255,255,255,0.06); border-color: rgba(255,255,255,0.10);' }}">
                            <i class="fa-solid {{ $done ? 'fa-check' : 'fa-circle' }} text-white text-xs"></i>
                        </div>
                        <div class="{{ $isCurrent ? 'font-extrabold' : 'font-semibold text-white/80' }}">
                            {{ $s }}
                        </div>
                    </div>
                @endforeach

                @if($current === 'annulee')
                    <div class="flex items-center gap-3">
                        <div class="h-9 w-9 rounded-xl flex items-center justify-center border border-red-400/20 bg-red-500/10">
                            <i class="fa-solid fa-xmark text-red-300 text-xs"></i>
                        </div>
                        <div class="font-extrabold text-red-200">annulee</div>
                    </div>
                @endif
            </div>

            <div class="mt-5 flex items-center justify-between">
                <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ (int)$commande->synced_pme === 1 ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-400/20' : 'bg-amber-500/15 text-amber-300 border border-amber-400/20' }}">
                    {{ (int)$commande->synced_pme === 1 ? 'Synchronisé PME' : 'En attente sync' }}
                </span>
                <span class="text-sm font-extrabold">{{ number_format((float)$commande->montant_total, 2, '.', ' ') }}</span>
            </div>

            <form method="POST" action="{{ url('/fournisseur/commandes/'.$commande->id.'/statut') }}" class="mt-5">
                @csrf
                @method('PUT')
                <label class="block text-sm font-semibold text-white/70 mb-2">Changer statut</label>
                <div class="flex gap-2">
                    <select name="statut"
                            class="flex-1 rounded-2xl border border-white/10 bg-black/20 px-4 py-3 outline-none focus:border-[var(--frs-primary)]">
                        @foreach($statuts as $s)
                            <option value="{{ $s }}" @selected($current === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                    <button type="submit"
                            class="rounded-2xl px-4 py-3 font-extrabold text-white"
                            style="background: linear-gradient(135deg, var(--frs-primary), #0A3D7A);">
                        OK
                    </button>
                </div>
                <div class="mt-2 text-xs text-white/50">Une notification client sera créée lors du changement.</div>
            </form>
        </div>
    </div>

    <div class="rounded-2xl border border-white/10 bg-[var(--frs-card)] overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4">
            <div class="font-extrabold tracking-wide">Produits commandés</div>
            <div class="text-sm text-white/60">{{ $lignes->count() }} lignes</div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-white/60">
                    <tr>
                        <th class="text-left py-3 px-4 font-semibold">Produit</th>
                        <th class="text-right py-3 px-4 font-semibold">Qté</th>
                        <th class="text-right py-3 px-4 font-semibold">Prix unit</th>
                        <th class="text-right py-3 px-4 font-semibold">Sous-total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @foreach($lignes as $l)
                        <tr class="hover:bg-white/5">
                            <td class="py-3 px-4">
                                <div class="font-semibold">{{ $l->produit_designation ?? 'Produit' }}</div>
                                <div class="text-xs text-white/60">{{ $l->produit_reference }}</div>
                            </td>
                            <td class="py-3 px-4 text-right font-extrabold">{{ (int)$l->quantite }}</td>
                            <td class="py-3 px-4 text-right font-extrabold">{{ number_format((float)$l->prix_unitaire, 2, '.', ' ') }}</td>
                            <td class="py-3 px-4 text-right font-extrabold">{{ number_format((float)$l->sous_total, 2, '.', ' ') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t border-white/10">
                        <td colspan="3" class="py-4 px-4 text-right font-extrabold">Total</td>
                        <td class="py-4 px-4 text-right font-extrabold">{{ number_format((float)$commande->montant_total, 2, '.', ' ') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

