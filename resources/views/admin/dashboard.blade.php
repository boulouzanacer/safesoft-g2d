@extends('layouts.admin')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
    <div class="rounded-2xl p-5 border border-white/10 bg-[var(--admin-card)]">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-sm text-white/60">Total Fournisseurs</div>
                <div class="text-3xl font-extrabold mt-1">{{ $nb_fournisseurs }}</div>
            </div>
            <div class="h-12 w-12 rounded-2xl flex items-center justify-center"
                 style="background: linear-gradient(135deg, var(--admin-primary), #0A3D7A);">
                <i class="fa-solid fa-store text-white text-lg"></i>
            </div>
        </div>
    </div>

    <div class="rounded-2xl p-5 border border-white/10 bg-[var(--admin-card)]">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-sm text-white/60">Total Clients</div>
                <div class="text-3xl font-extrabold mt-1">{{ $nb_clients }}</div>
            </div>
            <div class="h-12 w-12 rounded-2xl flex items-center justify-center"
                 style="background: linear-gradient(135deg, #22c55e, #16a34a);">
                <i class="fa-solid fa-users text-white text-lg"></i>
            </div>
        </div>
    </div>

    <div class="rounded-2xl p-5 border border-white/10 bg-[var(--admin-card)]">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-sm text-white/60">Commandes du jour</div>
                <div class="text-3xl font-extrabold mt-1">{{ $nb_commandes }}</div>
            </div>
            <div class="h-12 w-12 rounded-2xl flex items-center justify-center"
                 style="background: linear-gradient(135deg, #fb923c, #f97316);">
                <i class="fa-solid fa-cart-shopping text-white text-lg"></i>
            </div>
        </div>
    </div>

    <div class="rounded-2xl p-5 border border-white/10 bg-[var(--admin-card)]">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-sm text-white/60">CA Total</div>
                <div class="text-3xl font-extrabold mt-1">{{ number_format($ca_total, 2, '.', ' ') }}</div>
            </div>
            <div class="h-12 w-12 rounded-2xl flex items-center justify-center"
                 style="background: linear-gradient(135deg, #a855f7, #7c3aed);">
                <i class="fa-solid fa-sack-dollar text-white text-lg"></i>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mt-4">
    <div class="xl:col-span-2 rounded-2xl p-5 border border-white/10 bg-[var(--admin-card)]">
        <div class="flex items-center justify-between mb-4">
            <div class="font-extrabold tracking-wide">Commandes (7 derniers jours)</div>
            <div class="text-sm text-white/60">Total</div>
        </div>
        <canvas id="ordersChart" height="110"></canvas>
    </div>

    <div class="rounded-2xl p-5 border border-white/10 bg-[var(--admin-card)]">
        <div class="font-extrabold tracking-wide mb-4">Fournisseurs récents</div>
        <div class="space-y-3">
            @foreach($fournisseurs_recents as $f)
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <div class="font-semibold truncate">{{ $f->nom_frs }}</div>
                        <div class="text-xs text-white/60 truncate">{{ $f->email }}</div>
                    </div>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ (int)$f->actif === 1 ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-400/20' : 'bg-red-500/15 text-red-300 border border-red-400/20' }}">
                        {{ (int)$f->actif === 1 ? 'Actif' : 'Inactif' }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="rounded-2xl p-5 border border-white/10 bg-[var(--admin-card)] mt-4 overflow-hidden">
    <div class="flex items-center justify-between mb-4">
        <div class="font-extrabold tracking-wide">5 dernières commandes</div>
        <a href="{{ url('/admin/commandes') }}" class="text-sm text-[var(--admin-primary)] hover:opacity-90">Voir tout</a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="text-white/60">
                <tr>
                    <th class="text-left py-3 pr-4 font-semibold">#</th>
                    <th class="text-left py-3 pr-4 font-semibold">Date</th>
                    <th class="text-left py-3 pr-4 font-semibold">Client</th>
                    <th class="text-left py-3 pr-4 font-semibold">Fournisseur</th>
                    <th class="text-left py-3 pr-4 font-semibold">Statut</th>
                    <th class="text-right py-3 font-semibold">Montant</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @forelse($dernieres_commandes as $c)
                    @php
                        $statut = $c->statut;
                        $badge = match($statut) {
                            'en_attente' => 'bg-amber-500/15 text-amber-300 border border-amber-400/20',
                            'confirmee' => 'bg-sky-500/15 text-sky-300 border border-sky-400/20',
                            'expediee' => 'bg-indigo-500/15 text-indigo-300 border border-indigo-400/20',
                            'livree' => 'bg-emerald-500/15 text-emerald-300 border border-emerald-400/20',
                            'annulee' => 'bg-red-500/15 text-red-300 border border-red-400/20',
                            default => 'bg-white/10 text-white/70 border border-white/10'
                        };
                    @endphp
                    <tr class="hover:bg-white/5">
                        <td class="py-3 pr-4 font-semibold">#{{ $c->id }}</td>
                        <td class="py-3 pr-4 text-white/80">{{ \Illuminate\Support\Carbon::parse($c->date_cmd)->format('d/m/Y H:i') }}</td>
                        <td class="py-3 pr-4 text-white/80">{{ trim(($c->client_prenom ?? '').' '.($c->client_nom ?? '')) }}</td>
                        <td class="py-3 pr-4 text-white/80">{{ $c->frs_nom }}</td>
                        <td class="py-3 pr-4">
                            <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $badge }}">{{ $statut }}</span>
                        </td>
                        <td class="py-3 text-right font-bold">{{ number_format((float)$c->montant_total, 2, '.', ' ') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-white/60">Aucune commande</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    const ctx = document.getElementById('ordersChart');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($chart_labels),
            datasets: [{
                label: 'Commandes',
                data: @json($chart_series),
                borderColor: '#1E6FD9',
                backgroundColor: 'rgba(30,111,217,0.15)',
                fill: true,
                tension: 0.35,
                pointRadius: 4,
                pointBackgroundColor: '#1E6FD9'
            }]
        },
        options: {
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { ticks: { color: 'rgba(255,255,255,0.65)' }, grid: { color: 'rgba(255,255,255,0.08)' } },
                y: { ticks: { color: 'rgba(255,255,255,0.65)' }, grid: { color: 'rgba(255,255,255,0.08)' }, beginAtZero: true }
            }
        }
    });
</script>
@endsection

