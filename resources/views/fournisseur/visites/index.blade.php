@extends('layouts.fournisseur')

@section('content')
@php
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

    $editing = $editing_plan;
    $selectedWeekdays = collect(old('weekdays', $editing ? $editing->days->pluck('day_of_week')->all() : []))
        ->map(fn ($value) => (int) $value)
        ->all();
@endphp

<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="rounded-2xl p-5 border border-white/10 bg-[var(--frs-card)]">
            <div class="text-sm text-white/60">Plans actifs</div>
            <div class="mt-2 text-3xl font-extrabold">{{ $active_plans_count }}</div>
        </div>
        <div class="rounded-2xl p-5 border border-white/10 bg-[var(--frs-card)]">
            <div class="text-sm text-white/60">Visites du jour</div>
            <div class="mt-2 text-3xl font-extrabold">{{ $today_visits_count }}</div>
        </div>
        <div class="rounded-2xl p-5 border border-white/10 bg-[var(--frs-card)]">
            <div class="text-sm text-white/60">Clients sans planning</div>
            <div class="mt-2 text-3xl font-extrabold">{{ $clients_without_plan }}</div>
        </div>
        <div class="rounded-2xl p-5 border border-white/10 bg-[var(--frs-card)]">
            <div class="text-sm text-white/60">Clients sans prevendeur</div>
            <div class="mt-2 text-3xl font-extrabold">{{ $clients_without_prevendeur }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 rounded-3xl border border-white/10 bg-[var(--frs-card)] p-5 md:p-6">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <div class="font-extrabold tracking-wide">{{ $editing ? 'Modifier un planning' : 'Nouveau planning de visite' }}</div>
                    <div class="text-sm text-white/60">
                        Hebdomadaire: un ou plusieurs jours. Mensuel: un seul jour + occurrence. Quotidien: jours ignores.
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @if($editing)
                        <a href="{{ url('/fournisseur/visites/planning') }}"
                           class="rounded-2xl px-4 py-3 text-sm font-bold border border-white/10 hover:bg-white/10">
                            Annuler l'edition
                        </a>
                    @endif

                    <form method="POST" action="{{ url('/fournisseur/visites/planning/regenerate') }}">
                        @csrf
                        <button type="submit"
                                class="rounded-2xl px-4 py-3 text-sm font-bold text-white"
                                style="background: linear-gradient(135deg, var(--frs-primary), #0A3D7A);">
                            Regenerer 60 jours
                        </button>
                    </form>
                </div>
            </div>

            <form method="POST" action="{{ $editing ? url('/fournisseur/visites/planning/'.$editing->id) : url('/fournisseur/visites/planning') }}" class="mt-6 space-y-5">
                @csrf
                @if($editing)
                    @method('PUT')
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-2">Client</label>
                        <select name="client_id" class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                            <option value="">Selectionner</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" @selected((int) old('client_id', $editing?->client_id) === (int) $client->id)>
                                    {{ trim(($client->prenom ?? '').' '.($client->nom ?? '')) }}{{ $client->code_client ? ' - '.$client->code_client : '' }}{{ $client->prevendeur?->nom ? ' - '.$client->prevendeur->nom : ' - sans prevendeur' }}
                                </option>
                            @endforeach
                        </select>
                        @error('client_id') <div class="mt-1 text-sm text-red-300">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Libelle</label>
                        <input type="text" name="label" value="{{ old('label', $editing?->label) }}"
                               class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3"
                               placeholder="Ex: Tournee centre ville">
                        @error('label') <div class="mt-1 text-sm text-red-300">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Type</label>
                        <select name="frequency_type" class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                            <option value="daily" @selected(old('frequency_type', $editing?->frequency_type) === 'daily')>Quotidien</option>
                            <option value="weekly" @selected(old('frequency_type', $editing?->frequency_type) === 'weekly')>Hebdomadaire</option>
                            <option value="monthly" @selected(old('frequency_type', $editing?->frequency_type) === 'monthly')>Mensuel</option>
                        </select>
                        @error('frequency_type') <div class="mt-1 text-sm text-red-300">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Intervalle</label>
                        <input type="number" min="1" max="90" name="interval_value"
                               value="{{ old('interval_value', $editing?->interval_value ?? 1) }}"
                               class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                        @error('interval_value') <div class="mt-1 text-sm text-red-300">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Date debut</label>
                        <input type="date" name="start_date"
                               value="{{ old('start_date', $editing?->start_date?->format('Y-m-d')) }}"
                               class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                        @error('start_date') <div class="mt-1 text-sm text-red-300">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Date fin</label>
                        <input type="date" name="end_date"
                               value="{{ old('end_date', $editing?->end_date?->format('Y-m-d')) }}"
                               class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                        @error('end_date') <div class="mt-1 text-sm text-red-300">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Occurrence mensuelle</label>
                        <select name="month_occurrence" class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                            <option value="">Aucune</option>
                            @foreach($occurrenceLabels as $value => $label)
                                <option value="{{ $value }}" @selected(old('month_occurrence', $editing?->month_occurrence) === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('month_occurrence') <div class="mt-1 text-sm text-red-300">{{ $message }}</div> @enderror
                    </div>

                    <div class="flex items-center gap-3 pt-8">
                        <input id="is_active" type="checkbox" name="is_active" value="1"
                               @checked((int) old('is_active', $editing?->is_active ?? 1) === 1)
                               class="h-4 w-4 rounded border-white/10 bg-black/20">
                        <label for="is_active" class="text-sm font-semibold">Planning actif</label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-3">Jours de visite</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-7 gap-3">
                        @foreach($dayLabels as $key => $label)
                            <label class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3 text-sm flex items-center gap-3">
                                <input type="checkbox" name="weekdays[]" value="{{ $key }}" @checked(in_array($key, $selectedWeekdays, true)) class="h-4 w-4">
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('weekdays') <div class="mt-2 text-sm text-red-300">{{ $message }}</div> @enderror
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button type="submit"
                            class="rounded-2xl px-5 py-3 font-bold text-white"
                            style="background: linear-gradient(135deg, var(--frs-primary), #0A3D7A);">
                        {{ $editing ? 'Mettre a jour et recalculer' : 'Creer et generer' }}
                    </button>
                </div>
            </form>
        </div>

        <div class="rounded-3xl border border-white/10 bg-[var(--frs-card)] p-5 md:p-6">
            <div class="font-extrabold tracking-wide">Visites aujourd'hui</div>
            <div class="mt-4 space-y-3">
                @forelse($today_visits as $visit)
                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                        <div class="font-bold">{{ trim(($visit->client->prenom ?? '').' '.($visit->client->nom ?? '')) }}</div>
                        <div class="text-xs text-white/60 mt-1">{{ $visit->client->code_client ?: 'Sans code' }}</div>
                        <div class="text-xs text-white/50 mt-1">Prevendeur: {{ $visit->prevendeur?->nom ?: '-' }}</div>
                    </div>
                @empty
                    <div class="text-white/60">Aucune visite prevue aujourd'hui.</div>
                @endforelse
            </div>

            <div class="mt-6 font-extrabold tracking-wide">Projection 15 jours</div>
            <div class="mt-4 space-y-2">
                @forelse($upcoming_visits as $item)
                    <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-black/20 px-4 py-3 text-sm">
                        <span>{{ \Illuminate\Support\Carbon::parse($item->visit_date)->format('d/m/Y') }}</span>
                        <span class="font-extrabold">{{ $item->total }} visite(s)</span>
                    </div>
                @empty
                    <div class="text-white/60">Aucune projection disponible.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="rounded-3xl border border-white/10 bg-[var(--frs-card)] overflow-hidden">
        <div class="px-5 py-4 flex items-center justify-between">
            <div>
                <div class="font-extrabold tracking-wide">Plans enregistres</div>
                <div class="text-sm text-white/60">Chaque creation ou modification declenche un recalcul partiel sur 60 jours.</div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-white/60">
                    <tr>
                        <th class="text-left py-3 px-4 font-semibold">Client</th>
                        <th class="text-left py-3 px-4 font-semibold">Prevendeur</th>
                        <th class="text-left py-3 px-4 font-semibold">Regle</th>
                        <th class="text-left py-3 px-4 font-semibold">Jours</th>
                        <th class="text-left py-3 px-4 font-semibold">Periode</th>
                        <th class="text-left py-3 px-4 font-semibold">Etat</th>
                        <th class="text-right py-3 px-4 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse($plans as $plan)
                        @php
                            $rule = match($plan->frequency_type) {
                                'daily' => 'Tous les '.$plan->interval_value.' jour(s)',
                                'weekly' => 'Toutes les '.$plan->interval_value.' semaine(s)',
                                'monthly' => 'Tous les '.$plan->interval_value.' mois',
                                default => '-',
                            };

                            $days = $plan->days
                                ->sortBy('day_of_week')
                                ->map(fn ($day) => $dayLabels[(int) $day->day_of_week] ?? $day->day_of_week)
                                ->implode(', ');
                        @endphp
                        <tr class="hover:bg-white/5">
                            <td class="py-3 px-4">
                                <div class="font-bold">{{ trim(($plan->client->prenom ?? '').' '.($plan->client->nom ?? '')) }}</div>
                                <div class="text-xs text-white/60">{{ $plan->client->code_client ?: '-' }}</div>
                            </td>
                            <td class="py-3 px-4 text-white/80">{{ $plan->prevendeur?->nom ?: ($plan->client->prevendeur?->nom ?: '-') }}</td>
                            <td class="py-3 px-4">
                                <div class="font-semibold">{{ $rule }}</div>
                                @if($plan->frequency_type === 'monthly' && $plan->month_occurrence)
                                    <div class="text-xs text-white/60">{{ $occurrenceLabels[$plan->month_occurrence] ?? $plan->month_occurrence }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-white/80">{{ $days !== '' ? $days : 'N/A' }}</td>
                            <td class="py-3 px-4 text-white/80">
                                {{ $plan->start_date?->format('d/m/Y') }}
                                @if($plan->end_date)
                                    <span class="text-white/50">-></span> {{ $plan->end_date->format('d/m/Y') }}
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold {{ $plan->is_active ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-400/20' : 'bg-red-500/15 text-red-300 border border-red-400/20' }}">
                                    {{ $plan->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ url('/fournisseur/visites/planning?edit='.$plan->id) }}"
                                       class="rounded-xl px-3 py-2 text-xs font-bold border border-white/10 hover:bg-white/10">
                                        Editer
                                    </a>
                                    <form method="POST" action="{{ url('/fournisseur/visites/planning/'.$plan->id.'/toggle') }}">
                                        @csrf
                                        <button type="submit"
                                                class="rounded-xl px-3 py-2 text-xs font-bold border border-white/10 hover:bg-white/10">
                                            {{ $plan->is_active ? 'Desactiver' : 'Activer' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-white/60">Aucun planning enregistre.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4">
            {{ $plans->links() }}
        </div>
    </div>
</div>
@endsection
