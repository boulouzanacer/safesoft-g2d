@extends('store.layout')

@section('content')
@php
    $profileTabs = collect($profile_tabs ?? []);
    $hasMultipleTabs = $profileTabs->count() > 1;
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

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900">Informations commerciales par fournisseur</h2>
                <p class="text-sm text-slate-500">Consultez vos montants et conditions commerciales pour chaque fournisseur associé.</p>
            </div>
            @if($hasMultipleTabs)
                <div class="text-xs font-semibold text-slate-500">{{ $profileTabs->count() }} fournisseurs associes</div>
            @endif
        </div>

        @if($hasMultipleTabs)
            <div class="mt-5 flex flex-wrap gap-2" id="profileTabsNav">
                @foreach($profileTabs as $index => $tab)
                    <button type="button"
                            data-profile-tab-button="{{ $tab['key'] }}"
                            class="rounded-2xl border px-4 py-2 text-sm font-bold transition {{ $index === 0 ? 'border-[var(--store-primary)] bg-blue-50 text-[var(--store-primary)]' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                        {{ $tab['fournisseur_name'] }}
                    </button>
                @endforeach
            </div>
        @endif

        <div class="mt-5 space-y-6">
            @foreach($profileTabs as $index => $tab)
                @php
                    $achatClient = (float) ($tab['achat_client'] ?? 0);
                    $versementClient = (float) ($tab['versement_client'] ?? 0);
                    $soldeClient = (float) ($tab['solde_client'] ?? 0);
                    $soldeCard = $soldeClient > 0
                        ? 'border-red-200 bg-gradient-to-br from-red-50 to-white text-red-700'
                        : ($soldeClient < 0
                            ? 'border-emerald-200 bg-gradient-to-br from-emerald-50 to-white text-emerald-700'
                            : 'border-slate-200 bg-gradient-to-br from-slate-50 to-white text-slate-700');
                @endphp
                <div data-profile-tab-panel="{{ $tab['key'] }}" class="{{ $index === 0 ? '' : 'hidden' }}">
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
                            <div class="mt-3 text-sm text-sky-700/80">Montant cumule de vos achats chez {{ $tab['fournisseur_name'] }}.</div>
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
                            <div class="mt-3 text-sm text-emerald-700/80">Total deja verse chez {{ $tab['fournisseur_name'] }}.</div>
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

                    <div class="mt-6 rounded-3xl border border-slate-200 bg-slate-50/70 p-6">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-lg font-extrabold text-slate-900">{{ $tab['fournisseur_name'] }}</h3>
                                <p class="text-sm text-slate-500">Informations commerciales appliquees pour ce fournisseur.</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold {{ (string)($tab['type_client'] ?? '') === 'abonne' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                                    {{ (string)($tab['type_client'] ?? '') === 'abonne' ? 'Compte abonne' : 'Compte simple' }}
                                </span>
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold {{ (int)($tab['synced_pme'] ?? 0) === 1 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                    {{ (int)($tab['synced_pme'] ?? 0) === 1 ? 'Synced PME' : 'Not Synced' }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Type de compte</div>
                                <div class="mt-1 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900">{{ ucfirst((string) ($tab['type_client'] ?? '')) ?: '-' }}</div>
                            </div>
                            <div>
                                <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Tarif</div>
                                <div class="mt-1 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900">PV_{{ max(1, min(3, (int) ($tab['tarif'] ?? 1))) }}</div>
                            </div>
                            <div class="sm:col-span-2">
                                <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Code client</div>
                                <div class="mt-1 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900">{{ trim((string) ($tab['code_client'] ?? '')) !== '' ? $tab['code_client'] : '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
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

            <div>
                <h2 class="text-lg font-extrabold text-slate-900">Adresse</h2>
                <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
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
                </div>
            </div>
        </div>
    </div>
</div>
@if($hasMultipleTabs)
    <script>
        (() => {
            const buttons = Array.from(document.querySelectorAll('[data-profile-tab-button]'));
            const panels = Array.from(document.querySelectorAll('[data-profile-tab-panel]'));
            if (buttons.length === 0 || panels.length === 0) return;

            const activate = (key) => {
                buttons.forEach((button) => {
                    const active = button.getAttribute('data-profile-tab-button') === key;
                    button.classList.toggle('border-[var(--store-primary)]', active);
                    button.classList.toggle('bg-blue-50', active);
                    button.classList.toggle('text-[var(--store-primary)]', active);
                    button.classList.toggle('border-slate-200', !active);
                    button.classList.toggle('bg-white', !active);
                    button.classList.toggle('text-slate-600', !active);
                });

                panels.forEach((panel) => {
                    const active = panel.getAttribute('data-profile-tab-panel') === key;
                    panel.classList.toggle('hidden', !active);
                });
            };

            buttons.forEach((button) => {
                button.addEventListener('click', () => activate(button.getAttribute('data-profile-tab-button')));
            });
        })();
    </script>
@endif
@endsection
