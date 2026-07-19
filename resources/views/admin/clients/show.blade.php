@extends('layouts.admin')

@section('content')
@php
    $achatClient = (float) ($client->achat_client ?? 0);
    $versementClient = (float) ($client->versement_client ?? 0);
    $soldeClient = (float) ($client->solde_client ?? 0);
    $soldeBadge = $soldeClient > 0
        ? 'border-red-400/20 from-red-500/20 to-red-500/5 text-red-200'
        : ($soldeClient < 0
            ? 'border-emerald-400/20 from-emerald-500/20 to-emerald-500/5 text-emerald-200'
            : 'border-white/10 from-slate-500/20 to-slate-500/5 text-white');
    $typeBadge = (string) ($client->type_client ?? '') === 'abonne'
        ? 'border-sky-400/20 bg-sky-500/15 text-sky-200'
        : 'border-white/10 bg-white/5 text-white/80';
    $statusBadge = (int) ($client->actif ?? 0) === 1
        ? 'border-emerald-400/20 bg-emerald-500/15 text-emerald-200'
        : 'border-red-400/20 bg-red-500/15 text-red-200';
@endphp
<div class="max-w-7xl space-y-6">
    <div class="rounded-3xl border border-white/10 bg-[var(--admin-card)] p-5 md:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start gap-4">
                <div class="h-16 w-16 shrink-0 rounded-2xl bg-[var(--admin-primary)]/15 text-[var(--admin-primary)] flex items-center justify-center text-2xl">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-2xl font-extrabold tracking-wide break-words">{{ $client->display_name ?: '-' }}</div>
                    <div class="mt-1 text-sm text-white/60 break-all">{{ $client->email ?: '-' }}</div>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-bold {{ $typeBadge }}">
                            {{ $client->type_client ?: 'client' }}
                        </span>
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-bold {{ $statusBadge }}">
                            {{ (int) ($client->actif ?? 0) === 1 ? 'Actif' : 'Inactif' }}
                        </span>
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-bold {{ (int)($client->synced_pme ?? 0) === 1 ? 'border-emerald-400/20 bg-emerald-500/15 text-emerald-200' : 'border-amber-400/20 bg-amber-500/15 text-amber-200' }}">
                            {{ (int)($client->synced_pme ?? 0) === 1 ? 'Synced PME' : 'Not Synced' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ url('/admin/clients') }}"
                   class="rounded-2xl px-4 py-3 text-sm font-bold border border-white/10 hover:bg-white/10">
                    Retour
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="rounded-3xl border border-sky-400/20 bg-gradient-to-br from-sky-500/20 to-sky-500/5 p-5">
            <div class="text-xs font-bold uppercase tracking-[0.2em] text-sky-200/80">Achat Client</div>
            <div class="keep-ltr mt-2 text-3xl font-extrabold text-sky-100">{{ number_format($achatClient, 2, '.', ' ') }}</div>
            <div class="mt-3 text-xs text-sky-100/70">Montant total des achats.</div>
        </div>
        <div class="rounded-3xl border border-emerald-400/20 bg-gradient-to-br from-emerald-500/20 to-emerald-500/5 p-5">
            <div class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-200/80">Versement Client</div>
            <div class="keep-ltr mt-2 text-3xl font-extrabold text-emerald-100">{{ number_format($versementClient, 2, '.', ' ') }}</div>
            <div class="mt-3 text-xs text-emerald-100/70">Montant total des versements.</div>
        </div>
        <div class="rounded-3xl border bg-gradient-to-br p-5 {{ $soldeBadge }}">
            <div class="text-xs font-bold uppercase tracking-[0.2em] text-white/70">Solde Client</div>
            <div class="keep-ltr mt-2 text-3xl font-extrabold">{{ number_format($soldeClient, 2, '.', ' ') }}</div>
            <div class="mt-3 text-xs text-white/70">
                {{ $soldeClient > 0 ? 'Solde a recouvrer.' : ($soldeClient < 0 ? 'Client crediteur.' : 'Compte equilibre.') }}
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 rounded-3xl border border-white/10 bg-[var(--admin-card)] p-5 md:p-6">
            <div class="font-extrabold tracking-wide">Informations Client</div>
            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-white/50">Client ID</div>
                    <div class="mt-2 font-extrabold">#{{ $client->id }}</div>
                </div>
                <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-white/50">Code client</div>
                    <div class="keep-ltr mt-2 font-extrabold break-words">{{ $client->code_client ?: '-' }}</div>
                </div>
                <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-white/50">Telephone</div>
                    <div class="keep-ltr mt-2 font-extrabold break-words">{{ $client->telephone ?: '-' }}</div>
                </div>
                <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-white/50">Tarif</div>
                    <div class="mt-2 font-extrabold">PV {{ (int) ($client->tarif ?? 1) }}</div>
                </div>
                <div class="rounded-2xl border border-white/10 bg-black/20 p-4 md:col-span-2">
                    <div class="text-xs font-bold uppercase tracking-wide text-white/50">Adresse</div>
                    <div class="mt-2 font-semibold text-white/85 break-words">{{ trim((string) $client->adresse) !== '' ? $client->adresse : '-' }}</div>
                </div>
                <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-white/50">Wilaya</div>
                    <div class="mt-2 font-extrabold break-words">{{ $client->wilaya?->WILAYA ?: '-' }}</div>
                </div>
                <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-white/50">Commune</div>
                    <div class="mt-2 font-extrabold break-words">{{ $client->commune?->COMMUNE ?: '-' }}</div>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-white/10 bg-[var(--admin-card)] p-5 md:p-6">
            <div class="font-extrabold tracking-wide">Boutique & Comptes lies</div>
            <div class="mt-5 space-y-3 text-sm">
                <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                    <div class="text-xs font-bold uppercase tracking-wide text-white/50">Boutique principale</div>
                    <div class="mt-2 font-extrabold break-words">{{ $client->fournisseur?->nom_frs ?: 'Aucune boutique' }}</div>
                </div>
                <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                    <div class="text-xs font-bold uppercase tracking-wide text-white/50">Comptes lies meme email</div>
                    @if(($associated_accounts ?? collect())->isNotEmpty())
                        <div class="mt-3 space-y-2">
                            @foreach($associated_accounts as $account)
                                <a href="{{ url('/admin/clients/'.$account->id) }}"
                                   class="block rounded-xl border border-white/10 bg-white/5 px-3 py-2 hover:bg-white/10">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="font-extrabold break-words">#{{ $account->id }} · {{ $account->type_client }}</div>
                                            <div class="text-white/60 break-words">{{ $account->fournisseur?->nom_frs ?: 'Sans boutique' }}</div>
                                        </div>
                                        <i class="fa-solid fa-arrow-up-right-from-square text-xs text-sky-300 mt-1"></i>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="mt-2 text-white/70">Aucun autre compte lié.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-3xl border border-white/10 bg-[var(--admin-card)] overflow-hidden">
        <div class="flex flex-col gap-2 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="font-extrabold tracking-wide">Historique commandes</div>
                <div class="text-sm text-white/50">Commandes enregistrées pour ce client.</div>
            </div>
            <div class="text-sm text-white/60">{{ $commandes->total() }} commande(s)</div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-white/60">
                    <tr>
                        <th class="text-left py-3 px-4 font-semibold">#</th>
                        <th class="text-left py-3 px-4 font-semibold">Date</th>
                        <th class="text-left py-3 px-4 font-semibold">Boutique</th>
                        <th class="text-left py-3 px-4 font-semibold">Statut</th>
                        <th class="text-right py-3 px-4 font-semibold">Montant</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse($commandes as $commande)
                        @php
                            $badge = match($commande->statut) {
                                'en_attente' => 'bg-amber-500/15 text-amber-300 border border-amber-400/20',
                                'confirmee' => 'bg-sky-500/15 text-sky-300 border border-sky-400/20',
                                'expediee' => 'bg-indigo-500/15 text-indigo-300 border border-indigo-400/20',
                                'livree' => 'bg-emerald-500/15 text-emerald-300 border border-emerald-400/20',
                                'annulee' => 'bg-red-500/15 text-red-300 border border-red-400/20',
                                default => 'bg-white/10 text-white/70 border border-white/10'
                            };
                        @endphp
                        <tr class="hover:bg-white/5">
                            <td class="keep-ltr py-3 px-4 font-semibold whitespace-nowrap">#{{ $commande->id }}</td>
                            <td class="keep-ltr py-3 px-4 text-white/80 whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($commande->date_cmd)->format('d/m/Y H:i') }}</td>
                            <td class="py-3 px-4 text-white/80 break-words">{{ $commande->frs_nom ?: '-' }}</td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold {{ $badge }}">
                                    {{ $commande->statut }}
                                </span>
                            </td>
                            <td class="keep-ltr py-3 px-4 text-right font-extrabold whitespace-nowrap">{{ number_format((float)$commande->montant_total, 2, '.', ' ') }}</td>
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
