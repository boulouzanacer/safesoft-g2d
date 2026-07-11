@extends('layouts.admin')

@section('content')
<div class="space-y-4">
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-red-200">
            {{ session('error') }}
        </div>
    @endif

    <form id="clientsFiltersForm" method="GET" action="{{ url('/admin/clients') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3">
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
                    @disabled($without_fournisseur)
                    class="w-full rounded-2xl border border-white/10 bg-[var(--admin-card)] px-4 py-3 outline-none focus:border-[var(--admin-primary)]">
                <option value="">Toutes les boutiques</option>
                @foreach($fournisseurs as $f)
                    <option value="{{ $f->id }}" @selected((string)$selected_fournisseur === (string)$f->id)>{{ $f->nom_frs }}</option>
                @endforeach
            </select>
        </div>

        <label class="inline-flex items-center gap-3 rounded-2xl border border-white/10 bg-[var(--admin-card)] px-4 py-3 cursor-pointer">
            <input id="clientsWithoutFournisseurCheckbox"
                   type="checkbox"
                   name="without_fournisseur"
                   value="1"
                   @checked($without_fournisseur)
                   class="h-4 w-4 rounded border-white/20 bg-transparent text-[var(--admin-primary)] focus:ring-[var(--admin-primary)]">
            <span class="text-sm text-white/85">Sans boutique</span>
        </label>

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
                        <th class="text-left py-3 px-4 font-semibold">Boutique</th>
                        <th class="text-left py-3 px-4 font-semibold">Synced PME</th>
                        <th class="text-left py-3 px-4 font-semibold">Statut</th>
                        <th class="text-right py-3 px-4 font-semibold">Actions</th>
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
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ (int)($c->synced_pme ?? 0) === 1 ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-400/20' : 'bg-red-500/15 text-red-300 border border-red-400/20' }}">
                                    {{ (int)($c->synced_pme ?? 0) === 1 ? 'Synced' : 'Not Synced' }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ (int)$c->actif === 1 ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-400/20' : 'bg-red-500/15 text-red-300 border border-red-400/20' }}">
                                    {{ (int)$c->actif === 1 ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ url('/admin/clients/'.$c->id) }}"
                                       class="h-9 w-9 inline-flex items-center justify-center rounded-xl text-xs font-bold border border-sky-400/20 text-sky-300 hover:bg-sky-500/10"
                                       title="Détail">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    @if($c->id_frs === null)
                                        <form method="POST" action="{{ url('/admin/clients/'.$c->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    onclick="return confirm('Supprimer ce client sans boutique ?')"
                                                    class="h-9 w-9 inline-flex items-center justify-center rounded-xl text-xs font-bold border border-red-400/20 text-red-300 hover:bg-red-500/10"
                                                    title="Supprimer">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-white/40">-</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-white/60">Aucun client</td>
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
    const withoutFournisseur = document.getElementById('clientsWithoutFournisseurCheckbox');
    if (!form || !input || !select || !withoutFournisseur) return;

    let t = null;
    const submit = () => {
        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
            return;
        }
        form.submit();
    };

    select.addEventListener('change', () => submit());
    withoutFournisseur.addEventListener('change', () => submit());

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
