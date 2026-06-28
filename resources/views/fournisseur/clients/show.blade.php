@extends('layouts.fournisseur')

@section('content')
@php
    $achatClient = (float) ($client->achat_client ?? 0);
    $versementClient = (float) ($client->versement_client ?? 0);
    $soldeClient = (float) ($client->solde_client ?? 0);
    $activePlan = $active_plan;
    $dayLabels = [
        0 => 'Dimanche',
        1 => 'Lundi',
        2 => 'Mardi',
        3 => 'Mercredi',
        4 => 'Jeudi',
        5 => 'Vendredi',
        6 => 'Samedi',
    ];
    $occurrenceLabels = [
        'first' => 'Premier',
        'second' => 'Deuxieme',
        'third' => 'Troisieme',
        'fourth' => 'Quatrieme',
        'last' => 'Dernier',
    ];
    $programmeTourneeActif = (int) old('programme_tournee_actif', $activePlan?->is_active ?? 0) === 1;
    $selectedWeekdays = collect(old('weekdays', $activePlan ? $activePlan->days->pluck('day_of_week')->all() : []))
        ->map(fn ($value) => (int) $value)
        ->all();
    $planningDays = $activePlan
        ? $activePlan->days
            ->sortBy('day_of_week')
            ->map(fn ($day) => $dayLabels[(int) $day->day_of_week] ?? (string) $day->day_of_week)
            ->implode(', ')
        : '';
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
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-3xl border border-white/10 bg-[var(--frs-card)] p-5 md:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start gap-4">
                <div class="h-16 w-16 shrink-0 rounded-2xl bg-[var(--frs-primary)]/15 text-[var(--frs-primary)] flex items-center justify-center text-2xl">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-2xl font-extrabold tracking-wide break-words">{{ $client->prenom }} {{ $client->nom }}</div>
                    <div class="mt-1 text-sm text-white/60 break-all">{{ $client->email ?: '-' }}</div>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-bold {{ $typeBadge }}">
                            {{ $client->type_client ?: 'client' }}
                        </span>
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-bold {{ $statusBadge }}">
                            {{ (int) ($client->actif ?? 0) === 1 ? 'Actif' : 'Inactif' }}
                        </span>
                        <span class="inline-flex items-center rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs font-bold text-white/80">
                            Tarif {{ (int) ($client->tarif ?? 1) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ url('/fournisseur/commandes?client='.$client->id) }}"
                   class="rounded-2xl px-4 py-3 text-sm font-bold border border-white/10 hover:bg-white/10">
                    Voir commandes
                </a>
                <a href="{{ url('/fournisseur/clients') }}"
                   class="rounded-2xl px-4 py-3 text-sm font-bold border border-white/10 hover:bg-white/10">
                    Retour
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="rounded-3xl border border-sky-400/20 bg-gradient-to-br from-sky-500/20 to-sky-500/5 p-5 shadow-lg shadow-sky-950/10">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="text-xs font-bold uppercase tracking-[0.2em] text-sky-200/80">Achat Client</div>
                    <div class="mt-2 text-3xl font-extrabold text-sky-100 break-words">{{ number_format($achatClient, 2, '.', ' ') }}</div>
                </div>
                <div class="h-12 w-12 shrink-0 rounded-2xl bg-sky-500/15 text-sky-200 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-cart-shopping"></i>
                </div>
            </div>
            <div class="mt-3 text-xs text-sky-100/70">Total purchases for this client with this fournisseur.</div>
        </div>

        <div class="rounded-3xl border border-emerald-400/20 bg-gradient-to-br from-emerald-500/20 to-emerald-500/5 p-5 shadow-lg shadow-emerald-950/10">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-200/80">Versement Client</div>
                    <div class="mt-2 text-3xl font-extrabold text-emerald-100 break-words">{{ number_format($versementClient, 2, '.', ' ') }}</div>
                </div>
                <div class="h-12 w-12 shrink-0 rounded-2xl bg-emerald-500/15 text-emerald-200 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-wallet"></i>
                </div>
            </div>
            <div class="mt-3 text-xs text-emerald-100/70">Total payments received from this client.</div>
        </div>

        <div class="rounded-3xl border bg-gradient-to-br p-5 shadow-lg {{ $soldeBadge }}">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="text-xs font-bold uppercase tracking-[0.2em] text-white/70">Solde Client</div>
                    <div class="mt-2 text-3xl font-extrabold break-words">{{ number_format($soldeClient, 2, '.', ' ') }}</div>
                </div>
                <div class="h-12 w-12 shrink-0 rounded-2xl bg-white/10 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-scale-balanced"></i>
                </div>
            </div>
            <div class="mt-3 text-xs text-white/70">
                {{ $soldeClient > 0 ? 'Outstanding balance to collect.' : ($soldeClient < 0 ? 'Client has credit available.' : 'Account is balanced.') }}
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 rounded-3xl border border-white/10 bg-[var(--frs-card)] p-5 md:p-6">
            <div class="flex items-center justify-between gap-3">
                <div class="font-extrabold tracking-wide">Client Information</div>
                <div class="text-xs text-white/50">Structured overview</div>
            </div>

            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-white/50">Code Client</div>
                    <div class="mt-2 font-extrabold break-words">{{ $client->code_client ?: '-' }}</div>
                </div>
                <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-white/50">Telephone</div>
                    <div class="mt-2 font-extrabold break-words">{{ $client->telephone ?: '-' }}</div>
                </div>
                <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-white/50">Type</div>
                    <div class="mt-2 font-extrabold capitalize">{{ $client->type_client ?: '-' }}</div>
                </div>
                <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-white/50">Tarif</div>
                    <div class="mt-2 font-extrabold">PV {{ (int) ($client->tarif ?? 1) }}</div>
                </div>
                <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-white/50">Prevendeur</div>
                    <div class="mt-2 font-extrabold">{{ $client->prevendeur?->nom ?: '-' }}</div>
                </div>
                <div class="rounded-2xl border border-white/10 bg-black/20 p-4 md:col-span-2">
                    <div class="text-xs font-bold uppercase tracking-wide text-white/50">Adresse</div>
                    <div class="mt-2 font-semibold text-white/85 break-words">{{ trim((string) $client->adresse) !== '' ? $client->adresse : '-' }}</div>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-white/10 bg-[var(--frs-card)] p-5 md:p-6">
            <div class="font-extrabold tracking-wide">Affectation Prevendeur</div>
            <form method="POST" action="{{ url('/fournisseur/clients/'.$client->id.'/prevendeur') }}" class="mt-5 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-semibold mb-2">Choisir un prevendeur</label>
                    <select name="prevendeur_id" class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                        <option value="">Sans prevendeur</option>
                        @foreach($prevendeurs as $prevendeur)
                            <option value="{{ $prevendeur->id }}" @selected((int) old('prevendeur_id', $client->prevendeur_id) === (int) $prevendeur->id)>
                                {{ $prevendeur->nom }}
                            </option>
                        @endforeach
                    </select>
                    @error('prevendeur_id') <div class="mt-1 text-sm text-red-300">{{ $message }}</div> @enderror
                </div>
                <button type="submit"
                        class="w-full rounded-2xl px-4 py-3 text-sm font-bold text-white"
                        style="background: linear-gradient(135deg, var(--frs-primary), #0A3D7A);">
                    Enregistrer l'affectation
                </button>
            </form>

            <div class="mt-6 font-extrabold tracking-wide">Quick Summary</div>
            <div class="mt-4 space-y-3 text-sm">
                <div class="flex items-center justify-between gap-4 rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                    <span class="text-white/60">Client ID</span>
                    <span class="font-extrabold">#{{ $client->id }}</span>
                </div>
                <div class="flex items-center justify-between gap-4 rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                    <span class="text-white/60">Orders</span>
                    <span class="font-extrabold">{{ $commandes->total() }}</span>
                </div>
                <div class="flex items-center justify-between gap-4 rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                    <span class="text-white/60">Status</span>
                    <span class="font-extrabold {{ (int) ($client->actif ?? 0) === 1 ? 'text-emerald-300' : 'text-red-300' }}">
                        {{ (int) ($client->actif ?? 0) === 1 ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="flex items-center justify-between gap-4 rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                    <span class="text-white/60">Email</span>
                    <span class="font-extrabold truncate max-w-[12rem] text-right">{{ $client->email ?: '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-3xl border border-white/10 bg-[var(--frs-card)] p-5 md:p-6">
        <div class="flex items-center justify-between gap-3">
            <div>
                <div class="font-extrabold tracking-wide">Programme tournee du client</div>
                <div class="text-sm text-white/50">Si active, la generation automatique de minuit remplira les 60 prochains jours pour ce client.</div>
            </div>
            <a href="{{ url('/fournisseur/visites/planning') }}"
               class="rounded-2xl px-4 py-3 text-sm font-bold border border-white/10 hover:bg-white/10">
                Voir les tournees
            </a>
        </div>

        <form method="POST" action="{{ url('/fournisseur/clients/'.$client->id.'/planning') }}" class="mt-5 space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
                <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                    <label for="programme_tournee_actif" class="text-xs font-bold uppercase tracking-wide text-white/50">Programme tournee</label>
                    <div class="mt-3 flex items-center gap-3">
                        <input id="programme_tournee_actif" type="checkbox" name="programme_tournee_actif" value="1"
                               @checked($programmeTourneeActif)
                               class="h-4 w-4 rounded border-white/10 bg-black/20">
                        <span class="font-extrabold">{{ $programmeTourneeActif ? 'Active' : 'Desactive' }}</span>
                    </div>
                    @error('programme_tournee_actif') <div class="mt-2 text-sm text-red-300">{{ $message }}</div> @enderror
                </div>
                <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                    <label class="text-xs font-bold uppercase tracking-wide text-white/50">Type</label>
                    <select name="frequency_type" class="mt-3 w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                        <option value="daily" @selected(old('frequency_type', $activePlan?->frequency_type) === 'daily')>Quotidien</option>
                        <option value="weekly" @selected(old('frequency_type', $activePlan?->frequency_type) === 'weekly')>Hebdomadaire</option>
                        <option value="monthly" @selected(old('frequency_type', $activePlan?->frequency_type) === 'monthly')>Mensuel</option>
                    </select>
                    @error('frequency_type') <div class="mt-2 text-sm text-red-300">{{ $message }}</div> @enderror
                </div>
                <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                    <label class="text-xs font-bold uppercase tracking-wide text-white/50">Intervalle</label>
                    <input type="number" min="1" max="90" name="interval_value"
                           value="{{ old('interval_value', $activePlan?->interval_value ?? 1) }}"
                           class="mt-3 w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                    @error('interval_value') <div class="mt-2 text-sm text-red-300">{{ $message }}</div> @enderror
                </div>
                <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                    <label class="text-xs font-bold uppercase tracking-wide text-white/50">Occurrence mensuelle</label>
                    <select name="month_occurrence" class="mt-3 w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                        <option value="">Aucune</option>
                        @foreach($occurrenceLabels as $value => $label)
                            <option value="{{ $value }}" @selected(old('month_occurrence', $activePlan?->month_occurrence) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('month_occurrence') <div class="mt-2 text-sm text-red-300">{{ $message }}</div> @enderror
                </div>
                <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-white/50">Prevendeur</div>
                    <div class="mt-3 font-extrabold">{{ $client->prevendeur?->nom ?: '-' }}</div>
                </div>
            </div>

            <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                <div class="text-xs font-bold uppercase tracking-wide text-white/50">Jours de visite</div>
                <div class="mt-4 grid grid-cols-2 md:grid-cols-4 xl:grid-cols-7 gap-3">
                    @foreach($dayLabels as $key => $label)
                        <label class="rounded-2xl border border-white/10 bg-[var(--frs-card)] px-4 py-3 text-sm flex items-center gap-3">
                            <input type="checkbox" name="weekdays[]" value="{{ $key }}" @checked(in_array($key, $selectedWeekdays, true)) class="h-4 w-4">
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('weekdays') <div class="mt-2 text-sm text-red-300">{{ $message }}</div> @enderror
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="text-sm text-white/60">
                    Details actuels: {{ $activePlan?->frequency_type ?: '-' }} | intervalle {{ $activePlan?->interval_value ?: '-' }} | jours {{ $planningDays !== '' ? $planningDays : '-' }}
                </div>
                <button type="submit"
                        class="rounded-2xl px-5 py-3 font-bold text-white"
                        style="background: linear-gradient(135deg, var(--frs-primary), #0A3D7A);">
                    Enregistrer le programme tournee
                </button>
            </div>
        </form>
    </div>

    <div class="rounded-3xl border border-white/10 bg-[var(--frs-card)] overflow-hidden">
        <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="font-extrabold tracking-wide">Order History</div>
                <div class="text-sm text-white/50">Recent commandes linked to this client.</div>
            </div>
            <a href="{{ url('/fournisseur/commandes?client='.$client->id) }}"
               class="text-sm font-bold text-[var(--frs-primary)] hover:opacity-90">
                Open full commandes list
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-white/60">
                    <tr>
                        <th class="text-left py-3 px-4 font-semibold">#</th>
                        <th class="text-left py-3 px-4 font-semibold">Date</th>
                        <th class="text-left py-3 px-4 font-semibold">Status</th>
                        <th class="text-right py-3 px-4 font-semibold">Amount</th>
                        <th class="text-right py-3 px-4 font-semibold">Action</th>
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
                            <td class="py-3 px-4 font-semibold whitespace-nowrap">#{{ $c->id }}</td>
                            <td class="py-3 px-4 text-white/80 whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($c->date_cmd)->format('d/m/Y H:i') }}</td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold {{ $badge }}">
                                    {{ $c->statut }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right font-extrabold whitespace-nowrap">{{ number_format((float) $c->montant_total, 2, '.', ' ') }}</td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ url('/fournisseur/commandes/'.$c->id) }}"
                                   class="inline-flex items-center rounded-xl px-3 py-2 text-xs font-bold border border-white/10 hover:bg-white/10">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-white/60">No orders found for this client.</td>
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
