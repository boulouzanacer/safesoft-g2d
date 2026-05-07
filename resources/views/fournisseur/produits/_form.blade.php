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
        <label class="block text-sm font-semibold text-white/70 mb-1">Prix</label>
        <input name="prix"
               type="number"
               step="0.01"
               value="{{ old('prix', $produit->prix ?? '') }}"
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

    <div class="md:col-span-2 flex items-center justify-between">
        <label class="flex items-center gap-3 cursor-pointer select-none">
            <input type="checkbox"
                   name="actif"
                   value="1"
                   class="h-5 w-5 rounded border-white/20 bg-[var(--frs-card)]"
                   @checked((int)old('actif', $produit->actif ?? 1) === 1)>
            <span class="text-sm font-semibold text-white/70">Actif</span>
        </label>
        <div class="text-xs text-white/50">Max 5 images • WebP généré automatiquement</div>
    </div>
</div>

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

