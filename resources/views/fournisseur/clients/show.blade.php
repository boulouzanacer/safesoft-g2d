@extends('layouts.fournisseur')

@section('content')
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
        <div class="rounded-2xl border border-white/10 bg-[var(--frs-card)] p-5">
            <div class="text-sm text-white/60">Code client</div>
            <div class="font-extrabold mt-1">{{ $client->code_client ?? '-' }}</div>
        </div>
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
