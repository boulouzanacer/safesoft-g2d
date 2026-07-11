@extends('layouts.admin')

@section('content')
<div class="hidden js-api-keys-config"
     data-create-open="{{ ($create_open || old('modal_context') === 'create' || $errors->any()) ? '1' : '0' }}"
     data-initial-type="{{ old('type', array_key_first($type_options)) }}"
     data-initial-api-key="{{ old('api_key', '') }}"
     data-close-url="{{ url('/admin/api-keys') }}"
     data-created-api-key="{{ e(session('created_api_key', '')) }}"></div>

<div x-data="apiKeysPage()" class="space-y-4">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <form method="GET" action="{{ url('/admin/api-keys') }}" class="flex flex-col md:flex-row gap-2 w-full md:w-auto">
            <div class="relative w-full md:w-[340px]">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-white/50"></i>
                <input name="q"
                       value="{{ $q }}"
                       placeholder="Rechercher une clé..."
                       class="w-full rounded-2xl border border-white/10 bg-[var(--admin-card)] pl-11 pr-4 py-3 outline-none focus:border-[var(--admin-primary)]">
            </div>

            <select name="type"
                    onchange="this.form.submit()"
                    class="rounded-2xl border border-white/10 bg-[var(--admin-card)] px-4 py-3 outline-none focus:border-[var(--admin-primary)]">
                <option value="">Tous les types</option>
                @foreach($type_options as $typeValue => $typeLabel)
                    <option value="{{ $typeValue }}" @selected($type_filter === $typeValue)>{{ $typeLabel }}</option>
                @endforeach
            </select>
        </form>

        <button type="button"
                class="inline-flex items-center justify-center gap-2 rounded-2xl px-4 py-3 font-bold text-white"
                style="background: linear-gradient(135deg, var(--admin-primary), #0A3D7A);"
                @click="openCreate()">
            <i class="fa-solid fa-plus"></i>
            Add API
        </button>
    </div>

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    @if(session('created_api_key'))
        <div class="rounded-2xl border border-sky-400/20 bg-sky-500/10 px-4 py-3 text-sky-200">
            Api key créée.
            <button type="button"
                    class="ml-2 underline"
                    data-api-key="{{ e(session('created_api_key', '')) }}"
                    @click="openViewFromButton($event)">
                Voir la clé
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl border border-rose-400/20 bg-rose-500/10 px-4 py-3 text-rose-200">
            Vérifiez les champs du formulaire Api Key.
        </div>
    @endif

    <div class="rounded-2xl border border-white/10 bg-[var(--admin-card)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-white/60">
                    <tr>
                        <th class="text-left py-3 px-4 font-semibold">Type</th>
                        <th class="text-left py-3 px-4 font-semibold">Api Key</th>
                        <th class="text-left py-3 px-4 font-semibold">Statut</th>
                        <th class="text-left py-3 px-4 font-semibold">Dernière utilisation</th>
                        <th class="text-left py-3 px-4 font-semibold">Créée le</th>
                        <th class="text-right py-3 px-4 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse($api_keys as $apiKey)
                        <tr class="hover:bg-white/5">
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold border {{ $apiKey->type === \App\Models\ApiKey::TYPE_CREATE_FOURNISSEUR ? 'bg-sky-500/15 text-sky-200 border-sky-400/20' : 'bg-violet-500/15 text-violet-200 border-violet-400/20' }}">
                                    {{ $apiKey->type_label }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-mono text-white/80">{{ $apiKey->masked_key }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $apiKey->actif ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-400/20' : 'bg-red-500/15 text-red-300 border border-red-400/20' }}">
                                    {{ $apiKey->actif ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-white/80">
                                {{ $apiKey->last_used_at ? $apiKey->last_used_at->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="py-3 px-4 text-white/80">
                                {{ optional($apiKey->created_at)->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button"
                                            class="h-9 w-9 inline-flex items-center justify-center rounded-xl text-xs font-bold border border-white/10 hover:bg-white/10"
                                            title="Voir"
                                            aria-label="Voir"
                                            data-api-key="{{ e($apiKey->api_key) }}"
                                            @click="openViewFromButton($event)">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>

                                    <form method="POST" action="{{ url('/admin/api-keys/'.$apiKey->id.'/toggle') }}">
                                        @csrf
                                        <label class="relative inline-flex items-center cursor-pointer select-none">
                                            <input type="checkbox"
                                                   class="sr-only peer"
                                                   onchange="this.form.submit()"
                                                   @checked($apiKey->actif)>
                                            <div class="w-11 h-6 rounded-full bg-white/15 peer-checked:bg-[var(--admin-primary)] transition"></div>
                                            <div class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition peer-checked:translate-x-5"></div>
                                        </label>
                                    </form>

                                    <button type="button"
                                            class="h-9 w-9 inline-flex items-center justify-center rounded-xl text-xs font-bold border border-red-400/20 text-red-300 hover:bg-red-500/10"
                                            title="Supprimer"
                                            aria-label="Supprimer"
                                            data-delete-action="{{ url('/admin/api-keys/'.$apiKey->id) }}"
                                            data-delete-label="{{ e($apiKey->masked_key) }}"
                                            @click="openDeleteFromButton($event)">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-white/60">Aucune api key</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $api_keys->links() }}
    </div>

    <div x-show="createOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6">
        <div class="absolute inset-0 bg-black/60" @click="closeCreate()"></div>
        <div class="relative w-full max-w-2xl rounded-2xl border border-white/10 bg-[var(--admin-card)] p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <div class="text-2xl font-extrabold tracking-wide">Créer une Api Key</div>
                    <div class="text-sm text-white/60">Choisissez un type puis générez la clé.</div>
                </div>
                <button type="button" class="text-white/60 hover:text-white" @click="closeCreate()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form method="POST" action="{{ url('/admin/api-keys') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="modal_context" value="create">

                <div>
                    <label class="block text-sm font-semibold text-white/70 mb-1">Type api</label>
                    <select name="type"
                            x-model="form.type"
                            class="w-full rounded-2xl border border-white/10 bg-[var(--admin-card)] px-4 py-3 outline-none focus:border-[var(--admin-primary)]">
                        @foreach($type_options as $typeValue => $typeLabel)
                            <option value="{{ $typeValue }}">{{ $typeLabel }}</option>
                        @endforeach
                    </select>
                    @error('type')
                        <div class="mt-1 text-xs text-red-300">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-white/70 mb-1">Api key</label>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input type="text"
                               name="api_key"
                               x-model="form.apiKey"
                               placeholder="Cliquez sur Generate"
                               class="flex-1 rounded-2xl border border-white/10 bg-[var(--admin-card)] px-4 py-3 outline-none focus:border-[var(--admin-primary)]">
                        <button type="button"
                                class="rounded-2xl px-4 py-3 font-bold border border-white/10 hover:bg-white/10"
                                @click="generateKey()">
                            Generate
                        </button>
                    </div>
                    <div class="mt-1 text-xs text-white/50">Utilisez cette clé dans le header `X-API-KEY` ou `Authorization: Bearer`.</div>
                    @error('api_key')
                        <div class="mt-1 text-xs text-red-300">{{ $message }}</div>
                    @enderror
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button"
                            class="rounded-2xl px-4 py-3 font-bold border border-white/10 hover:bg-white/10"
                            @click="closeCreate()">
                        Annuler
                    </button>
                    <button type="submit"
                            class="rounded-2xl px-6 py-3 font-extrabold text-white"
                            style="background: linear-gradient(135deg, var(--admin-primary), #0A3D7A);">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="viewOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-black/60" @click="viewOpen = false"></div>
        <div class="relative w-full max-w-xl rounded-2xl border border-white/10 bg-[var(--admin-card)] p-6">
            <div class="flex items-center justify-between">
                <div class="font-extrabold tracking-wide">Api Key</div>
                <button type="button" class="text-white/60 hover:text-white" @click="viewOpen = false">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="mt-4 rounded-xl border border-white/10 bg-black/20 p-4 font-mono text-sm break-all" x-text="currentKey"></div>

            <div class="mt-4 flex justify-end gap-2">
                <button type="button"
                        class="rounded-xl px-4 py-2 font-bold border border-white/10 hover:bg-white/10"
                        @click="copyCurrentKey()">
                    Copier
                </button>
                <button type="button"
                        class="rounded-xl px-4 py-2 font-bold text-white"
                        style="background: linear-gradient(135deg, var(--admin-primary), #0A3D7A);"
                        @click="viewOpen = false">
                    Fermer
                </button>
            </div>
        </div>
    </div>

    <div x-show="deleteOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-black/60" @click="deleteOpen = false"></div>
        <div class="relative w-full max-w-lg rounded-2xl border border-white/10 bg-[var(--admin-card)] p-6">
            <div class="flex items-center justify-between">
                <div class="font-extrabold tracking-wide">Supprimer l'Api Key</div>
                <button type="button" class="text-white/60 hover:text-white" @click="deleteOpen = false">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="mt-4 text-sm text-white/70">
                Cette action supprime définitivement la clé <span class="font-mono text-white/90" x-text="deleteLabel"></span>.
            </div>

            <form class="mt-5 flex justify-end gap-2" method="POST" :action="deleteAction">
                @csrf
                @method('DELETE')
                <button type="button"
                        class="rounded-2xl px-4 py-3 font-bold border border-white/10 hover:bg-white/10"
                        @click="deleteOpen = false">
                    Annuler
                </button>
                <button type="submit"
                        class="rounded-2xl px-4 py-3 font-bold text-white bg-red-600 hover:bg-red-500">
                    Supprimer
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    window.apiKeysPage = window.apiKeysPage || function () {
        const configElement = document.querySelector('.js-api-keys-config');
        const initialType = configElement ? (configElement.dataset.initialType || '') : '';
        const initialApiKey = configElement ? (configElement.dataset.initialApiKey || '') : '';
        const closeUrl = configElement ? (configElement.dataset.closeUrl || '') : '';
        const createdApiKey = configElement ? (configElement.dataset.createdApiKey || '') : '';

        return {
            createOpen: configElement ? (configElement.dataset.createOpen || '0') === '1' : false,
            viewOpen: false,
            deleteOpen: false,
            currentKey: '',
            deleteAction: '',
            deleteLabel: '',
            form: {
                type: initialType,
                apiKey: initialApiKey,
            },
            openCreate() {
                this.createOpen = true;

                if (!this.form.apiKey) {
                    this.generateKey();
                }
            },
            closeCreate() {
                if (closeUrl) {
                    window.location = closeUrl;
                }
            },
            openView(value) {
                this.currentKey = value || '';
                this.viewOpen = true;
            },
            openViewFromButton(event) {
                this.openView(event.currentTarget.dataset.apiKey || createdApiKey || '');
            },
            openDelete(action, label) {
                this.deleteAction = action || '';
                this.deleteLabel = label || '';
                this.deleteOpen = true;
            },
            openDeleteFromButton(event) {
                const target = event.currentTarget;
                this.openDelete(target.dataset.deleteAction || '', target.dataset.deleteLabel || '');
            },
            copyCurrentKey() {
                if (!this.currentKey || !navigator.clipboard) {
                    return;
                }

                navigator.clipboard.writeText(this.currentKey);
            },
            generateKey() {
                const bytes = new Uint8Array(24);

                if (window.crypto && typeof window.crypto.getRandomValues === 'function') {
                    window.crypto.getRandomValues(bytes);
                } else {
                    for (let index = 0; index < bytes.length; index += 1) {
                        bytes[index] = Math.floor(Math.random() * 256);
                    }
                }

                const suffix = Array.from(bytes, (value) => value.toString(16).padStart(2, '0')).join('');
                this.form.apiKey = 'g2d_' + suffix;
            },
        };
    };
</script>
@endsection
