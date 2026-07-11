@php
    $frs = $frs ?? null;
    $formPrefix = $formPrefix ?? 'frs';
    $wilayaSelectId = $formPrefix.'_wilayaSelect';
    $communeSelectId = $formPrefix.'_communeSelect';
    $selectedCommuneId = (int) old('id_commune', $frs?->id_commune ?? 0);
@endphp

@csrf

@if($errors->any())
    <div class="rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-red-200 mb-4">
        <ul class="list-disc pl-5 space-y-1 text-sm">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(session('success'))
    <div class="rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-emerald-200 mb-4">
        {{ session('success') }}
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-semibold text-white/70 mb-1">Nom</label>
        <input name="nom_frs"
               value="{{ old('nom_frs', $frs?->nom_frs ?? '') }}"
               class="w-full rounded-2xl border border-white/10 bg-[var(--admin-card)] px-4 py-3 outline-none focus:border-[var(--admin-primary)]"
               required>
    </div>

    <div>
        <label class="block text-sm font-semibold text-white/70 mb-1">Catégorie boutique</label>
        <select name="boutique_category_id"
                class="w-full rounded-2xl border border-white/10 bg-[var(--admin-card)] px-4 py-3 outline-none focus:border-[var(--admin-primary)]"
                required>
            <option value="">Choisir...</option>
            @foreach(($boutique_categories ?? collect()) as $category)
                <option value="{{ $category->id }}"
                        @selected((int) old('boutique_category_id', $frs?->boutique_category_id ?? 0) === (int) $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-semibold text-white/70 mb-1">Email</label>
        <input type="email"
               name="email"
               value="{{ old('email', $frs?->email ?? '') }}"
               class="w-full rounded-2xl border border-white/10 bg-[var(--admin-card)] px-4 py-3 outline-none focus:border-[var(--admin-primary)]"
               required>
    </div>

    @if(!isset($isEdit) || !$isEdit)
        <div>
            <label class="block text-sm font-semibold text-white/70 mb-1">Password</label>
            <input type="password"
                   name="password"
                   class="w-full rounded-2xl border border-white/10 bg-[var(--admin-card)] px-4 py-3 outline-none focus:border-[var(--admin-primary)]"
                   required>
        </div>
    @else
        <div>
            <label class="block text-sm font-semibold text-white/70 mb-1">Nouveau Password (optionnel)</label>
            <input type="password"
                   name="password"
                   class="w-full rounded-2xl border border-white/10 bg-[var(--admin-card)] px-4 py-3 outline-none focus:border-[var(--admin-primary)]"
                   placeholder="Laisser vide pour ne pas changer">
        </div>
    @endif

    <div>
        <label class="block text-sm font-semibold text-white/70 mb-1">Téléphone</label>
        <input name="telephone"
               value="{{ old('telephone', $frs?->telephone ?? '') }}"
               class="w-full rounded-2xl border border-white/10 bg-[var(--admin-card)] px-4 py-3 outline-none focus:border-[var(--admin-primary)]">
    </div>

    <div>
        <label class="block text-sm font-semibold text-white/70 mb-1">Date expiration</label>
        <input type="date"
               name="expires_at"
               value="{{ old('expires_at', $frs?->expires_at?->format('Y-m-d')) }}"
               class="w-full rounded-2xl border border-white/10 bg-[var(--admin-card)] px-4 py-3 outline-none focus:border-[var(--admin-primary)]"
               required>
        <div class="mt-1 text-xs text-white/50">L admin peut prolonger cette date plus tard pour reactiver l acces.</div>
    </div>

    <div>
        <label class="block text-sm font-semibold text-white/70 mb-1">Logo (optionnel)</label>
        <input type="file"
               name="logo"
               accept="image/png,image/jpeg,image/webp"
               class="w-full rounded-2xl border border-white/10 bg-[var(--admin-card)] px-4 py-3 outline-none focus:border-[var(--admin-primary)]">
        @if(($frs?->logo_path ?? null))
            <div class="mt-2 flex items-center gap-3">
                <img src="{{ \Illuminate\Support\Facades\Storage::url($frs->logo_path) }}"
                     alt=""
                     class="h-12 w-12 rounded-xl object-cover border border-white/10">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox"
                           name="remove_logo"
                           value="1"
                           class="h-5 w-5 rounded border-white/20 bg-[var(--admin-card)]">
                    <span class="text-sm font-semibold text-white/70">Supprimer</span>
                </label>
            </div>
        @endif
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-white/70 mb-1">Adresse</label>
        <textarea name="adresse"
                  rows="3"
                  class="w-full rounded-2xl border border-white/10 bg-[var(--admin-card)] px-4 py-3 outline-none focus:border-[var(--admin-primary)]"
                  required>{{ old('adresse', $frs?->adresse ?? '') }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-semibold text-white/70 mb-1">Latitude (optionnel)</label>
        <input name="latitude"
               value="{{ old('latitude', $frs?->latitude ?? '') }}"
               inputmode="decimal"
               class="w-full rounded-2xl border border-white/10 bg-[var(--admin-card)] px-4 py-3 outline-none focus:border-[var(--admin-primary)]">
    </div>

    <div>
        <label class="block text-sm font-semibold text-white/70 mb-1">Longitude (optionnel)</label>
        <input name="longitude"
               value="{{ old('longitude', $frs?->longitude ?? '') }}"
               inputmode="decimal"
               class="w-full rounded-2xl border border-white/10 bg-[var(--admin-card)] px-4 py-3 outline-none focus:border-[var(--admin-primary)]">
    </div>

    <div>
        <label class="block text-sm font-semibold text-white/70 mb-1">Wilaya</label>
        <select name="id_wilaya"
                id="{{ $wilayaSelectId }}"
                class="w-full rounded-2xl border border-white/10 bg-[var(--admin-card)] px-4 py-3 outline-none focus:border-[var(--admin-primary)]"
                required>
            <option value="">Choisir...</option>
            @foreach($wilayas as $w)
                <option value="{{ $w->ID_WILAYA }}"
                        @selected((int)old('id_wilaya', $frs?->id_wilaya ?? 0) === (int)$w->ID_WILAYA)>
                    {{ $w->ID_WILAYA }} - {{ $w->WILAYA }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-semibold text-white/70 mb-1">Commune</label>
        <select name="id_commune"
                id="{{ $communeSelectId }}"
                class="w-full rounded-2xl border border-white/10 bg-[var(--admin-card)] px-4 py-3 outline-none focus:border-[var(--admin-primary)]"
                required>
            <option value="">Choisir...</option>
            @foreach($communes as $c)
                <option value="{{ $c->ID_COMMUNE }}"
                        @selected((int)old('id_commune', $frs?->id_commune ?? 0) === (int)$c->ID_COMMUNE)>
                    {{ $c->COMMUNE }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="md:col-span-2 flex items-center justify-between">
        <label class="flex items-center gap-3 cursor-pointer select-none">
            <input type="checkbox"
                   name="actif"
                   value="1"
                   class="h-5 w-5 rounded border-white/20 bg-[var(--admin-card)]"
                   @checked((int)old('actif', $frs?->actif ?? 1) === 1)>
            <span class="text-sm font-semibold text-white/70">Actif</span>
        </label>
    </div>
</div>

<div class="hidden js-fournisseur-location-config"
     data-wilaya-select-id="{{ $wilayaSelectId }}"
     data-commune-select-id="{{ $communeSelectId }}"
     data-selected-commune-id="{{ $selectedCommuneId }}"
     data-base-url="{{ url('/admin/wilayas') }}"></div>

<script>
    window.initFournisseurLocationForm = window.initFournisseurLocationForm || function (config) {
        const wilayaSelect = document.getElementById(config.wilayaSelectId);
        const communeSelect = document.getElementById(config.communeSelectId);

        if (!wilayaSelect || !communeSelect || wilayaSelect.dataset.locationInit === '1') {
            return;
        }

        wilayaSelect.dataset.locationInit = '1';

        async function loadCommunes(wilayaId, selectedCommuneId) {
            communeSelect.innerHTML = '<option value="">Chargement...</option>';

            if (!wilayaId) {
                communeSelect.innerHTML = '<option value="">Choisir...</option>';
                return;
            }

            const response = await fetch(config.baseUrl + '/' + wilayaId + '/communes', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });

            const rows = await response.json();
            communeSelect.innerHTML = '<option value="">Choisir...</option>';

            rows.forEach(function (row) {
                const option = document.createElement('option');
                option.value = row.ID_COMMUNE;
                option.textContent = row.COMMUNE;

                if (String(row.ID_COMMUNE) === String(selectedCommuneId || '')) {
                    option.selected = true;
                }

                communeSelect.appendChild(option);
            });
        }

        wilayaSelect.addEventListener('change', function (event) {
            loadCommunes(event.target.value, null);
        });

        if (wilayaSelect.value && communeSelect.options.length <= 1) {
            loadCommunes(wilayaSelect.value, config.selectedCommuneId);
        }
    };

    (function () {
        const configElement = document.currentScript.previousElementSibling;
        if (!configElement || !configElement.classList.contains('js-fournisseur-location-config')) {
            return;
        }

        window.initFournisseurLocationForm({
            wilayaSelectId: configElement.dataset.wilayaSelectId || '',
            communeSelectId: configElement.dataset.communeSelectId || '',
            selectedCommuneId: configElement.dataset.selectedCommuneId || '',
            baseUrl: configElement.dataset.baseUrl || '',
        });
    })();
</script>
