@php
    $localeOptions = $locale_options ?? [];
    $currentLocale = $current_locale ?? app()->getLocale();
    $compact = $compact ?? false;
@endphp

@if(count($localeOptions) > 1)
    <div class="flex items-center gap-2 flex-wrap {{ $compact ? 'justify-end' : '' }}">
        @foreach($localeOptions as $localeCode => $localeMeta)
            <form method="POST" action="{{ url('/locale') }}">
                @csrf
                <input type="hidden" name="locale" value="{{ $localeCode }}">
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-full border px-3 py-2 text-xs font-bold transition {{ $currentLocale === $localeCode ? 'border-[var(--lang-active-border,#2563eb)] bg-[var(--lang-active-bg,rgba(37,99,235,0.12))] text-[var(--lang-active-text,#1d4ed8)]' : 'border-slate-200 bg-white/90 text-slate-700 hover:bg-slate-50' }}">
                    <span class="text-sm leading-none">{{ $localeMeta['flag'] ?? '🏳️' }}</span>
                    <span>{{ $localeMeta['native'] ?? strtoupper($localeCode) }}</span>
                </button>
            </form>
        @endforeach
    </div>
@endif
