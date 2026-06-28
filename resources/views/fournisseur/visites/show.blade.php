@extends('layouts.fournisseur')

@section('content')
@php
    $statusLabels = [
        'pending' => 'En attente',
        'open' => 'Ouverte',
        'closed' => 'Fermee',
    ];
@endphp

<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <div class="text-2xl font-extrabold tracking-wide">Detail tournee</div>
            <div class="text-sm text-white/60">
                {{ $tour->tour_date?->format('d/m/Y') }} | {{ $tour->prevendeur?->nom ?: '-' }} | {{ (int) $tour->clients_count }} client(s)
            </div>
        </div>
        <a href="{{ url('/fournisseur/visites/planning') }}"
           class="rounded-2xl px-4 py-3 text-sm font-bold border border-white/10 hover:bg-white/10">
            Retour au planning
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="rounded-2xl p-5 border border-white/10 bg-[var(--frs-card)]">
            <div class="text-sm text-white/60">Date</div>
            <div class="mt-2 text-3xl font-extrabold">{{ $tour->tour_date?->format('d/m/Y') }}</div>
        </div>
        <div class="rounded-2xl p-5 border border-white/10 bg-[var(--frs-card)]">
            <div class="text-sm text-white/60">Prevendeur</div>
            <div class="mt-2 text-3xl font-extrabold">{{ $tour->prevendeur?->nom ?: '-' }}</div>
        </div>
        <div class="rounded-2xl p-5 border border-white/10 bg-[var(--frs-card)]">
            <div class="text-sm text-white/60">Etat</div>
            <div class="mt-2 text-3xl font-extrabold">{{ $statusLabels[$tour->status] ?? $tour->status }}</div>
        </div>
    </div>

    <div class="rounded-3xl border border-white/10 bg-[var(--frs-card)] p-5 md:p-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="font-extrabold tracking-wide">Etat de la tournee</div>
                <div class="text-sm text-white/60">Tu peux changer manuellement l'etat si besoin.</div>
            </div>
            <form method="POST" action="{{ url('/fournisseur/visites/planning/tournees/'.$tour->id.'/status') }}" class="flex flex-wrap items-center gap-3">
                @csrf
                <select name="status" class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                    @foreach($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected($tour->status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit"
                        class="rounded-2xl px-5 py-3 font-bold text-white"
                        style="background: linear-gradient(135deg, var(--frs-primary), #0A3D7A);">
                    Enregistrer l'etat
                </button>
            </form>
        </div>
    </div>

    <div class="rounded-3xl border border-white/10 bg-[var(--frs-card)] overflow-hidden">
        <div class="px-5 py-4">
            <div class="font-extrabold tracking-wide">Clients a visiter</div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-white/60">
                    <tr>
                        <th class="text-left py-3 px-4 font-semibold">Client</th>
                        <th class="text-left py-3 px-4 font-semibold">Code</th>
                        <th class="text-left py-3 px-4 font-semibold">Telephone</th>
                        <th class="text-left py-3 px-4 font-semibold">Adresse</th>
                        <th class="text-left py-3 px-4 font-semibold">Etat visite</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse($visits as $visit)
                        <tr class="hover:bg-white/5">
                            <td class="py-3 px-4 font-bold">{{ trim(($visit->client->prenom ?? '').' '.($visit->client->nom ?? '')) }}</td>
                            <td class="py-3 px-4 text-white/80">{{ $visit->client->code_client ?: '-' }}</td>
                            <td class="py-3 px-4 text-white/80">{{ $visit->client->telephone ?: '-' }}</td>
                            <td class="py-3 px-4 text-white/80">{{ $visit->client->adresse ?: '-' }}</td>
                            <td class="py-3 px-4 text-white/80">{{ $visit->status }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-white/60">Aucun client dans cette tournee.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
