@php
    $categories = $categories ?? collect();
    $selectedCategoryId = $selectedCategoryId ?? null;
    $currentUrl = $currentUrl ?? url()->current();
    $query = $query ?? '';
    $title = $title ?? 'Choisir une catégorie';
    $subtitle = $subtitle ?? 'Sélectionnez une catégorie de boutique pour filtrer les résultats.';
    $selectedCategory = $categories->first(fn ($category) => (int) $category->id === (int) $selectedCategoryId);
@endphp

@if($categories->count() > 0)
    <div class="mt-5 space-y-4">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <div class="text-sm font-extrabold tracking-wide text-slate-900">{{ $title }}</div>
                <div class="text-xs text-slate-500">{{ $subtitle }}</div>
            </div>
            <div class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600">
                <span class="font-semibold text-slate-500">Sélection :</span>
                <span class="font-bold text-slate-900">{{ $selectedCategory?->name ?? 'Toutes les catégories' }}</span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-5">
            <a href="{{ $currentUrl.'?'.http_build_query(array_filter(['q' => $query])) }}"
               class="group relative overflow-hidden rounded-3xl border p-3 transition duration-200 {{ ! $selectedCategoryId ? 'border-[var(--store-primary)] bg-blue-50 shadow-sm shadow-blue-100/80' : 'border-slate-200 bg-white hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-lg hover:shadow-slate-200/60' }}">
                <div class="flex aspect-[4/3] items-center justify-center rounded-2xl border border-dashed {{ ! $selectedCategoryId ? 'border-blue-200 bg-white text-[var(--store-primary)]' : 'border-slate-200 bg-slate-50 text-slate-400 group-hover:text-slate-600' }}">
                    <i class="fa-solid fa-grid-2 text-2xl"></i>
                </div>
                <div class="mt-3 flex items-start justify-between gap-2">
                    <div>
                        <div class="text-sm font-extrabold text-slate-900">Toutes les catégories</div>
                        <div class="mt-1 text-[11px] text-slate-500">Afficher toutes les boutiques</div>
                    </div>
                    @if(! $selectedCategoryId)
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-[var(--store-primary)] text-white shadow-sm">
                            <i class="fa-solid fa-check text-xs"></i>
                        </span>
                    @endif
                </div>
            </a>

            @foreach($categories as $category)
                <a href="{{ $currentUrl.'?'.http_build_query(array_filter(['q' => $query, 'categorie_boutique' => $category->id])) }}"
                   class="group relative overflow-hidden rounded-3xl border p-3 transition duration-200 {{ (int) $selectedCategoryId === (int) $category->id ? 'border-[var(--store-primary)] bg-blue-50 shadow-sm shadow-blue-100/80' : 'border-slate-200 bg-white hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-lg hover:shadow-slate-200/60' }}">
                    <div class="aspect-[4/3] overflow-hidden rounded-2xl bg-slate-100">
                        @if($category->image_url)
                            <img src="{{ $category->image_url }}"
                                 alt="{{ $category->name }}"
                                 class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]">
                        @else
                            <div class="flex h-full w-full items-center justify-center text-slate-400">
                                <i class="fa-solid fa-image text-2xl"></i>
                            </div>
                        @endif
                    </div>

                    <div class="mt-3 flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <div class="truncate text-sm font-extrabold text-slate-900">{{ $category->name }}</div>
                            <div class="mt-1 text-[11px] text-slate-500">Filtrer selon cette catégorie</div>
                        </div>
                        @if((int) $selectedCategoryId === (int) $category->id)
                            <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[var(--store-primary)] text-white shadow-sm">
                                <i class="fa-solid fa-check text-xs"></i>
                            </span>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endif
