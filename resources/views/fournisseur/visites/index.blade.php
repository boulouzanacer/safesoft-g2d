@extends('layouts.fournisseur')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <div class="text-2xl font-extrabold tracking-wide">Planning de visite</div>
            <div class="text-sm text-white/60">Liste des tournees generees automatiquement pour les 60 prochains jours.</div>
        </div>

        <form method="POST" action="{{ url('/fournisseur/visites/planning/regenerate') }}">
            @csrf
            <button type="submit"
                    class="rounded-2xl px-5 py-3 font-bold text-white"
                    style="background: linear-gradient(135deg, var(--frs-primary), #0A3D7A);">
                Regenerer manuellement
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
        <div class="rounded-2xl p-5 border border-white/10 bg-[var(--frs-card)]">
            <div class="text-sm text-white/60">Programmes actifs</div>
            <div class="mt-2 text-3xl font-extrabold">{{ $active_plans_count }}</div>
        </div>
        <div class="rounded-2xl p-5 border border-white/10 bg-[var(--frs-card)]">
            <div class="text-sm text-white/60">Clients a visiter aujourd'hui</div>
            <div class="mt-2 text-3xl font-extrabold">{{ $today_visits_count }}</div>
        </div>
        <div class="rounded-2xl p-5 border border-white/10 bg-[var(--frs-card)]">
            <div class="text-sm text-white/60">Tournees ouvertes</div>
            <div class="mt-2 text-3xl font-extrabold">{{ $open_tours_count }}</div>
        </div>
        <div class="rounded-2xl p-5 border border-white/10 bg-[var(--frs-card)]">
            <div class="text-sm text-white/60">Clients sans programme</div>
            <div class="mt-2 text-3xl font-extrabold">{{ $clients_without_plan }}</div>
        </div>
        <div class="rounded-2xl p-5 border border-white/10 bg-[var(--frs-card)]">
            <div class="text-sm text-white/60">Clients sans prevendeur</div>
            <div class="mt-2 text-3xl font-extrabold">{{ $clients_without_prevendeur }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 rounded-3xl border border-white/10 bg-[var(--frs-card)] overflow-hidden">
            <div class="px-5 py-4 flex items-center justify-between">
                <div>
                    <div class="font-extrabold tracking-wide">Liste des tournees</div>
                    <div class="text-sm text-white/60">Chaque ligne represente une tournee d'un prevendeur pour une date donnee.</div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-white/60">
                        <tr>
                            <th class="text-left py-3 px-4 font-semibold">Date</th>
                            <th class="text-left py-3 px-4 font-semibold">Prevendeur</th>
                            <th class="text-right py-3 px-4 font-semibold">Nb clients</th>
                            <th class="text-left py-3 px-4 font-semibold">Etat</th>
                            <th class="text-right py-3 px-4 font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse($tours as $tour)
                            @php
                                $badge = match($tour->status) {
                                    'pending' => 'bg-amber-500/15 text-amber-300 border border-amber-400/20',
                                    'open' => 'bg-sky-500/15 text-sky-300 border border-sky-400/20',
                                    'closed' => 'bg-emerald-500/15 text-emerald-300 border border-emerald-400/20',
                                    default => 'bg-white/10 text-white/70 border border-white/10'
                                };
                                $label = match($tour->status) {
                                    'pending' => 'En attente',
                                    'open' => 'Ouverte',
                                    'closed' => 'Fermee',
                                    default => $tour->status,
                                };
                            @endphp
                            <tr class="hover:bg-white/5">
                                <td class="py-3 px-4 font-semibold">{{ $tour->tour_date?->format('d/m/Y') }}</td>
                                <td class="py-3 px-4 text-white/80">{{ $tour->prevendeur?->nom ?: '-' }}</td>
                                <td class="py-3 px-4 text-right font-extrabold">{{ (int) $tour->clients_count }}</td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold {{ $badge }}">{{ $label }}</span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ url('/fournisseur/visites/planning/tournees/'.$tour->id) }}"
                                       class="inline-flex items-center rounded-xl px-3 py-2 text-xs font-bold border border-white/10 hover:bg-white/10">
                                        Ouvrir
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-10 text-center text-white/60">Aucune tournee generee.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-4">
                {{ $tours->links() }}
            </div>
        </div>

        <div class="rounded-3xl border border-white/10 bg-[var(--frs-card)] p-5 md:p-6">
            <div class="font-extrabold tracking-wide">Tournees du jour</div>
            <div class="mt-4 space-y-3">
                @forelse($today_tours as $tour)
                    <a href="{{ url('/fournisseur/visites/planning/tournees/'.$tour->id) }}"
                       class="block rounded-2xl border border-white/10 bg-black/20 px-4 py-3 hover:bg-white/5">
                        <div class="font-bold">{{ $tour->prevendeur?->nom ?: '-' }}</div>
                        <div class="text-xs text-white/60 mt-1">{{ $tour->tour_date?->format('d/m/Y') }}</div>
                        <div class="text-xs text-white/50 mt-1">{{ (int) $tour->clients_count }} client(s)</div>
                    </a>
                @empty
                    <div class="text-white/60">Aucune tournee ouverte aujourd'hui.</div>
                @endforelse
            </div>

            <div class="mt-6 font-extrabold tracking-wide">Prochaines dates</div>
            <div class="mt-4 space-y-2">
                @forelse($upcoming_dates as $item)
                    <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-black/20 px-4 py-3 text-sm">
                        <span>{{ \Illuminate\Support\Carbon::parse($item->tour_date)->format('d/m/Y') }}</span>
                        <span class="font-extrabold">{{ (int) $item->clients_count }} client(s)</span>
                    </div>
                @empty
                    <div class="text-white/60">Aucune tournee programmee.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
