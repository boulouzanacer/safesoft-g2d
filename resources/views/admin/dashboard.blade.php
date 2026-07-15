@extends('layouts.admin')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div x-data="adminDashboardPage()" class="space-y-4">
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

    @if($errors->any())
        <div class="rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-red-200">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
        <div class="rounded-2xl p-5 border border-white/10 bg-[var(--admin-card)]">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-white/60">Total Boutiques</div>
                    <div class="text-3xl font-extrabold mt-1">{{ $nb_fournisseurs }}</div>
                </div>
                <div class="h-12 w-12 rounded-2xl flex items-center justify-center"
                     style="background: linear-gradient(135deg, var(--admin-primary), #0A3D7A);">
                    <i class="fa-solid fa-store text-white text-lg"></i>
                </div>
            </div>
        </div>

        <a href="{{ url('/admin/clients') }}" class="block rounded-2xl p-5 border border-white/10 bg-[var(--admin-card)] hover:bg-white/5">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-white/60">Total Clients</div>
                    <div class="text-3xl font-extrabold mt-1">{{ $nb_clients }}</div>
                    <div class="mt-2 text-xs text-white/50">Ouvrir la liste clients</div>
                </div>
                <div class="h-12 w-12 rounded-2xl flex items-center justify-center"
                     style="background: linear-gradient(135deg, #22c55e, #16a34a);">
                    <i class="fa-solid fa-users text-white text-lg"></i>
                </div>
            </div>
        </a>

        <a href="{{ url('/admin/api-keys') }}" class="block rounded-2xl p-5 border border-white/10 bg-[var(--admin-card)] hover:bg-white/5">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-white/60">Api Keys actives</div>
                    <div class="text-3xl font-extrabold mt-1">{{ $nb_api_keys_actives }}</div>
                    <div class="mt-2 text-xs text-white/50">Gérer les accès API</div>
                </div>
                <div class="h-12 w-12 rounded-2xl flex items-center justify-center"
                     style="background: linear-gradient(135deg, #06b6d4, #0284c7);">
                    <i class="fa-solid fa-key text-white text-lg"></i>
                </div>
            </div>
        </a>

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

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
        <div class="xl:col-span-2 rounded-2xl p-5 border border-white/10 bg-[var(--admin-card)]">
            <div class="flex items-center justify-between mb-4">
                <div class="font-extrabold tracking-wide">Commandes (7 derniers jours)</div>
                <div class="text-sm text-white/60">Total</div>
            </div>
            <canvas id="ordersChart" height="110"></canvas>
        </div>

        <div class="rounded-2xl p-5 border border-white/10 bg-[var(--admin-card)]">
            <div class="font-extrabold tracking-wide mb-4">Boutiques récentes</div>
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

    <div class="rounded-2xl p-5 border border-white/10 bg-[var(--admin-card)] overflow-hidden">
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
                        <th class="text-left py-3 pr-4 font-semibold">Boutique</th>
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
                            <td class="py-3 pr-4 text-white/80">
                                @if((int)($c->client_id ?? 0) > 0)
                                    <a href="{{ url('/admin/clients/'.$c->client_id) }}"
                                       class="inline-flex items-center gap-2 font-semibold text-sky-300 hover:text-sky-200">
                                        <span>{{ trim(($c->client_prenom ?? '').' '.($c->client_nom ?? '')) }}</span>
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                    </a>
                                @else
                                    {{ trim(($c->client_prenom ?? '').' '.($c->client_nom ?? '')) }}
                                @endif
                            </td>
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

    <div id="boutique-categories-section" class="rounded-2xl p-5 border border-white/10 bg-[var(--admin-card)]">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div>
                <div class="font-extrabold tracking-wide">Catégories boutiques</div>
                <div class="text-sm text-white/60">Liste prédéfinie utilisée dans la création des boutiques et dans le site web.</div>
            </div>
            <button type="button"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl px-4 py-3 font-bold text-white"
                    style="background: linear-gradient(135deg, var(--admin-primary), #0A3D7A);"
                    @click="categoryCreateOpen = true">
                <i class="fa-solid fa-plus"></i>
                Nouvelle catégorie
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @forelse($boutique_categories as $category)
                <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex items-start gap-3">
                            <div class="h-14 w-14 shrink-0 overflow-hidden rounded-2xl border border-white/10 bg-white/5">
                                @if($category->image_url)
                                    <img src="{{ $category->image_url }}"
                                         alt="{{ $category->name }}"
                                         class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-white/40">
                                        <i class="fa-solid fa-image text-lg"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <div class="font-extrabold truncate">{{ $category->name }}</div>
                                <div class="mt-1 text-xs text-white/50 font-mono">{{ $category->slug }}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button"
                                    class="h-9 w-9 inline-flex items-center justify-center rounded-xl text-xs font-bold border border-white/10 hover:bg-white/10"
                                    title="Éditer"
                                    aria-label="Éditer"
                                    data-category-id="{{ $category->id }}"
                                    data-category-name="{{ e($category->name) }}"
                                    data-category-image-url="{{ $category->image_url }}"
                                    @click="openCategoryEditFromButton($event)">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <button type="button"
                                    class="h-9 w-9 inline-flex items-center justify-center rounded-xl text-xs font-bold border border-red-400/20 text-red-300 hover:bg-red-500/10 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-transparent"
                                    title="{{ (int) $category->fournisseurs_count > 0 ? 'Impossible de supprimer une catégorie déjà utilisée' : 'Supprimer' }}"
                                    aria-label="Supprimer"
                                    @disabled((int) $category->fournisseurs_count > 0)
                                    data-delete-action="{{ url('/admin/boutique-categories/'.$category->id) }}"
                                    data-category-name="{{ e($category->name) }}"
                                    data-category-count="{{ (int) $category->fournisseurs_count }}"
                                    @click="openCategoryDeleteFromButton($event)">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-between gap-3">
                        <span class="text-xs font-bold px-2.5 py-1 rounded-full border border-sky-400/20 bg-sky-500/10 text-sky-200">
                            {{ (int) $category->fournisseurs_count }} boutique(s)
                        </span>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-white/10 bg-black/20 p-10 text-center text-white/60">
                    Aucune catégorie boutique.
                </div>
            @endforelse
        </div>
    </div>

    <div x-show="categoryCreateOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6">
        <div class="absolute inset-0 bg-black/60" @click="categoryCreateOpen = false"></div>
        <div class="relative w-full max-w-lg rounded-2xl border border-white/10 bg-[var(--admin-card)] p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <div class="text-2xl font-extrabold tracking-wide">Nouvelle catégorie</div>
                    <div class="text-sm text-white/60">Cette catégorie sera disponible dans les boutiques.</div>
                </div>
                <button type="button" class="text-white/60 hover:text-white" @click="categoryCreateOpen = false">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form method="POST" action="{{ url('/admin/boutique-categories') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                <input type="hidden" name="modal_context" value="create">
                <div>
                    <label class="block text-sm font-semibold text-white/70 mb-1">Nom catégorie</label>
                    <input type="text"
                           name="name"
                           value="{{ old('name') }}"
                           class="w-full rounded-2xl border border-white/10 bg-[var(--admin-card)] px-4 py-3 outline-none focus:border-[var(--admin-primary)]"
                           placeholder="Ex: Cosmétique"
                           required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-white/70 mb-2">Image catégorie</label>
                    <div class="flex items-start gap-4">
                        <div class="h-24 w-24 overflow-hidden rounded-2xl border border-white/10 bg-white/5">
                            <template x-if="categoryCreateImagePreview">
                                <img :src="categoryCreateImagePreview" alt="Aperçu catégorie" class="h-full w-full object-cover">
                            </template>
                            <template x-if="!categoryCreateImagePreview">
                                <div class="flex h-full w-full items-center justify-center text-white/40">
                                    <i class="fa-solid fa-image text-2xl"></i>
                                </div>
                            </template>
                        </div>
                        <div class="flex-1">
                            <input type="file"
                                   name="image"
                                   accept="image/*"
                                   class="w-full rounded-2xl border border-white/10 bg-[var(--admin-card)] px-4 py-3 outline-none file:mr-4 file:rounded-xl file:border-0 file:bg-white/10 file:px-4 file:py-2 file:text-white hover:file:bg-white/20"
                                   @change="previewCategoryImage($event, 'create')"
                                   required>
                            <div class="mt-1 text-xs text-white/50">Image obligatoire pour afficher la catégorie dans la plateforme.</div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button"
                            class="rounded-2xl px-4 py-3 font-bold border border-white/10 hover:bg-white/10"
                            @click="categoryCreateOpen = false">
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

    <div x-show="categoryEditOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6">
        <div class="absolute inset-0 bg-black/60" @click="categoryEditOpen = false"></div>
        <div class="relative w-full max-w-lg rounded-2xl border border-white/10 bg-[var(--admin-card)] p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <div class="text-2xl font-extrabold tracking-wide">Modifier catégorie</div>
                    <div class="text-sm text-white/60">Mettre à jour le nom utilisé dans les filtres publics.</div>
                </div>
                <button type="button" class="text-white/60 hover:text-white" @click="categoryEditOpen = false">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form method="POST" :action="categoryEditAction" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')
                <input type="hidden" name="modal_context" value="edit">
                <input type="hidden" name="category_id" :value="categoryEditId">
                <div>
                    <label class="block text-sm font-semibold text-white/70 mb-1">Nom catégorie</label>
                    <input type="text"
                           name="name"
                           x-model="categoryEditName"
                           class="w-full rounded-2xl border border-white/10 bg-[var(--admin-card)] px-4 py-3 outline-none focus:border-[var(--admin-primary)]"
                           required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-white/70 mb-2">Image catégorie</label>
                    <div class="flex items-start gap-4">
                        <div class="h-24 w-24 overflow-hidden rounded-2xl border border-white/10 bg-white/5">
                            <template x-if="categoryEditImagePreview">
                                <img :src="categoryEditImagePreview" alt="Aperçu catégorie" class="h-full w-full object-cover">
                            </template>
                            <template x-if="!categoryEditImagePreview">
                                <div class="flex h-full w-full items-center justify-center text-white/40">
                                    <i class="fa-solid fa-image text-2xl"></i>
                                </div>
                            </template>
                        </div>
                        <div class="flex-1">
                            <input type="file"
                                   name="image"
                                   accept="image/*"
                                   class="w-full rounded-2xl border border-white/10 bg-[var(--admin-card)] px-4 py-3 outline-none file:mr-4 file:rounded-xl file:border-0 file:bg-white/10 file:px-4 file:py-2 file:text-white hover:file:bg-white/20"
                                   @change="previewCategoryImage($event, 'edit')">
                            <div class="mt-1 text-xs text-white/50">Optionnel en modification. Tu peux changer uniquement le nom ou remplacer l'image ici.</div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button"
                            class="rounded-2xl px-4 py-3 font-bold border border-white/10 hover:bg-white/10"
                            @click="categoryEditOpen = false">
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

    <div x-show="categoryDeleteOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6">
        <div class="absolute inset-0 bg-black/60" @click="categoryDeleteOpen = false"></div>
        <div class="relative w-full max-w-lg rounded-2xl border border-white/10 bg-[var(--admin-card)] p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="text-2xl font-extrabold tracking-wide">Supprimer catégorie</div>
                <button type="button" class="text-white/60 hover:text-white" @click="categoryDeleteOpen = false">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="text-sm text-white/70">
                Voulez-vous supprimer la catégorie
                <span class="font-semibold text-white" x-text="categoryDeleteName"></span> ?
            </div>
            <div class="mt-2 text-xs text-white/50">
                Suppression autorisée uniquement si aucune boutique n’utilise cette catégorie.
            </div>

            <form method="POST" :action="categoryDeleteAction" class="mt-5 flex justify-end gap-2">
                @csrf
                @method('DELETE')
                <button type="button"
                        class="rounded-2xl px-4 py-3 font-bold border border-white/10 hover:bg-white/10"
                        @click="categoryDeleteOpen = false">
                    Annuler
                </button>
                <button type="submit"
                        class="rounded-2xl px-4 py-3 font-bold text-white bg-red-600 hover:bg-red-500"
                        :disabled="categoryDeleteCount > 0"
                        :class="categoryDeleteCount > 0 ? 'opacity-50 cursor-not-allowed' : ''">
                    Supprimer
                </button>
            </form>
        </div>
    </div>
</div>

@php
    $oldCategoryId = (int) old('category_id', 0);
    $oldEditingCategory = $oldCategoryId > 0
        ? $boutique_categories->firstWhere('id', $oldCategoryId)
        : null;
@endphp

<div class="hidden js-admin-dashboard-config"
     data-chart-labels="{{ e(json_encode($chart_labels)) }}"
     data-chart-series="{{ e(json_encode($chart_series)) }}"
     data-category-base-url="{{ url('/admin/boutique-categories') }}"
     data-category-create-open="{{ old('modal_context', $errors->any() ? 'create' : '') === 'create' ? '1' : '0' }}"
     data-category-edit-open="{{ old('modal_context') === 'edit' ? '1' : '0' }}"
     data-category-old-id="{{ old('category_id', '') }}"
     data-category-old-name="{{ old('name', '') }}"
     data-category-old-image-url="{{ $oldEditingCategory?->image_url ?? '' }}"></div>

<script>
    (function () {
        const configElement = document.querySelector('.js-admin-dashboard-config');
        if (! configElement) {
            return;
        }

        window.adminDashboardPage = window.adminDashboardPage || function () {
            const initialEditId = configElement.dataset.categoryOldId || '';
            const initialEditOpen = (configElement.dataset.categoryEditOpen || '0') === '1';

            return {
                categoryCreateOpen: (configElement.dataset.categoryCreateOpen || '0') === '1',
                categoryEditOpen: initialEditOpen,
                categoryDeleteOpen: false,
                categoryEditAction: initialEditId ? (configElement.dataset.categoryBaseUrl || '') + '/' + initialEditId : '',
                categoryEditId: initialEditId,
                categoryEditName: configElement.dataset.categoryOldName || '',
                categoryCreateImagePreview: '',
                categoryEditImagePreview: configElement.dataset.categoryOldImageUrl || '',
                categoryDeleteAction: '',
                categoryDeleteName: '',
                categoryDeleteCount: 0,
                openCategoryEdit(category) {
                    this.categoryEditAction = (configElement.dataset.categoryBaseUrl || '') + '/' + category.id;
                    this.categoryEditId = category.id || '';
                    this.categoryEditName = category.name || '';
                    this.categoryEditImagePreview = category.imageUrl || '';
                    this.categoryEditOpen = true;
                },
                openCategoryEditFromButton(event) {
                    const target = event.currentTarget;
                    this.openCategoryEdit({
                        id: target.dataset.categoryId || '',
                        name: target.dataset.categoryName || '',
                        imageUrl: target.dataset.categoryImageUrl || '',
                    });
                },
                previewCategoryImage(event, mode) {
                    const file = event.target.files && event.target.files[0] ? event.target.files[0] : null;
                    if (!file) {
                        if (mode === 'create') {
                            this.categoryCreateImagePreview = '';
                            return;
                        }

                        this.categoryEditImagePreview = '';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = (loadEvent) => {
                        const result = loadEvent.target && typeof loadEvent.target.result === 'string'
                            ? loadEvent.target.result
                            : '';

                        if (mode === 'create') {
                            this.categoryCreateImagePreview = result;
                            return;
                        }

                        this.categoryEditImagePreview = result;
                    };
                    reader.readAsDataURL(file);
                },
                openCategoryDelete(action, name, count) {
                    this.categoryDeleteAction = action || '';
                    this.categoryDeleteName = name || '';
                    this.categoryDeleteCount = Number(count || 0);
                    this.categoryDeleteOpen = true;
                },
                openCategoryDeleteFromButton(event) {
                    const target = event.currentTarget;
                    this.openCategoryDelete(
                        target.dataset.deleteAction || '',
                        target.dataset.categoryName || '',
                        target.dataset.categoryCount || 0
                    );
                },
            };
        };

        const chartLabels = JSON.parse(configElement.dataset.chartLabels || '[]');
        const chartSeries = JSON.parse(configElement.dataset.chartSeries || '[]');
        const ctx = document.getElementById('ordersChart');

        if (ctx && typeof window.Chart !== 'undefined') {
            new window.Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Commandes',
                        data: chartSeries,
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
        }
    })();
</script>
@endsection
