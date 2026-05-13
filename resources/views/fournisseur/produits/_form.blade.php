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

<input type="hidden" name="__produit_id" value="{{ $produit->id ?? '' }}">

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-semibold text-white/70 mb-1">Référence</label>
        <input name="reference"
               value="{{ old('reference', $produit->reference ?? '') }}"
               class="w-full rounded-2xl border border-white/10 bg-[var(--frs-card)] px-4 py-3 outline-none focus:border-[var(--frs-primary)]"
               required>
    </div>

    <div>
        <label class="block text-sm font-semibold text-white/70 mb-1">Désignation</label>
        <input name="designation"
               value="{{ old('designation', $produit->designation ?? '') }}"
               class="w-full rounded-2xl border border-white/10 bg-[var(--frs-card)] px-4 py-3 outline-none focus:border-[var(--frs-primary)]"
               required>
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-white/70 mb-1">Description</label>
        <textarea name="description"
                  rows="5"
                  class="w-full rounded-2xl border border-white/10 bg-[var(--frs-card)] px-4 py-3 outline-none focus:border-[var(--frs-primary)]"
                  required>{{ old('description', $produit->description ?? '') }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-semibold text-white/70 mb-1">PV 1</label>
        <input name="pv_1"
               type="number"
               step="0.01"
               value="{{ old('pv_1', $produit->pv_1 ?? '') }}"
               class="w-full rounded-2xl border border-white/10 bg-[var(--frs-card)] px-4 py-3 outline-none focus:border-[var(--frs-primary)]"
               required>
    </div>

    <div>
        <label class="block text-sm font-semibold text-white/70 mb-1">PV 2</label>
        <input name="pv_2"
               type="number"
               step="0.01"
               value="{{ old('pv_2', $produit->pv_2 ?? '') }}"
               class="w-full rounded-2xl border border-white/10 bg-[var(--frs-card)] px-4 py-3 outline-none focus:border-[var(--frs-primary)]"
               required>
    </div>

    <div>
        <label class="block text-sm font-semibold text-white/70 mb-1">PV 3</label>
        <input name="pv_3"
               type="number"
               step="0.01"
               value="{{ old('pv_3', $produit->pv_3 ?? '') }}"
               class="w-full rounded-2xl border border-white/10 bg-[var(--frs-card)] px-4 py-3 outline-none focus:border-[var(--frs-primary)]"
               required>
    </div>

    <div>
        <label class="block text-sm font-semibold text-white/70 mb-1">Stock</label>
        <input name="stock"
               type="number"
               min="0"
               value="{{ old('stock', $produit->stock ?? 0) }}"
               class="w-full rounded-2xl border border-white/10 bg-[var(--frs-card)] px-4 py-3 outline-none focus:border-[var(--frs-primary)]"
               required>
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-white/70 mb-1">Catégorie</label>
        <input name="categorie"
               value="{{ old('categorie', $produit->categorie ?? '') }}"
               class="w-full rounded-2xl border border-white/10 bg-[var(--frs-card)] px-4 py-3 outline-none focus:border-[var(--frs-primary)]"
               required>
    </div>

    @php
        $actifDefault = (int) old('actif', $produit->actif ?? 1) === 1;
        $abonneOnlyDefault = (int) old('abonne_only', $produit->abonne_only ?? 0) === 1;
    @endphp

    <div class="md:col-span-2"
         x-data="{ actif: @json($actifDefault), abonneOnly: @json($abonneOnlyDefault) }">
        <div class="flex items-center justify-between">
            <label class="flex items-center gap-3 cursor-pointer select-none">
                <input type="checkbox"
                       name="actif"
                       value="1"
                       class="h-5 w-5 rounded border-white/20 bg-[var(--frs-card)]"
                       @checked($actifDefault)
                       x-model="actif">
                <span class="text-sm font-semibold text-white/70">Actif</span>
            </label>
            <div class="text-xs text-white/50">Max 5 images • WebP généré automatiquement</div>
        </div>

        <div class="mt-3"
             :class="!actif ? 'opacity-50' : ''">
            <label class="flex items-center gap-3 cursor-pointer select-none"
                   :class="!actif ? 'cursor-not-allowed' : ''">
                <input type="hidden" name="abonne_only" :value="abonneOnly ? 1 : 0">
                <input type="checkbox"
                       name="abonne_only"
                       value="1"
                       class="h-5 w-5 rounded border-white/20 bg-[var(--frs-card)]"
                       @checked($abonneOnlyDefault)
                       x-model="abonneOnly"
                       :disabled="!actif">
                <span class="text-sm font-semibold text-white/70">Visible uniquement pour abonnés</span>
            </label>
        </div>
    </div>
</div>

@php
    $oldProduitId = old('__produit_id');
    $useOldTier = $oldProduitId !== null && (string) $oldProduitId !== '' && (string) $oldProduitId === (string) ($produit->id ?? '');

    $tierOldEnabled = $useOldTier ? old('enable_tier_pricing') : null;
    $tierEnabled = $tierOldEnabled !== null
        ? ((int) $tierOldEnabled === 1)
        : ((bool) ($produit?->enable_tier_pricing ?? false));

    $tierOld = $useOldTier ? old('quantity_prices') : null;
    $tierDefaults = [];
    if (is_array($tierOld)) {
        $tierDefaults = $tierOld;
    } elseif (isset($produit) && $produit) {
        $tierDefaults = $produit->quantityPrices()
            ->get(['quantity_min', 'quantity_max', 'price'])
            ->map(fn ($t) => ['quantity_min' => (int) $t->quantity_min, 'quantity_max' => $t->quantity_max === null ? null : (int) $t->quantity_max, 'price' => (float) $t->price])
            ->values()
            ->all();
    }

    if ($tierOldEnabled === null && $tierEnabled === false && count($tierDefaults) > 0) {
        $tierEnabled = true;
    }
@endphp

<div class="mt-6 rounded-2xl border border-white/10 bg-black/20 p-4"
     x-data="window.__tierPricingForm({ enabled: @json($tierEnabled), tiers: @json($tierDefaults) })">
    <div class="flex items-center justify-between gap-3">
        <label class="flex items-center gap-3 cursor-pointer select-none">
            <input type="checkbox"
                   name="enable_tier_pricing"
                   value="1"
                   class="sr-only peer"
                   @checked($tierEnabled)
                   x-model="enabled">
            <span class="h-5 w-5 rounded border border-white/20 bg-[var(--frs-card)] flex items-center justify-center peer-checked:bg-[var(--frs-primary)] peer-checked:border-[var(--frs-primary)]">
                <i class="fa-solid fa-check text-white text-[10px] opacity-0 peer-checked:opacity-100"></i>
            </span>
            <span class="text-sm font-extrabold text-white/80">Prix par palier</span>
        </label>
        <div class="text-xs text-white/50">Illimité • Sans chevauchement • Ignore le tarif client si activé</div>
    </div>

    <div class="mt-4" x-show="enabled" style="{{ $tierEnabled ? '' : 'display:none;' }}">
        <div class="grid grid-cols-12 gap-2 text-xs font-bold text-white/60">
            <div class="col-span-3">Qté min</div>
            <div class="col-span-3">Qté max</div>
            <div class="col-span-4">Prix (DA)</div>
            <div class="col-span-2 text-right">Actions</div>
        </div>

        <div class="mt-2 space-y-2">
            <template x-for="(row, i) in tiers" :key="i">
                <div class="grid grid-cols-12 gap-2 items-center">
                    <div class="col-span-3">
                        <input type="number"
                               min="1"
                               class="w-full rounded-xl border border-white/10 bg-[var(--frs-card)] px-3 py-2 outline-none focus:border-[var(--frs-primary)]"
                               x-model.number="row.quantity_min"
                               :name="`quantity_prices[${i}][quantity_min]`"
                               :disabled="!enabled"
                               @input="validate()">
                    </div>
                    <div class="col-span-3">
                        <input type="number"
                               min="1"
                               placeholder="∞"
                               class="w-full rounded-xl border border-white/10 bg-[var(--frs-card)] px-3 py-2 outline-none focus:border-[var(--frs-primary)]"
                               x-model="row.quantity_max"
                               :name="`quantity_prices[${i}][quantity_max]`"
                               :disabled="!enabled"
                               @input="validate()">
                    </div>
                    <div class="col-span-4">
                        <input type="number"
                               min="0"
                               step="0.01"
                               class="w-full rounded-xl border border-white/10 bg-[var(--frs-card)] px-3 py-2 outline-none focus:border-[var(--frs-primary)]"
                               x-model.number="row.price"
                               :name="`quantity_prices[${i}][price]`"
                               :disabled="!enabled"
                               @input="validate()">
                    </div>
                    <div class="col-span-2 flex justify-end">
                        <button type="button"
                                class="h-9 w-9 rounded-xl border border-white/10 bg-[var(--frs-card)] hover:bg-white/5 flex items-center justify-center"
                                @click="remove(i)">
                            <i class="fa-solid fa-trash text-white/80"></i>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <div class="mt-3 flex items-center justify-between gap-3">
            <button type="button"
                    class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-extrabold border border-white/10 bg-[var(--frs-card)] hover:bg-white/5"
                    @click="add()">
                <i class="fa-solid fa-plus"></i>
                Ajouter un palier
            </button>
            <div class="text-xs text-red-300" x-text="error" x-show="error"></div>
        </div>
    </div>
</div>

<script>
    window.__tierPricingForm = function (init) {
        const safeBool = (v) => v === true || v === 1 || v === '1';
        const safeNum = (v) => (v === null || v === undefined || v === '') ? null : Number(v);

        const tiers = Array.isArray(init?.tiers) ? init.tiers.map((t) => ({
            quantity_min: Number(t.quantity_min ?? 1),
            quantity_max: (t.quantity_max === null || t.quantity_max === '') ? null : Number(t.quantity_max),
            price: Number(t.price ?? 0),
        })) : [];

        return {
            enabled: safeBool(init?.enabled),
            tiers: tiers.length ? tiers : [{ quantity_min: 1, quantity_max: null, price: 0 }],
            error: '',

            add() {
                const last = this.tiers[this.tiers.length - 1];
                const lastMax = safeNum(last?.quantity_max);
                if (lastMax === null) {
                    this.error = 'Définissez une quantité max pour le dernier palier avant d’en ajouter un autre.';
                    return;
                }
                this.tiers.push({ quantity_min: lastMax + 1, quantity_max: null, price: 0 });
                this.validate();
            },

            remove(i) {
                this.tiers.splice(i, 1);
                if (this.tiers.length === 0) {
                    this.tiers.push({ quantity_min: 1, quantity_max: null, price: 0 });
                }
                this.validate();
            },

            validate() {
                this.error = '';
                if (!this.enabled) return true;

                const rows = this.tiers.map((r) => ({
                    quantity_min: Number(r.quantity_min ?? 0),
                    quantity_max: (r.quantity_max === null || r.quantity_max === '') ? null : Number(r.quantity_max),
                    price: Number(r.price ?? -1),
                }));

                for (const r of rows) {
                    if (!Number.isFinite(r.quantity_min) || r.quantity_min < 1) {
                        this.error = 'Quantité min invalide.';
                        return false;
                    }
                    if (r.quantity_max !== null && (!Number.isFinite(r.quantity_max) || r.quantity_max < r.quantity_min)) {
                        this.error = 'Quantité max doit être >= quantité min.';
                        return false;
                    }
                    if (!Number.isFinite(r.price) || r.price < 0) {
                        this.error = 'Prix invalide.';
                        return false;
                    }
                }

                rows.sort((a, b) => a.quantity_min - b.quantity_min);
                for (let i = 1; i < rows.length; i++) {
                    const prev = rows[i - 1];
                    const cur = rows[i];
                    if (prev.quantity_max === null) {
                        this.error = 'Aucun palier ne peut suivre un palier sans quantité max.';
                        return false;
                    }
                    if (cur.quantity_min <= prev.quantity_max) {
                        this.error = 'Chevauchement détecté entre paliers.';
                        return false;
                    }
                }

                return true;
            },

            init() {
                this.validate();
            },
        }
    }
</script>

<div class="mt-6">
    <div class="flex items-center justify-between">
        <div class="font-extrabold tracking-wide">Images</div>
        <div class="text-xs text-white/50">Glisser-déposer pour l’ordre • ⭐ pour image principale</div>
    </div>

    <div class="mt-3 rounded-2xl border border-white/10 bg-black/20 p-4">
        <input id="imagesInput" type="file" name="images[]" multiple accept="image/*" class="block w-full text-sm text-white/80">
        <div class="mt-3 text-xs text-white/50">Formats: jpg, png, webp • 5MB max par image</div>
    </div>

    <input type="hidden" name="primary_image" id="primaryImageInput" value="{{ old('primary_image', '') }}">
    <div id="orderInputs"></div>

    <div id="imageList" class="mt-4 grid grid-cols-2 md:grid-cols-5 gap-3">
        @foreach($images as $img)
            <div class="relative rounded-2xl border border-white/10 bg-[var(--frs-card)] overflow-hidden group"
                 data-key="existing:{{ $img->id }}"
                 data-existing="1">
                <img src="{{ $img->url_thumbnail }}" class="h-28 w-full object-cover" alt="">

                <button type="button"
                        class="absolute top-2 left-2 h-9 w-9 rounded-xl bg-black/50 text-white/90 hover:bg-black/70 flex items-center justify-center"
                        onclick="window.__setPrimary('existing:{{ $img->id }}')"
                        title="Définir comme principale">
                    <i class="fa-solid fa-star"></i>
                </button>

                <button type="button"
                        class="absolute top-2 right-2 h-9 w-9 rounded-xl bg-black/50 text-white/90 hover:bg-black/70 flex items-center justify-center"
                        onclick="window.__markDeleteExisting(this, {{ $img->id }})"
                        title="Supprimer">
                    <i class="fa-solid fa-trash"></i>
                </button>

                <div class="absolute bottom-2 left-2 right-2 text-[10px] text-white/70 truncate bg-black/40 rounded-lg px-2 py-1">
                    {{ $img->filename }}
                </div>
            </div>
        @endforeach
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
<script>
    (function () {
        const imagesInput = document.getElementById('imagesInput');
        const imageList = document.getElementById('imageList');
        const orderInputs = document.getElementById('orderInputs');
        const primaryInput = document.getElementById('primaryImageInput');

        function rebuildOrderInputs() {
            orderInputs.innerHTML = '';
            const items = imageList.querySelectorAll('[data-key]');
            items.forEach(el => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'images_order[]';
                input.value = el.getAttribute('data-key');
                orderInputs.appendChild(input);
            });
        }

        window.__setPrimary = function (key) {
            primaryInput.value = key;
            imageList.querySelectorAll('[data-key]').forEach(el => {
                el.classList.remove('ring-2', 'ring-[var(--frs-primary)]');
            });
            const el = imageList.querySelector(`[data-key="${CSS.escape(key)}"]`);
            if (el) {
                el.classList.add('ring-2', 'ring-[var(--frs-primary)]');
            }
        }

        window.__markDeleteExisting = function (btn, id) {
            const card = btn.closest('[data-existing="1"]');
            if (!card) return;
            card.style.display = 'none';
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'delete_images[]';
            input.value = id;
            orderInputs.appendChild(input);
            rebuildOrderInputs();
        }

        function fileToKey(index) {
            return 'new:' + index;
        }

        function syncFileList(files) {
            const dt = new DataTransfer();
            files.forEach(f => dt.items.add(f));
            imagesInput.files = dt.files;
        }

        function renderNewPreviews() {
            const files = Array.from(imagesInput.files || []);
            const existingNew = imageList.querySelectorAll('[data-key^="new:"]');
            existingNew.forEach(el => el.remove());

            files.forEach((file, idx) => {
                const key = fileToKey(idx);
                const card = document.createElement('div');
                card.className = 'relative rounded-2xl border border-white/10 bg-[var(--frs-card)] overflow-hidden group';
                card.setAttribute('data-key', key);

                const img = document.createElement('img');
                img.className = 'h-28 w-full object-cover';
                img.src = URL.createObjectURL(file);
                card.appendChild(img);

                const star = document.createElement('button');
                star.type = 'button';
                star.className = 'absolute top-2 left-2 h-9 w-9 rounded-xl bg-black/50 text-white/90 hover:bg-black/70 flex items-center justify-center';
                star.title = 'Définir comme principale';
                star.innerHTML = '<i class="fa-solid fa-star"></i>';
                star.addEventListener('click', () => window.__setPrimary(key));
                card.appendChild(star);

                const del = document.createElement('button');
                del.type = 'button';
                del.className = 'absolute top-2 right-2 h-9 w-9 rounded-xl bg-black/50 text-white/90 hover:bg-black/70 flex items-center justify-center';
                del.title = 'Supprimer';
                del.innerHTML = '<i class="fa-solid fa-xmark"></i>';
                del.addEventListener('click', () => {
                    const next = files.filter((_, i) => i !== idx);
                    syncFileList(next);
                    renderNewPreviews();
                });
                card.appendChild(del);

                const label = document.createElement('div');
                label.className = 'absolute bottom-2 left-2 right-2 text-[10px] text-white/70 truncate bg-black/40 rounded-lg px-2 py-1';
                label.textContent = file.name;
                card.appendChild(label);

                imageList.appendChild(card);
            });

            rebuildOrderInputs();
        }

        Sortable.create(imageList, {
            animation: 150,
            onSort: () => rebuildOrderInputs()
        });

        imagesInput.addEventListener('change', () => {
            const files = Array.from(imagesInput.files || []);
            if (files.length > 5) {
                syncFileList(files.slice(0, 5));
            }
            renderNewPreviews();
        });

        document.addEventListener('submit', (e) => {
            if (e.target && e.target.matches('form')) rebuildOrderInputs();
        });

        const initialPrimary = '{{ old('primary_image') }}';
        if (initialPrimary) window.__setPrimary(initialPrimary);
    })();
</script>
