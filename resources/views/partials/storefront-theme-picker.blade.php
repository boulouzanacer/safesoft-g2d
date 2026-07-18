@php
    $themeOptions = $themeOptions ?? [];
    $selectedTheme = $selectedTheme ?? 'azure_modern';
    $inputName = $inputName ?? 'storefront_theme';
    $inputIdPrefix = $inputIdPrefix ?? 'storefront-theme';
    $surfaceClass = $surfaceClass ?? 'bg-black/20 border-white/10';
    $mutedClass = $mutedClass ?? 'text-white/60';
    $titleClass = $titleClass ?? 'text-white';
    $previewThemeClasses = [
        'azure_modern' => 'theme-preview--azure',
        'emerald_bloom' => 'theme-preview--emerald',
        'sunset_pop' => 'theme-preview--sunset',
        'violet_luxe' => 'theme-preview--violet',
        'rose_boutique' => 'theme-preview--rose',
        'graphite_pro' => 'theme-preview--graphite',
    ];
@endphp

<style>
    .theme-preview-card {
        color: #fff;
        box-shadow: 0 20px 40px rgba(15, 23, 42, 0.18);
    }
    .theme-preview-bar,
    .theme-preview-accent-box,
    .theme-preview-badge {
        transition: background-color 150ms ease, color 150ms ease, border-color 150ms ease;
    }
    .theme-preview-choice {
        transition: border-color 150ms ease, color 150ms ease;
    }
    .theme-preview--azure .theme-preview-card { background: linear-gradient(135deg, #1D4ED8, #0EA5E9); }
    .theme-preview--azure .theme-preview-bar { background: #1D4ED8; }
    .theme-preview--azure .theme-preview-accent-box { background: #DBEAFE; }
    .theme-preview--azure .theme-preview-badge { background: #DBEAFE; color: #1D4ED8; }
    .theme-preview--azure .theme-preview-choice { border-color: #93C5FD; color: #DBEAFE; }

    .theme-preview--emerald .theme-preview-card { background: linear-gradient(135deg, #059669, #34D399); }
    .theme-preview--emerald .theme-preview-bar { background: #059669; }
    .theme-preview--emerald .theme-preview-accent-box { background: #D1FAE5; }
    .theme-preview--emerald .theme-preview-badge { background: #D1FAE5; color: #047857; }
    .theme-preview--emerald .theme-preview-choice { border-color: #6EE7B7; color: #D1FAE5; }

    .theme-preview--sunset .theme-preview-card { background: linear-gradient(135deg, #EA580C, #FB7185); }
    .theme-preview--sunset .theme-preview-bar { background: #EA580C; }
    .theme-preview--sunset .theme-preview-accent-box { background: #FFE4E6; }
    .theme-preview--sunset .theme-preview-badge { background: #FFE4E6; color: #BE123C; }
    .theme-preview--sunset .theme-preview-choice { border-color: #FDBA74; color: #FFE4E6; }

    .theme-preview--violet .theme-preview-card { background: linear-gradient(135deg, #7C3AED, #A855F7); }
    .theme-preview--violet .theme-preview-bar { background: #7C3AED; }
    .theme-preview--violet .theme-preview-accent-box { background: #EDE9FE; }
    .theme-preview--violet .theme-preview-badge { background: #EDE9FE; color: #6D28D9; }
    .theme-preview--violet .theme-preview-choice { border-color: #C4B5FD; color: #EDE9FE; }

    .theme-preview--rose .theme-preview-card { background: linear-gradient(135deg, #DB2777, #FB7185); }
    .theme-preview--rose .theme-preview-bar { background: #DB2777; }
    .theme-preview--rose .theme-preview-accent-box { background: #FCE7F3; }
    .theme-preview--rose .theme-preview-badge { background: #FCE7F3; color: #BE185D; }
    .theme-preview--rose .theme-preview-choice { border-color: #F9A8D4; color: #FCE7F3; }

    .theme-preview--graphite .theme-preview-card { background: linear-gradient(135deg, #111827, #334155); }
    .theme-preview--graphite .theme-preview-bar { background: #111827; }
    .theme-preview--graphite .theme-preview-accent-box { background: #E2E8F0; }
    .theme-preview--graphite .theme-preview-badge { background: #E2E8F0; color: #0F172A; }
    .theme-preview--graphite .theme-preview-choice { border-color: #94A3B8; color: #E2E8F0; }
</style>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
    @foreach($themeOptions as $themeKey => $theme)
        @php
            $previewThemeClass = $previewThemeClasses[$themeKey] ?? 'theme-preview--azure';
        @endphp
        <label class="block cursor-pointer">
            <input type="radio"
                   name="{{ $inputName }}"
                   value="{{ $themeKey }}"
                   class="peer sr-only"
                   @checked((string) $selectedTheme === (string) $themeKey)>
            <div class="h-full rounded-3xl border {{ $surfaceClass }} p-4 transition duration-150 peer-checked:ring-2 peer-checked:scale-[1.01]">
                <div class="theme-preview-card {{ $previewThemeClass }} rounded-[1.35rem] p-4 shadow-lg">
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
                            <div class="theme-preview-bar h-2.5 w-14 rounded-full"></div>
                            <div class="mt-3 space-y-2">
                                <div class="h-2 rounded-full bg-slate-200"></div>
                                <div class="h-2 w-3/4 rounded-full bg-slate-200"></div>
                                <div class="flex gap-2">
                                    <div class="theme-preview-accent-box h-8 flex-1 rounded-xl"></div>
                                    <div class="h-8 w-10 rounded-xl bg-slate-100"></div>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="rounded-2xl border border-white/25 bg-white/10 p-3">
                                <div class="h-2 w-2/3 rounded-full bg-white/70"></div>
                                <div class="mt-2 h-10 rounded-2xl bg-white/15"></div>
                            </div>
                            <div class="theme-preview-badge rounded-2xl px-3 py-2 text-[11px] font-bold">
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
                    <span class="theme-preview-choice inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-black {{ $mutedClass }}">
                        {{ (string) $selectedTheme === (string) $themeKey ? 'Selectionne' : 'Choisir' }}
                    </span>
                </div>
            </div>
        </label>
    @endforeach
</div>
