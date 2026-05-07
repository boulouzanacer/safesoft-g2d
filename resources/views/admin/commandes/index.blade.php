@extends('layouts.admin')

@section('content')
<div class="space-y-4">
    <form id="commandesFiltersForm" method="GET" action="{{ url('/admin/commandes') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <div>
            <select id="commandesStatutSelect"
                    name="statut"
                    class="w-full rounded-2xl border border-white/10 bg-[var(--admin-card)] px-4 py-3 outline-none focus:border-[var(--admin-primary)]">
                <option value="">Tous statuts</option>
                @foreach($statuts as $s)
                    <option value="{{ $s }}" @selected((string)$selected_statut === (string)$s)>{{ $s }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <select id="commandesFournisseurSelect"
                    name="fournisseur"
                    class="w-full rounded-2xl border border-white/10 bg-[var(--admin-card)] px-4 py-3 outline-none focus:border-[var(--admin-primary)]">
                <option value="">Tous fournisseurs</option>
                @foreach($fournisseurs as $f)
                    <option value="{{ $f->id }}" @selected((string)$selected_fournisseur === (string)$f->id)>{{ $f->nom_frs }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <input type="date"
                   id="commandesFromInput"
                   name="from"
                   value="{{ $from }}"
                   class="w-full rounded-2xl border border-white/10 bg-[var(--admin-card)] px-4 py-3 outline-none focus:border-[var(--admin-primary)]">
        </div>

        <div>
            <input type="date"
                   id="commandesToInput"
                   name="to"
                   value="{{ $to }}"
                   class="w-full rounded-2xl border border-white/10 bg-[var(--admin-card)] px-4 py-3 outline-none focus:border-[var(--admin-primary)]">
        </div>

        <button type="submit" class="hidden">Filtrer</button>
    </form>

    <div class="rounded-2xl border border-white/10 bg-[var(--admin-card)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-white/60">
                    <tr>
                        <th class="text-left py-3 px-4 font-semibold">#</th>
                        <th class="text-left py-3 px-4 font-semibold">Date</th>
                        <th class="text-left py-3 px-4 font-semibold">Client</th>
                        <th class="text-left py-3 px-4 font-semibold">Fournisseur</th>
                        <th class="text-left py-3 px-4 font-semibold">Statut</th>
                        <th class="text-right py-3 px-4 font-semibold">Montant</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse($commandes as $c)
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
                            <td class="py-3 px-4 font-semibold">#{{ $c->id }}</td>
                            <td class="py-3 px-4 text-white/80">{{ \Illuminate\Support\Carbon::parse($c->date_cmd)->format('d/m/Y H:i') }}</td>
                            <td class="py-3 px-4 text-white/80">{{ trim(($c->client_prenom ?? '').' '.($c->client_nom ?? '')) }}</td>
                            <td class="py-3 px-4 text-white/80">{{ $c->frs_nom }}</td>
                            <td class="py-3 px-4">
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $badge }}">{{ $statut }}</span>
                            </td>
                            <td class="py-3 px-4 text-right font-bold">{{ number_format((float)$c->montant_total, 2, '.', ' ') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-white/60">Aucune commande</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $commandes->links() }}
    </div>
</div>
<script>
(() => {
    const form = document.getElementById('commandesFiltersForm');
    const statut = document.getElementById('commandesStatutSelect');
    const fournisseur = document.getElementById('commandesFournisseurSelect');
    const from = document.getElementById('commandesFromInput');
    const to = document.getElementById('commandesToInput');
    if (!form || !statut || !fournisseur || !from || !to) return;

    let t = null;
    const submit = () => {
        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
            return;
        }
        form.submit();
    };

    const scheduleSubmit = () => {
        if (t) clearTimeout(t);
        t = setTimeout(() => submit(), 150);
    };

    statut.addEventListener('change', () => submit());
    fournisseur.addEventListener('change', () => submit());
    from.addEventListener('change', () => scheduleSubmit());
    to.addEventListener('change', () => scheduleSubmit());
})();
</script>
@endsection
