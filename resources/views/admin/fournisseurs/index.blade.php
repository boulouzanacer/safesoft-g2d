@extends('layouts.admin')

@section('content')
<div x-data="{ tokenOpen: false, tokenValue: '', createOpen: {{ ($create_open || old('modal_context') === 'create') ? 'true' : 'false' }}, editOpen: {{ ($editing_fournisseur || old('modal_context') === 'edit') ? 'true' : 'false' }} }" class="space-y-4">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <form id="fournisseursFiltersForm" method="GET" action="{{ url('/admin/fournisseurs') }}" class="flex items-center gap-2 w-full md:w-auto">
            <div class="relative w-full md:w-[340px]">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-white/50"></i>
                <input id="fournisseursSearchInput"
                       name="q"
                       value="{{ $q }}"
                       placeholder="Rechercher nom ou email..."
                       class="w-full rounded-2xl border border-white/10 bg-[var(--admin-card)] pl-11 pr-4 py-3 outline-none focus:border-[var(--admin-primary)]">
            </div>
            <button type="submit" class="hidden">Filtrer</button>
        </form>

        <a href="{{ url('/admin/fournisseurs?create=1') }}"
           class="inline-flex items-center justify-center gap-2 rounded-2xl px-4 py-3 font-bold text-white"
           style="background: linear-gradient(135deg, var(--admin-primary), #0A3D7A);">
            <i class="fa-solid fa-plus"></i>
            Nouvelle boutique
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    @if(session('regenerated_token'))
        <div class="rounded-2xl border border-sky-400/20 bg-sky-500/10 px-4 py-3 text-sky-200">
            Token régénéré.
            <button type="button"
                    class="ml-2 underline"
                    @click="tokenValue='{{ session('regenerated_token') }}'; tokenOpen=true">
                Voir token
            </button>
        </div>
    @endif

    <div class="rounded-2xl border border-white/10 bg-[var(--admin-card)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-white/60">
                    <tr>
                        <th class="text-left py-3 px-4 font-semibold">Nom</th>
                        <th class="text-left py-3 px-4 font-semibold">Catégorie</th>
                        <th class="text-left py-3 px-4 font-semibold">Email</th>
                        <th class="text-left py-3 px-4 font-semibold">Tel</th>
                        <th class="text-left py-3 px-4 font-semibold">Wilaya</th>
                        <th class="text-left py-3 px-4 font-semibold">Expiration</th>
                        <th class="text-left py-3 px-4 font-semibold">Statut</th>
                        <th class="text-left py-3 px-4 font-semibold">Token</th>
                        <th class="text-right py-3 px-4 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse($fournisseurs as $f)
                        <tr class="hover:bg-white/5">
                            <td class="py-3 px-4 font-semibold">{{ $f->nom_frs }}</td>
                            <td class="py-3 px-4 text-white/80">{{ $f->boutique_category_name ?? '—' }}</td>
                            <td class="py-3 px-4 text-white/80">{{ $f->email }}</td>
                            <td class="py-3 px-4 text-white/80">{{ $f->telephone }}</td>
                            <td class="py-3 px-4 text-white/80">{{ $f->wilaya_nom }}</td>
                            <td class="py-3 px-4">
                                <div class="flex flex-col gap-1">
                                    <span class="text-white/80">{{ $f->expires_at ? $f->expires_at->format('d/m/Y') : '-' }}</span>
                                    <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $f->isExpired() ? 'bg-red-500/15 text-red-300 border border-red-400/20' : 'bg-emerald-500/15 text-emerald-300 border border-emerald-400/20' }}">
                                        {{ $f->isExpired() ? 'Expire' : 'Valide' }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ (int)$f->actif === 1 ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-400/20' : 'bg-red-500/15 text-red-300 border border-red-400/20' }}">
                                        {{ (int)$f->actif === 1 ? 'Actif' : 'Inactif' }}
                                    </span>
                                    <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ (int)($f->is_visible ?? 0) === 1 ? 'bg-sky-500/15 text-sky-200 border border-sky-400/20' : 'bg-slate-500/15 text-slate-200 border border-slate-400/20' }}">
                                        {{ (int)($f->is_visible ?? 0) === 1 ? 'Visible' : 'Non visible' }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-white/60">***</span>
                                    <button type="button"
                                            class="text-[var(--admin-primary)] hover:opacity-90 text-sm font-semibold"
                                            @click="tokenValue='{{ $f->token }}'; tokenOpen=true">
                                        Voir Token
                                    </button>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center justify-end gap-2">
                                    <form method="POST" action="{{ url('/admin/fournisseurs/'.$f->id.'/toggle-actif') }}">
                                        @csrf
                                        <label class="relative inline-flex items-center cursor-pointer select-none">
                                            <input type="checkbox"
                                                   class="sr-only peer"
                                                   onchange="this.form.submit()"
                                                   @checked((int)$f->actif === 1)>
                                            <div class="w-11 h-6 rounded-full bg-white/15 peer-checked:bg-[var(--admin-primary)] transition"></div>
                                            <div class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition peer-checked:translate-x-5"></div>
                                        </label>
                                    </form>

                                    <form method="POST" action="{{ url('/admin/fournisseurs/'.$f->id.'/regenerer-token') }}">
                                        @csrf
                                        <button type="submit"
                                                onclick="return confirm('Régénérer le token ?')"
                                                class="h-9 w-9 inline-flex items-center justify-center rounded-xl text-xs font-bold border border-white/10 hover:bg-white/10"
                                                title="Régénérer token"
                                                aria-label="Régénérer token">
                                            <i class="fa-solid fa-arrows-rotate"></i>
                                        </button>
                                    </form>

                                    <a href="{{ url('/admin/fournisseurs?edit='.$f->id) }}"
                                       class="h-9 w-9 inline-flex items-center justify-center rounded-xl text-xs font-bold border border-white/10 hover:bg-white/10"
                                       title="Éditer"
                                       aria-label="Éditer">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>

                                    <form method="POST" action="{{ url('/admin/fournisseurs/'.$f->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                onclick="return confirm('Supprimer cette boutique ?')"
                                                class="h-9 w-9 inline-flex items-center justify-center rounded-xl text-xs font-bold border border-red-400/20 text-red-300 hover:bg-red-500/10"
                                                title="Supprimer"
                                                aria-label="Supprimer">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-10 text-center text-white/60">Aucune boutique</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $fournisseurs->links() }}
    </div>

    <div x-show="createOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6">
        <div class="absolute inset-0 bg-black/60" @click="window.location='{{ url('/admin/fournisseurs') }}'"></div>
        <div class="relative w-full max-w-4xl max-h-[90vh] overflow-y-auto rounded-2xl border border-white/10 bg-[var(--admin-card)] p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <div class="text-2xl font-extrabold tracking-wide">Créer une boutique</div>
                    <div class="text-sm text-white/60">Le token sera généré automatiquement.</div>
                </div>
                <button type="button" class="text-white/60 hover:text-white" @click="window.location='{{ url('/admin/fournisseurs') }}'">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form method="POST" action="{{ url('/admin/fournisseurs') }}" enctype="multipart/form-data">
                <input type="hidden" name="modal_context" value="create">
                @include('admin.fournisseurs._form', [
                    'isEdit' => false,
                    'frs' => null,
                    'wilayas' => $wilayas,
                    'communes' => collect(),
                    'formPrefix' => 'create_frs',
                ])

                <div class="mt-6 flex justify-end">
                    <button type="submit"
                            class="rounded-2xl px-6 py-3 font-extrabold text-white"
                            style="background: linear-gradient(135deg, var(--admin-primary), #0A3D7A);">
                        Créer
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($editing_fournisseur)
        <div x-show="editOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6">
            <div class="absolute inset-0 bg-black/60" @click="window.location='{{ url('/admin/fournisseurs') }}'"></div>
            <div class="relative w-full max-w-4xl max-h-[90vh] overflow-y-auto rounded-2xl border border-white/10 bg-[var(--admin-card)] p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <div class="text-2xl font-extrabold tracking-wide">Éditer boutique</div>
                        <div class="text-sm text-white/60">{{ $editing_fournisseur->nom_frs }}</div>
                    </div>
                    <button type="button" class="text-white/60 hover:text-white" @click="window.location='{{ url('/admin/fournisseurs') }}'">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form method="POST" action="{{ url('/admin/fournisseurs/'.$editing_fournisseur->id) }}" enctype="multipart/form-data">
                    @method('PUT')
                    <input type="hidden" name="modal_context" value="edit">
                    @include('admin.fournisseurs._form', [
                        'isEdit' => true,
                        'frs' => $editing_fournisseur,
                        'wilayas' => $wilayas,
                        'communes' => $edit_communes,
                        'formPrefix' => 'edit_frs',
                    ])

                    <div class="mt-6 flex justify-end">
                        <button type="submit"
                                class="rounded-2xl px-6 py-3 font-extrabold text-white"
                                style="background: linear-gradient(135deg, var(--admin-primary), #0A3D7A);">
                            Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div x-show="tokenOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-black/60" @click="tokenOpen=false"></div>
        <div class="relative w-full max-w-lg rounded-2xl border border-white/10 bg-[var(--admin-card)] p-6">
            <div class="flex items-center justify-between">
                <div class="font-extrabold tracking-wide">Token Boutique</div>
                <button type="button" class="text-white/60 hover:text-white" @click="tokenOpen=false">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="mt-4 rounded-xl border border-white/10 bg-black/20 p-4 font-mono text-sm break-all" x-text="tokenValue"></div>

            <div class="mt-4 flex justify-end gap-2">
                <button type="button"
                        class="rounded-xl px-4 py-2 font-bold border border-white/10 hover:bg-white/10"
                        @click="navigator.clipboard.writeText(tokenValue)">
                    Copier
                </button>
                <button type="button"
                        class="rounded-xl px-4 py-2 font-bold text-white"
                        style="background: linear-gradient(135deg, var(--admin-primary), #0A3D7A);"
                        @click="tokenOpen=false">
                    Fermer
                </button>
            </div>
        </div>
    </div>
</div>
<script>
(() => {
    const form = document.getElementById('fournisseursFiltersForm');
    const input = document.getElementById('fournisseursSearchInput');
    if (!form || !input) return;

    let t = null;
    const submit = () => {
        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
            return;
        }
        form.submit();
    };

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
