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
                id="wilayaSelect"
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
                id="communeSelect"
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

<script>
    (function () {
        const wilayaSelect = document.getElementById('wilayaSelect');
        const communeSelect = document.getElementById('communeSelect');
        if (!wilayaSelect || !communeSelect) return;

        async function loadCommunes(wilayaId) {
            communeSelect.innerHTML = '<option value="">Chargement...</option>';
            if (!wilayaId) {
                communeSelect.innerHTML = '<option value="">Choisir...</option>';
                return;
            }

            const res = await fetch('{{ url('/admin/wilayas') }}/' + wilayaId + '/communes');
            const rows = await res.json();
            const current = '{{ (int)old('id_commune', $frs?->id_commune ?? 0) }}';

            communeSelect.innerHTML = '<option value="">Choisir...</option>';
            rows.forEach(r => {
                const opt = document.createElement('option');
                opt.value = r.ID_COMMUNE;
                opt.textContent = r.COMMUNE;
                if (String(r.ID_COMMUNE) === String(current)) opt.selected = true;
                communeSelect.appendChild(opt);
            });
        }

        wilayaSelect.addEventListener('change', (e) => loadCommunes(e.target.value));

        if (wilayaSelect.value && communeSelect.options.length <= 1) {
            loadCommunes(wilayaSelect.value);
        }
    })();
</script>
