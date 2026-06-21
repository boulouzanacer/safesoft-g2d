@extends('store.layout')

@section('content')
@php
    $achatClient = (float) (($financial_totals['achat_client'] ?? null) ?? ($client->achat_client ?? 0));
    $versementClient = (float) (($financial_totals['versement_client'] ?? null) ?? ($client->versement_client ?? 0));
    $soldeClient = (float) (($financial_totals['solde_client'] ?? null) ?? ($client->solde_client ?? 0));
    $soldeCard = $soldeClient > 0
        ? 'border-red-200 bg-gradient-to-br from-red-50 to-white text-red-700'
        : ($soldeClient < 0
            ? 'border-emerald-200 bg-gradient-to-br from-emerald-50 to-white text-emerald-700'
            : 'border-slate-200 bg-gradient-to-br from-slate-50 to-white text-slate-700');
@endphp
<div class="max-w-4xl mx-auto space-y-6">
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="h-16 w-16 rounded-2xl bg-slate-100 text-[var(--store-primary)] flex items-center justify-center text-3xl">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900">Mon profil</h1>
                    <p class="text-sm text-slate-500">Consultez les informations de votre compte client.</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold {{ (string)($client->type_client ?? '') === 'abonne' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                    {{ (string)($client->type_client ?? '') === 'abonne' ? 'Compte abonne' : 'Compte simple' }}
                </span>
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold {{ (int)($client->actif ?? 0) === 1 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
                    {{ (int)($client->actif ?? 0) === 1 ? 'Actif' : 'Inactif' }}
                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="rounded-3xl border border-sky-200 bg-gradient-to-br from-sky-50 to-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs font-bold uppercase tracking-[0.2em] text-sky-500">Achat Client</div>
                    <div class="mt-2 text-3xl font-extrabold text-sky-700">{{ number_format($achatClient, 2, '.', ' ') }}</div>
                </div>
                <div class="h-12 w-12 rounded-2xl bg-sky-100 text-sky-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-bag-shopping"></i>
                </div>
            </div>
            <div class="mt-3 text-sm text-sky-700/80">Montant cumule de vos achats.</div>
        </div>

        <div class="rounded-3xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-500">Versement Client</div>
                    <div class="mt-2 text-3xl font-extrabold text-emerald-700">{{ number_format($versementClient, 2, '.', ' ') }}</div>
                </div>
                <div class="h-12 w-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-credit-card"></i>
                </div>
            </div>
            <div class="mt-3 text-sm text-emerald-700/80">Total deja verse sur votre compte.</div>
        </div>

        <div class="rounded-3xl border p-6 shadow-sm {{ $soldeCard }}">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs font-bold uppercase tracking-[0.2em]">Solde Client</div>
                    <div class="mt-2 text-3xl font-extrabold">{{ number_format($soldeClient, 2, '.', ' ') }}</div>
                </div>
                <div class="h-12 w-12 rounded-2xl bg-white/70 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-scale-balanced"></i>
                </div>
            </div>
            <div class="mt-3 text-sm opacity-80">
                {{ $soldeClient > 0 ? 'Solde restant a regler.' : ($soldeClient < 0 ? 'Vous avez un credit disponible.' : 'Votre compte est equilibre.') }}
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-extrabold text-slate-900">Informations personnelles</h2>
            <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Prenom</div>
                    <div class="mt-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900">{{ $client->prenom ?: '-' }}</div>
                </div>
                <div>
                    <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Nom</div>
                    <div class="mt-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900">{{ $client->nom ?: '-' }}</div>
                </div>
                <div class="sm:col-span-2">
                    <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Email</div>
                    <div class="mt-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 break-all">{{ $client->email ?: '-' }}</div>
                </div>
                <div>
                    <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Telephone</div>
                    <div class="mt-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900">{{ $client->telephone ?: '-' }}</div>
                </div>
                <div>
                    <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Email verifie</div>
                    <div class="mt-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold {{ $client->email_verified_at ? 'text-emerald-700' : 'text-amber-700' }}">
                        {{ $client->email_verified_at ? 'Oui' : 'Non' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-extrabold text-slate-900">Informations commerciales</h2>
            <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Type de compte</div>
                    <div class="mt-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900">{{ ucfirst((string) ($client->type_client ?? '')) ?: '-' }}</div>
                </div>
                <div>
                    <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Tarif</div>
                    <div class="mt-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900">PV_{{ max(1, min(3, (int) ($client->tarif ?? 1))) }}</div>
                </div>
                <div class="sm:col-span-2">
                    <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Fournisseur associe</div>
                    <div class="mt-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900">
                        @if(($associated_fournisseurs ?? collect())->isNotEmpty())
                            {{ $associated_fournisseurs->pluck('nom_frs')->implode(' , ') }}
                        @else
                            {{ $client->fournisseur->nom_frs ?? 'Aucun fournisseur' }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-extrabold text-slate-900">Adresse</h2>
        <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="sm:col-span-3">
                <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Adresse</div>
                <div class="mt-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900">{{ $client->adresse ?: '-' }}</div>
            </div>
            <div>
                <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Wilaya</div>
                <div class="mt-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900">{{ $client->wilaya->WILAYA ?? '-' }}</div>
            </div>
            <div>
                <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Commune</div>
                <div class="mt-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900">{{ $client->commune->COMMUNE ?? '-' }}</div>
            </div>
            <div>
                <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Code client</div>
                <div class="mt-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900">{{ $client->code_client ?: '-' }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
