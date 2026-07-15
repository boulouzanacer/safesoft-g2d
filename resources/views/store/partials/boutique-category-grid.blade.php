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

        <div class="grid grid-cols-3 gap-x-2 gap-y-4 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 xl:grid-cols-10 2xl:grid-cols-12">
            <a href="{{ $currentUrl.'?'.http_build_query(array_filter(['q' => $query])) }}"
               class="group relative flex min-w-0 flex-col items-center text-center transition duration-200">
                <div class="relative flex h-16 w-16 items-center justify-center rounded-full border bg-white shadow-sm transition duration-200 sm:h-[4.5rem] sm:w-[4.5rem] {{ ! $selectedCategoryId ? 'border-[var(--store-primary)] ring-2 ring-blue-100 text-[var(--store-primary)]' : 'border-slate-200 text-slate-400 group-hover:-translate-y-0.5 group-hover:border-slate-300 group-hover:shadow-md group-hover:text-slate-600' }}">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-50 sm:h-14 sm:w-14 {{ ! $selectedCategoryId ? 'bg-blue-50' : '' }}">
                        <i class="fa-solid fa-grid-2 text-xl"></i>
                    </span>
                    @if(! $selectedCategoryId)
                        <span class="absolute -right-1 -top-1 inline-flex h-5 w-5 items-center justify-center rounded-full bg-[var(--store-primary)] text-white shadow-sm">
                            <i class="fa-solid fa-check text-[10px]"></i>
                        </span>
                    @endif
                </div>
                <div class="mt-2 h-8 overflow-hidden text-[11px] font-semibold leading-4 text-slate-700 sm:text-xs {{ ! $selectedCategoryId ? 'text-[var(--store-primary)]' : 'group-hover:text-slate-900' }}">
                    Toutes les catégories
                </div>
            </a>

            @foreach($categories as $category)
                <a href="{{ $currentUrl.'?'.http_build_query(array_filter(['q' => $query, 'categorie_boutique' => $category->id])) }}"
                   class="group relative flex min-w-0 flex-col items-center text-center transition duration-200">
                    <div class="relative h-16 w-16 overflow-hidden rounded-full border bg-white shadow-sm transition duration-200 sm:h-[4.5rem] sm:w-[4.5rem] {{ (int) $selectedCategoryId === (int) $category->id ? 'border-[var(--store-primary)] ring-2 ring-blue-100' : 'border-slate-200 group-hover:-translate-y-0.5 group-hover:border-slate-300 group-hover:shadow-md' }}">
                        @if($category->image_url)
                            <img src="{{ $category->image_url }}"
                                 alt="{{ $category->name }}"
                                 class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.05]">
                        @else
                            <div class="flex h-full w-full items-center justify-center text-slate-400">
                                <i class="fa-solid fa-image text-2xl"></i>
                            </div>
                        @endif

                        @if((int) $selectedCategoryId === (int) $category->id)
                            <span class="absolute -right-1 -top-1 inline-flex h-5 w-5 items-center justify-center rounded-full bg-[var(--store-primary)] text-white shadow-sm">
                                <i class="fa-solid fa-check text-[10px]"></i>
                            </span>
                        @endif
                    </div>

                    <div class="mt-2 h-8 overflow-hidden text-[11px] font-semibold leading-4 text-slate-700 sm:text-xs {{ (int) $selectedCategoryId === (int) $category->id ? 'text-[var(--store-primary)]' : 'group-hover:text-slate-900' }}">
                        {{ $category->name }}
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endif
