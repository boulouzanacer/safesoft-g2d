@extends('layouts.admin')

@section('content')
<div class="space-y-4">
    <form id="clientsFiltersForm" method="GET" action="{{ url('/admin/clients') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <div class="md:col-span-2">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-white/50"></i>
                <input id="clientsSearchInput"
                       name="q"
                       value="{{ $q }}"
                       placeholder="Rechercher nom/prénom/email..."
                       class="w-full rounded-2xl border border-white/10 bg-[var(--admin-card)] pl-11 pr-4 py-3 outline-none focus:border-[var(--admin-primary)]">
            </div>
        </div>

        <div>
            <select id="clientsFournisseurSelect"
                    name="fournisseur"
                    class="w-full rounded-2xl border border-white/10 bg-[var(--admin-card)] px-4 py-3 outline-none focus:border-[var(--admin-primary)]">
                <option value="">Tous les fournisseurs</option>
                @foreach($fournisseurs as $f)
                    <option value="{{ $f->id }}" @selected((string)$selected_fournisseur === (string)$f->id)>{{ $f->nom_frs }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="hidden">Filtrer</button>
    </form>

    <div class="rounded-2xl border border-white/10 bg-[var(--admin-card)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-white/60">
                    <tr>
                        <th class="text-left py-3 px-4 font-semibold">Nom</th>
                        <th class="text-left py-3 px-4 font-semibold">Email</th>
                        <th class="text-left py-3 px-4 font-semibold">Type</th>
                        <th class="text-left py-3 px-4 font-semibold">Fournisseur</th>
                        <th class="text-left py-3 px-4 font-semibold">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse($clients as $c)
                        <tr class="hover:bg-white/5">
                            <td class="py-3 px-4 font-semibold">{{ $c->prenom }} {{ $c->nom }}</td>
                            <td class="py-3 px-4 text-white/80">{{ $c->email }}</td>
                            <td class="py-3 px-4 text-white/80">{{ $c->type_client }}</td>
                            <td class="py-3 px-4 text-white/80">{{ $c->frs_nom ?? '-' }}</td>
                            <td class="py-3 px-4">
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ (int)$c->actif === 1 ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-400/20' : 'bg-red-500/15 text-red-300 border border-red-400/20' }}">
                                    {{ (int)$c->actif === 1 ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-white/60">Aucun client</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $clients->links() }}
    </div>
</div>
<script>
(() => {
    const form = document.getElementById('clientsFiltersForm');
    const input = document.getElementById('clientsSearchInput');
    const select = document.getElementById('clientsFournisseurSelect');
    if (!form || !input || !select) return;

    let t = null;
    const submit = () => {
        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
            return;
        }
        form.submit();
    };

    select.addEventListener('change', () => submit());

    input.addEventListener('input', () => {
        if (t) clearTimeout(t);
        t = setTimeout(() => submit(), 400);
    });

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            if (t) clearTimeout(t);
            submit();
        }
    });
})();
</script>
@endsection
