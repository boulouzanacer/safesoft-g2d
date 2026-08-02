@php
    $localeOptions = $locale_options ?? [];
    $currentLocale = $current_locale ?? app()->getLocale();
    $compact = $compact ?? false;
@endphp

@if(count($localeOptions) > 1)
    <div class="flex {{ $compact ? 'justify-end' : 'justify-start' }}">
        <details class="group relative">
            <summary class="flex cursor-pointer list-none items-center gap-2 rounded-full border border-slate-200 bg-white/90 px-3 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                <i class="fa-solid fa-globe text-sm text-[var(--lang-active-text,#1d4ed8)]"></i>
                <span class="{{ $compact ? 'hidden sm:inline' : 'inline' }}">{{ __('Langue') }}</span>
                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition group-open:rotate-180"></i>
            </summary>

            <div class="absolute z-50 mt-2 min-w-[180px] {{ $compact ? 'right-0' : 'left-0' }} rounded-2xl border border-slate-200 bg-white p-2 shadow-[0_18px_40px_rgba(15,23,42,0.14)]">
                <div class="mb-1 px-2 py-1 text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">
                    {{ __('Choisir la langue') }}
                </div>

                @foreach($localeOptions as $localeCode => $localeMeta)
                    <form method="POST" action="{{ url('/locale') }}">
                        @csrf
                        <input type="hidden" name="locale" value="{{ $localeCode }}">
                        <button type="submit"
                                class="flex w-full items-center justify-between gap-3 rounded-xl px-3 py-2 text-left text-xs font-bold transition {{ $currentLocale === $localeCode ? 'bg-[var(--lang-active-bg,rgba(37,99,235,0.12))] text-[var(--lang-active-text,#1d4ed8)]' : 'text-slate-700 hover:bg-slate-50' }}">
                            <span class="flex items-center gap-2">
                                <span class="text-sm leading-none">{{ $localeMeta['flag'] ?? '🏳️' }}</span>
                                <span>{{ $localeMeta['native'] ?? strtoupper($localeCode) }}</span>
                            </span>
                            @if($currentLocale === $localeCode)
                                <i class="fa-solid fa-check text-[11px]"></i>
                            @endif
                        </button>
                    </form>
                @endforeach
            </div>
        </details>
    </div>
@endif
