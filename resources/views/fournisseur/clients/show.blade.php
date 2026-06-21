@extends('layouts.fournisseur')

@section('content')
@php
    $achatClient = (float) ($client->achat_client ?? 0);
    $versementClient = (float) ($client->versement_client ?? 0);
    $soldeClient = (float) ($client->solde_client ?? 0);
    $soldeBadge = $soldeClient > 0
        ? 'from-red-500/20 to-red-500/5 border-red-400/20 text-red-200'
        : ($soldeClient < 0
            ? 'from-emerald-500/20 to-emerald-500/5 border-emerald-400/20 text-emerald-200'
            : 'from-slate-500/20 to-slate-500/5 border-white/10 text-white');
@endphp
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <div class="text-2xl font-extrabold tracking-wide">{{ $client->prenom }} {{ $client->nom }}</div>
            <div class="text-sm text-white/60">{{ $client->email }}</div>
        </div>
        <a href="{{ url('/fournisseur/clients') }}"
           class="rounded-2xl px-4 py-3 font-bold border border-white/10 hover:bg-white/10">
            Retour
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="rounded-3xl border border-sky-400/20 bg-gradient-to-br from-sky-500/20 to-sky-500/5 p-5 shadow-lg shadow-sky-950/10">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs font-bold uppercase tracking-[0.2em] text-sky-200/80">Achat Client</div>
                    <div class="mt-2 text-3xl font-extrabold text-sky-100">{{ number_format($achatClient, 2, '.', ' ') }}</div>
                </div>
                <div class="h-12 w-12 rounded-2xl bg-sky-500/15 text-sky-200 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-cart-shopping"></i>
                </div>
            </div>
            <div class="mt-3 text-xs text-sky-100/70">Total des achats du client chez ce fournisseur.</div>
        </div>

        <div class="rounded-3xl border border-emerald-400/20 bg-gradient-to-br from-emerald-500/20 to-emerald-500/5 p-5 shadow-lg shadow-emerald-950/10">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-200/80">Versement Client</div>
                    <div class="mt-2 text-3xl font-extrabold text-emerald-100">{{ number_format($versementClient, 2, '.', ' ') }}</div>
                </div>
                <div class="h-12 w-12 rounded-2xl bg-emerald-500/15 text-emerald-200 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-wallet"></i>
                </div>
            </div>
            <div class="mt-3 text-xs text-emerald-100/70">Montant total verse par le client.</div>
        </div>

        <div class="rounded-3xl border bg-gradient-to-br p-5 shadow-lg {{ $soldeBadge }}">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs font-bold uppercase tracking-[0.2em] text-white/70">Solde Client</div>
                    <div class="mt-2 text-3xl font-extrabold">{{ number_format($soldeClient, 2, '.', ' ') }}</div>
                </div>
                <div class="h-12 w-12 rounded-2xl bg-white/10 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-scale-balanced"></i>
                </div>
            </div>
            <div class="mt-3 text-xs text-white/70">
                {{ $soldeClient > 0 ? 'Solde a recouvrer.' : ($soldeClient < 0 ? 'Client crediteur.' : 'Compte equilibre.') }}
            </div>
        </div>
    </div>

        <div class="rounded-2xl border border-white/10 bg-[var(--frs-card)] p-5">
            <div class="text-sm text-white/60">Code client</div>
            <div class="font-extrabold mt-1">{{ $client->code_client ?? '-' }}</div>
        </div>
        <div class="rounded-2xl border border-white/10 bg-[var(--frs-card)] p-5">
        <div class="rounded-2xl border border-white/10 bg-[var(--frs-card)] p-5">
            <div class="text-sm text-white/60">Téléphone</div>
            <div class="font-extrabold mt-1">{{ $client->telephone ?? '-' }}</div>
        </div>
        <div class="rounded-2xl border border-white/10 bg-[var(--frs-card)] p-5">
            <div class="text-sm text-white/60">Type</div>
            <div class="font-extrabold mt-1">{{ $client->type_client }}</div>
        </div>
        <div class="rounded-2xl border border-white/10 bg-[var(--frs-card)] p-5">
            <div class="text-sm text-white/60">Tarif</div>
            <div class="font-extrabold mt-1">{{ (int)($client->tarif ?? 1) }}</div>
        </div>
    </div>

    <div class="rounded-2xl border border-white/10 bg-[var(--frs-card)] p-5">
        <div class="font-extrabold tracking-wide mb-3">Adresse</div>
        <div class="text-white/80">{{ $client->adresse }}</div>
    </div>

    <div class="rounded-2xl border border-white/10 bg-[var(--frs-card)] overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4">
            <div class="font-extrabold tracking-wide">Historique commandes</div>
            <a href="{{ url('/fournisseur/commandes?client='.$client->id) }}" class="text-sm text-[var(--frs-primary)] hover:opacity-90">
                Voir dans commandes
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-white/60">
                    <tr>
                        <th class="text-left py-3 px-4 font-semibold">#</th>
                        <th class="text-left py-3 px-4 font-semibold">Date</th>
                        <th class="text-left py-3 px-4 font-semibold">Statut</th>
                        <th class="text-right py-3 px-4 font-semibold">Montant</th>
                        <th class="text-right py-3 px-4 font-semibold"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse($commandes as $c)
                        @php
                            $badge = match($c->statut) {
                                'en_attente' => 'bg-amber-500/15 text-amber-300 border border-amber-400/20',
                                'confirmee' => 'bg-sky-500/15 text-sky-300 border border-sky-400/20',
                                'expediee' => 'bg-indigo-500/15 text-indigo-300 border border-indigo-400/20',
                                'livree' => 'bg-emerald-500/15 text-emerald-300 border border-emerald-400/20',
                                'annulee' => 'bg-red-500/15 text-red-300 border border-red-400/20',
                                default => 'bg-white/10 text-white/70 border border-white/10'
                            };
                        @endphp
                        <tr class="hover:bg-white/5">
                            <td class="py-3 px-4 font-semibold">#{{ $c->id }}</td>
                            <td class="py-3 px-4 text-white/80">{{ \Illuminate\Support\Carbon::parse($c->date_cmd)->format('d/m/Y H:i') }}</td>
                            <td class="py-3 px-4">
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $badge }}">{{ $c->statut }}</span>
                            </td>
                            <td class="py-3 px-4 text-right font-extrabold">{{ number_format((float)$c->montant_total, 2, '.', ' ') }}</td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ url('/fournisseur/commandes/'.$c->id) }}"
                                   class="rounded-xl px-3 py-2 text-xs font-bold border border-white/10 hover:bg-white/10">
                                    Détail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-white/60">Aucune commande</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4">
            {{ $commandes->links() }}
        </div>
    </div>
</div>
@endsection
