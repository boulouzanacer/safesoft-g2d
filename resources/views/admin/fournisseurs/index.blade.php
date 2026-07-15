@extends('layouts.admin')

@section('content')
<div class="hidden js-admin-fournisseurs-config"
     data-create-open="{{ ($create_open || old('modal_context') === 'create') ? '1' : '0' }}"
     data-edit-open="{{ ($editing_fournisseur || old('modal_context') === 'edit') ? '1' : '0' }}"
     data-close-url="{{ url('/admin/fournisseurs') }}"
     data-regenerated-token="{{ e(session('regenerated_token', '')) }}"></div>

<div x-data="adminFournisseursPage()" class="space-y-4">
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
                    data-token="{{ e(session('regenerated_token', '')) }}"
                    @click="openTokenFromButton($event)">
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
                                            data-token="{{ e($f->token) }}"
                                            @click="openTokenFromButton($event)">
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
        <div class="absolute inset-0 bg-black/60" @click="closeModal()"></div>
        <div class="relative w-full max-w-4xl max-h-[90vh] overflow-y-auto rounded-2xl border border-white/10 bg-[var(--admin-card)] p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <div class="text-2xl font-extrabold tracking-wide">Créer une boutique</div>
                    <div class="text-sm text-white/60">Le token sera généré automatiquement.</div>
                </div>
                <button type="button" class="text-white/60 hover:text-white" @click="closeModal()">
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
            <div class="absolute inset-0 bg-black/60" @click="closeModal()"></div>
            <div class="relative w-full max-w-4xl max-h-[90vh] overflow-y-auto rounded-2xl border border-white/10 bg-[var(--admin-card)] p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <div class="text-2xl font-extrabold tracking-wide">Éditer boutique</div>
                        <div class="text-sm text-white/60">{{ $editing_fournisseur->nom_frs }}</div>
                    </div>
                    <button type="button" class="text-white/60 hover:text-white" @click="closeModal()">
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

                <div class="mt-6 rounded-2xl border border-white/10 bg-black/20 p-5">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <div class="text-lg font-extrabold tracking-wide">Domaines personnalisés</div>
                            <div class="mt-1 text-sm text-white/60">
                                Gère ici les domaines publics de cette boutique, par exemple <span class="font-mono text-white/80">www.boutika.com</span>.
                            </div>
                        </div>
                        <div class="text-xs text-white/50">
                            DNS conseillé: <span class="font-mono text-white/80">CNAME</span> vers <span class="font-mono text-white/80">g2d-dz.com</span>
                        </div>
                    </div>

                    <form method="POST"
                          action="{{ url('/admin/fournisseurs/'.$editing_fournisseur->id.'/custom-domains') }}"
                          class="mt-4 grid grid-cols-1 lg:grid-cols-[1fr_auto] gap-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-semibold text-white/70 mb-1">Ajouter un domaine</label>
                            <input name="domain"
                                   value="{{ old('domain') }}"
                                   placeholder="www.boutika.com"
                                   class="w-full rounded-2xl border border-white/10 bg-[var(--admin-card)] px-4 py-3 outline-none focus:border-[var(--admin-primary)]">
                            @error('domain')
                                <div class="mt-1 text-xs font-semibold text-red-300">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="flex items-end">
                            <button type="submit"
                                    class="w-full lg:w-auto rounded-2xl px-5 py-3 font-extrabold text-white"
                                    style="background: linear-gradient(135deg, var(--admin-primary), #0A3D7A);">
                                Ajouter le domaine
                            </button>
                        </div>
                    </form>

                    <div class="mt-5 space-y-3">
                        @forelse($editing_fournisseur->customDomains as $domain)
                            <div class="rounded-2xl border border-white/10 bg-[var(--admin-card)] p-4">
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <div class="font-bold break-all">{{ $domain->domain }}</div>
                                            @if($domain->is_primary)
                                                <span class="inline-flex items-center rounded-full border border-sky-400/20 bg-sky-500/15 px-2.5 py-1 text-[11px] font-bold text-sky-200">
                                                    Principal
                                                </span>
                                            @endif
                                            @if($domain->verified_at)
                                                <span class="inline-flex items-center rounded-full border border-emerald-400/20 bg-emerald-500/15 px-2.5 py-1 text-[11px] font-bold text-emerald-200">
                                                    Vérifié
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-full border border-amber-400/20 bg-amber-500/15 px-2.5 py-1 text-[11px] font-bold text-amber-200">
                                                    En attente de visite
                                                </span>
                                            @endif
                                        </div>
                                        <div class="mt-2 text-xs text-white/50">
                                            Ce domaine ouvrira directement la boutique sans afficher le domaine global de la plateforme.
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap gap-2">
                                        @if(! $domain->is_primary)
                                            <form method="POST" action="{{ url('/admin/fournisseurs/'.$editing_fournisseur->id.'/custom-domains/'.$domain->id.'/primary') }}">
                                                @csrf
                                                <button type="submit"
                                                        class="rounded-2xl px-4 py-2 text-sm font-extrabold border border-white/10 hover:bg-white/10">
                                                    Définir principal
                                                </button>
                                            </form>
                                        @endif

                                        <form method="POST" action="{{ url('/admin/fournisseurs/'.$editing_fournisseur->id.'/custom-domains/'.$domain->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="rounded-2xl px-4 py-2 text-sm font-extrabold border border-red-400/20 bg-red-500/10 text-red-200 hover:bg-red-500/20">
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-white/10 bg-[var(--admin-card)] p-4 text-sm text-white/60">
                                Aucun domaine personnalisé pour cette boutique.
                            </div>
                        @endforelse
                    </div>
                </div>
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
    window.adminFournisseursPage = window.adminFournisseursPage || function () {
        const configElement = document.querySelector('.js-admin-fournisseurs-config');
        const closeUrl = configElement ? (configElement.dataset.closeUrl || '') : '';
        const regeneratedToken = configElement ? (configElement.dataset.regeneratedToken || '') : '';

        return {
            tokenOpen: false,
            tokenValue: '',
            createOpen: configElement ? (configElement.dataset.createOpen || '0') === '1' : false,
            editOpen: configElement ? (configElement.dataset.editOpen || '0') === '1' : false,
            openToken(value) {
                this.tokenValue = value || '';
                this.tokenOpen = true;
            },
            openTokenFromButton(event) {
                this.openToken(event.currentTarget.dataset.token || regeneratedToken || '');
            },
            closeModal() {
                if (closeUrl) {
                    window.location = closeUrl;
                }
            },
        };
    };

    (() => {
        const form = document.getElementById('fournisseursFiltersForm');
        const input = document.getElementById('fournisseursSearchInput');
        if (!form || !input) {
            return;
        }

        let t = null;
        const submit = () => {
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
                return;
            }

            form.submit();
        };

        input.addEventListener('input', () => {
            if (t) {
                clearTimeout(t);
            }

            t = setTimeout(() => submit(), 400);
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                if (t) {
                    clearTimeout(t);
                }

                submit();
            }
        });
    })();
</script>
@endsection
