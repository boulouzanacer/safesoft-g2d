@php
    $categories = $categories ?? collect();
    $selectedCategoryId = $selectedCategoryId ?? null;
    $currentUrl = $currentUrl ?? url()->current();
    $query = $query ?? '';
    $title = $title ?? __('Choisir une catégorie');
    $subtitle = $subtitle ?? __('Sélectionnez une catégorie de boutique pour filtrer les résultats.');
    $selectedCategory = $categories->first(fn ($category) => (int) $category->id === (int) $selectedCategoryId);
@endphp

@if($categories->count() > 0)
    @once
        <style>
            .store-category-rail{
                position:relative;
            }
            .store-category-track{
                display:flex;
                gap:.85rem;
                overflow-x:auto;
                overflow-y:hidden;
                padding:.25rem .15rem .65rem;
                scroll-behavior:smooth;
                scroll-snap-type:x proximity;
                -webkit-overflow-scrolling:touch;
                scrollbar-width:none;
                cursor:grab;
                touch-action:pan-x;
            }
            .store-category-track::-webkit-scrollbar{
                display:none;
            }
            .store-category-track.is-dragging{
                cursor:grabbing;
                scroll-behavior:auto;
            }
            .store-category-track.is-dragging a{
                pointer-events:none;
            }
            .store-category-item{
                flex:0 0 auto;
                width:5.5rem;
                scroll-snap-align:start;
            }
            .store-category-fade{
                position:absolute;
                top:0;
                bottom:.65rem;
                width:3.75rem;
                z-index:1;
                pointer-events:none;
                opacity:0;
                transition:opacity .2s ease;
            }
            .store-category-rail.is-start-hidden .store-category-fade--left,
            .store-category-rail.is-end-hidden .store-category-fade--right{
                opacity:1;
            }
            .store-category-fade--left{
                left:0;
                background:linear-gradient(90deg, var(--store-bg) 18%, rgba(255,255,255,0));
            }
            .store-category-fade--right{
                right:0;
                background:linear-gradient(270deg, var(--store-bg) 18%, rgba(255,255,255,0));
            }
            .store-category-arrow{
                position:absolute;
                top:1.15rem;
                z-index:2;
                display:inline-flex;
                align-items:center;
                justify-content:center;
                width:2.5rem;
                height:2.5rem;
                border:none;
                border-radius:9999px;
                background:rgba(255,255,255,.96);
                color:var(--store-primary);
                box-shadow:0 16px 30px rgba(15,23,42,.12);
                transition:opacity .2s ease, transform .2s ease, box-shadow .2s ease;
            }
            .store-category-arrow:hover{
                transform:translateY(-1px);
                box-shadow:0 18px 34px rgba(15,23,42,.16);
            }
            .store-category-arrow:disabled{
                opacity:0;
                pointer-events:none;
                transform:scale(.92);
            }
            .store-category-arrow--left{
                left:.15rem;
                animation:store-category-nudge-left 1.5s ease-in-out infinite alternate;
            }
            .store-category-arrow--right{
                right:.15rem;
                animation:store-category-nudge-right 1.5s ease-in-out infinite alternate;
            }
            [dir="rtl"] .store-category-arrow--left{
                left:auto;
                right:.15rem;
            }
            [dir="rtl"] .store-category-arrow--right{
                right:auto;
                left:.15rem;
            }
            [dir="rtl"] .store-category-fade--left{
                left:auto;
                right:0;
                background:linear-gradient(270deg, var(--store-bg) 18%, rgba(255,255,255,0));
            }
            [dir="rtl"] .store-category-fade--right{
                right:auto;
                left:0;
                background:linear-gradient(90deg, var(--store-bg) 18%, rgba(255,255,255,0));
            }
            .store-category-helper{
                display:inline-flex;
                align-items:center;
                gap:.45rem;
                font-size:.75rem;
                font-weight:700;
                color:var(--store-primary);
            }
            .store-category-helper i{
                font-size:.7rem;
                animation:store-category-blink 1.35s ease-in-out infinite;
            }
            @keyframes store-category-nudge-left{
                from{transform:translateX(0);}
                to{transform:translateX(-4px);}
            }
            @keyframes store-category-nudge-right{
                from{transform:translateX(0);}
                to{transform:translateX(4px);}
            }
            @keyframes store-category-blink{
                0%,100%{opacity:.55;}
                50%{opacity:1;}
            }
        </style>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('[data-category-rail]').forEach(function (rail) {
                    var track = rail.querySelector('[data-category-track]');
                    var prev = rail.querySelector('[data-category-prev]');
                    var next = rail.querySelector('[data-category-next]');
                    var isRtl = rail.dataset.isRtl === '1';
                    var directionFactor = isRtl ? -1 : 1;

                    if (!track || !prev || !next) {
                        return;
                    }

                    var updateState = function () {
                        var maxScroll = Math.max(track.scrollWidth - track.clientWidth, 0);
                        var left = track.scrollLeft;
                        var canGoPrev = left > 8;
                        var canGoNext = left < maxScroll - 8;

                        prev.disabled = !canGoPrev;
                        next.disabled = !canGoNext;
                        rail.classList.toggle('is-start-hidden', canGoPrev);
                        rail.classList.toggle('is-end-hidden', canGoNext);
                    };

                    var getStep = function () {
                        return Math.max(Math.round(track.clientWidth * 0.78), 220);
                    };

                    var startX = 0;
                    var startScrollLeft = 0;
                    var isPointerDown = false;
                    var isDragging = false;
                    var hasDragged = false;

                    var stopDragging = function () {
                        if (!isPointerDown && !isDragging) {
                            return;
                        }

                        isPointerDown = false;
                        isDragging = false;
                        track.classList.remove('is-dragging');
                    };

                    prev.addEventListener('click', function () {
                        track.scrollBy({ left: -getStep() * directionFactor, behavior: 'smooth' });
                    });

                    next.addEventListener('click', function () {
                        track.scrollBy({ left: getStep() * directionFactor, behavior: 'smooth' });
                    });

                    track.addEventListener('mousedown', function (event) {
                        if (event.button !== 0) {
                            return;
                        }

                        startX = event.clientX;
                        startScrollLeft = track.scrollLeft;
                        isPointerDown = true;
                        isDragging = false;
                        hasDragged = false;
                    });

                    window.addEventListener('mousemove', function (event) {
                        if (!isPointerDown) {
                            return;
                        }

                        var deltaX = event.clientX - startX;
                        if (Math.abs(deltaX) > 6) {
                            hasDragged = true;
                        }

                        if (hasDragged) {
                            if (!isDragging) {
                                isDragging = true;
                                track.classList.add('is-dragging');
                            }

                            event.preventDefault();
                            track.scrollLeft = startScrollLeft - deltaX;
                        }
                    });

                    window.addEventListener('mouseup', stopDragging);
                    track.addEventListener('mouseleave', function () {
                        if (isDragging) {
                            stopDragging();
                        }
                    });
                    track.addEventListener('dragstart', function (event) {
                        event.preventDefault();
                    });

                    track.addEventListener('click', function (event) {
                        if (!hasDragged) {
                            return;
                        }

                        event.preventDefault();
                        event.stopPropagation();
                        hasDragged = false;
                    }, true);

                    track.addEventListener('scroll', updateState, { passive: true });
                    window.addEventListener('resize', updateState);
                    updateState();
                });
            });
        </script>
    @endonce

    <div class="mt-5 space-y-4">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <div class="text-sm font-extrabold tracking-wide text-[color:var(--store-text)]">{{ __($title) }}</div>
                <div class="store-muted text-xs">{{ __($subtitle) }}</div>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-2 sm:justify-end">
                <span class="store-category-helper">
                    <i class="fa-solid fa-left-right"></i>
                    <span>{{ __('Glissez ou utilisez les flèches') }}</span>
                </span>
                <div class="store-soft inline-flex items-center gap-2 rounded-2xl px-3 py-2 text-xs">
                    <span class="store-muted font-semibold">{{ __('Sélection :') }}</span>
                    <span class="font-bold text-[color:var(--store-text)]">{{ $selectedCategory?->name ?? __('Toutes les catégories') }}</span>
                </div>
            </div>
        </div>

        <div class="store-category-rail" data-category-rail data-is-rtl="{{ ($is_rtl ?? false) ? '1' : '0' }}">
            <div class="store-category-fade store-category-fade--left"></div>
            <div class="store-category-fade store-category-fade--right"></div>

            <button type="button"
                    class="store-category-arrow store-category-arrow--left"
                    data-category-prev
                    aria-label="{{ __('Voir les catégories précédentes') }}">
                <i class="fa-solid {{ ($is_rtl ?? false) ? 'fa-chevron-right' : 'fa-chevron-left' }}"></i>
            </button>

            <button type="button"
                    class="store-category-arrow store-category-arrow--right"
                    data-category-next
                    aria-label="{{ __('Voir les catégories suivantes') }}">
                <i class="fa-solid {{ ($is_rtl ?? false) ? 'fa-chevron-left' : 'fa-chevron-right' }}"></i>
            </button>

            <div class="store-category-track" data-category-track>
                <a href="{{ $currentUrl.'?'.http_build_query(array_filter(['q' => $query])) }}"
                   class="store-category-item group relative flex min-w-0 flex-col items-center text-center transition duration-200">
                    <div class="relative flex h-16 w-16 items-center justify-center rounded-full border bg-white shadow-sm transition duration-200 sm:h-[4.5rem] sm:w-[4.5rem] {{ ! $selectedCategoryId ? 'border-[var(--store-primary)] ring-2 ring-blue-100 text-[var(--store-primary)]' : 'border-[color:var(--store-border)] text-slate-400 group-hover:-translate-y-0.5 group-hover:shadow-md group-hover:text-slate-600' }}">
                        <span class="flex h-12 w-12 items-center justify-center rounded-full sm:h-14 sm:w-14 {{ ! $selectedCategoryId ? 'bg-[var(--store-accent)]' : 'bg-[var(--store-card-soft)]' }}">
                            <i class="fa-solid fa-grid-2 text-xl"></i>
                        </span>
                        @if(! $selectedCategoryId)
                            <span class="absolute -right-1 -top-1 inline-flex h-5 w-5 items-center justify-center rounded-full bg-[var(--store-primary)] text-white shadow-sm">
                                <i class="fa-solid fa-check text-[10px]"></i>
                            </span>
                        @endif
                    </div>
                    <div class="mt-2 h-8 overflow-hidden text-[11px] font-semibold leading-4 sm:text-xs {{ ! $selectedCategoryId ? 'text-[var(--store-primary)]' : 'text-[color:var(--store-text)] group-hover:text-slate-900' }}">
                        {{ __('Toutes les catégories') }}
                    </div>
                </a>

                @foreach($categories as $category)
                    <a href="{{ $currentUrl.'?'.http_build_query(array_filter(['q' => $query, 'categorie_boutique' => $category->id])) }}"
                       class="store-category-item group relative flex min-w-0 flex-col items-center text-center transition duration-200">
                        <div class="relative h-16 w-16 overflow-hidden rounded-full border bg-white shadow-sm transition duration-200 sm:h-[4.5rem] sm:w-[4.5rem] {{ (int) $selectedCategoryId === (int) $category->id ? 'border-[var(--store-primary)] ring-2 ring-blue-100' : 'border-[color:var(--store-border)] group-hover:-translate-y-0.5 group-hover:shadow-md' }}">
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

                        <div class="mt-2 h-8 overflow-hidden text-[11px] font-semibold leading-4 sm:text-xs {{ (int) $selectedCategoryId === (int) $category->id ? 'text-[var(--store-primary)]' : 'text-[color:var(--store-text)] group-hover:text-slate-900' }}">
                            {{ $category->name }}
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@endif
