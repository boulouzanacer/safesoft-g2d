@extends('layouts.admin')

@section('content')
<div class="space-y-4">
    <form id="produitsFiltersForm" method="GET" action="{{ url('/admin/produits') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <div class="md:col-span-2">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-white/50"></i>
                <input id="produitsSearchInput"
                       name="q"
                       value="{{ $q }}"
                       placeholder="Rechercher référence/désignation/catégorie..."
                       class="w-full rounded-2xl border border-white/10 bg-[var(--admin-card)] pl-11 pr-4 py-3 outline-none focus:border-[var(--admin-primary)]">
            </div>
        </div>

        <div>
            <select id="produitsFournisseurSelect"
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
                        <th class="text-left py-3 px-4 font-semibold">Réf</th>
                        <th class="text-left py-3 px-4 font-semibold">Désignation</th>
                        <th class="text-left py-3 px-4 font-semibold">Catégorie</th>
                        <th class="text-left py-3 px-4 font-semibold">Fournisseur</th>
                        <th class="text-right py-3 px-4 font-semibold">PV 1</th>
                        <th class="text-right py-3 px-4 font-semibold">Stock</th>
                        <th class="text-left py-3 px-4 font-semibold">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse($produits as $p)
                        <tr class="hover:bg-white/5">
                            <td class="py-3 px-4 font-semibold">{{ $p->reference }}</td>
                            <td class="py-3 px-4 text-white/80">{{ $p->designation }}</td>
                            <td class="py-3 px-4 text-white/80">{{ $p->categorie }}</td>
                            <td class="py-3 px-4 text-white/80">{{ $p->frs_nom }}</td>
                            <td class="py-3 px-4 text-right font-bold">{{ number_format((float)$p->pv_1, 2, '.', ' ') }}</td>
                            <td class="py-3 px-4 text-right font-bold">{{ (int)$p->stock }}</td>
                            <td class="py-3 px-4">
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ (int)$p->actif === 1 ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-400/20' : 'bg-red-500/15 text-red-300 border border-red-400/20' }}">
                                    {{ (int)$p->actif === 1 ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-white/60">Aucun produit</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $produits->links() }}
    </div>
</div>
<script>
(() => {
    const form = document.getElementById('produitsFiltersForm');
    const input = document.getElementById('produitsSearchInput');
    const select = document.getElementById('produitsFournisseurSelect');
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
