@php
    $themeOptions = $themeOptions ?? [];
    $selectedTheme = $selectedTheme ?? \App\Models\Fournisseur::DEFAULT_STOREFRONT_THEME;
    $inputName = $inputName ?? 'storefront_theme';
    $inputIdPrefix = $inputIdPrefix ?? 'storefront-theme';
    $surfaceClass = $surfaceClass ?? 'bg-black/20 border-white/10';
    $mutedClass = $mutedClass ?? 'text-white/60';
    $titleClass = $titleClass ?? 'text-white';
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
    @foreach($themeOptions as $themeKey => $theme)
        @php
            $preview = $theme['preview'] ?? [];
            $from = $preview['from'] ?? '#1D4ED8';
            $to = $preview['to'] ?? '#0EA5E9';
            $accent = $preview['accent'] ?? '#DBEAFE';
        @endphp
        <label class="block cursor-pointer">
            <input type="radio"
                   name="{{ $inputName }}"
                   value="{{ $themeKey }}"
                   class="peer sr-only"
                   @checked((string) $selectedTheme === (string) $themeKey)>
            <div class="h-full rounded-3xl border {{ $surfaceClass }} p-4 transition duration-150 peer-checked:ring-2 peer-checked:scale-[1.01]">
                <div class="rounded-[1.35rem] p-4 text-white shadow-lg"
                     style="background: linear-gradient(135deg, {{ $from }}, {{ $to }});">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-black tracking-wide">{{ $theme['name'] ?? $themeKey }}</div>
                            <div class="mt-1 text-[11px] font-semibold text-white/85">{{ $theme['tagline'] ?? '' }}</div>
                        </div>
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-white/30 bg-white/15 text-xs font-black">
                            {{ $loop->iteration }}
                        </span>
                    </div>

                    <div class="mt-4 grid grid-cols-[1.1fr_.9fr] gap-2">
                        <div class="rounded-2xl bg-white/90 p-3 text-slate-900">
                            <div class="h-2.5 w-14 rounded-full" style="background: {{ $from }};"></div>
                            <div class="mt-3 space-y-2">
                                <div class="h-2 rounded-full bg-slate-200"></div>
                                <div class="h-2 w-3/4 rounded-full bg-slate-200"></div>
                                <div class="flex gap-2">
                                    <div class="h-8 flex-1 rounded-xl" style="background: {{ $accent }};"></div>
                                    <div class="h-8 w-10 rounded-xl bg-slate-100"></div>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="rounded-2xl border border-white/25 bg-white/10 p-3">
                                <div class="h-2 w-2/3 rounded-full bg-white/70"></div>
                                <div class="mt-2 h-10 rounded-2xl bg-white/15"></div>
                            </div>
                            <div class="rounded-2xl px-3 py-2 text-[11px] font-bold"
                                 style="background: {{ $accent }}; color: {{ $from }};">
                                Accent visuel
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-3 flex items-start justify-between gap-3">
                    <div>
                        <div class="text-sm font-bold {{ $titleClass }}">{{ $theme['name'] ?? $themeKey }}</div>
                        <div class="mt-1 text-xs {{ $mutedClass }}">{{ $theme['description'] ?? '' }}</div>
                    </div>
                    <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-black {{ $mutedClass }}"
                          style="border-color: {{ $from }}55;">
                        {{ (string) $selectedTheme === (string) $themeKey ? 'Selectionne' : 'Choisir' }}
                    </span>
                </div>
            </div>
        </label>
    @endforeach
</div>
