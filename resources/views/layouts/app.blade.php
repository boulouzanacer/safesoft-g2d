<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ ($is_rtl ?? false) ? 'rtl' : 'ltr' }}"
      x-data="themeSwitcher()"
      x-init="init()"
      :class="{ 'dark': dark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('branding.platform_name') }}</title>
    <script>
        window.tailwind = window.tailwind || {};
        window.tailwind.config = {
            darkMode: 'class',
        };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root{
            --font-latin:Inter,system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;
            --font-arabic:Tajawal,system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;
            --font-ui:var(--font-latin);
        }
        html:lang(ar),
        html[dir="rtl"]{
            --font-ui:var(--font-arabic);
        }
        html,body,button,input,select,textarea{
            font-family:var(--font-ui);
        }
        [dir="rtl"] .app-theme-toggle{right:auto;left:1rem;}
    </style>
    <script>
        function themeSwitcher() {
            return {
                dark: false,
                init() {
                    const stored = localStorage.getItem('theme');
                    if (stored === 'dark') {
                        this.dark = true;
                        return;
                    }
                    if (stored === 'light') {
                        this.dark = false;
                        return;
                    }
                    this.dark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                },
                toggle() {
                    this.dark = !this.dark;
                    localStorage.setItem('theme', this.dark ? 'dark' : 'light');
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
    <button type="button"
            class="app-theme-toggle fixed top-4 right-4 z-50 inline-flex items-center gap-2 rounded-xl bg-white/80 px-4 py-2 text-sm font-semibold shadow-lg backdrop-blur hover:bg-white dark:bg-slate-900/70 dark:hover:bg-slate-900"
            @click="toggle()">
        <i class="fa-solid" :class="dark ? 'fa-sun' : 'fa-moon'"></i>
        <span x-text="dark ? '{{ __('Clair') }}' : '{{ __('Sombre') }}'"></span>
    </button>

    <div class="fixed top-4 z-50 {{ ($is_rtl ?? false) ? 'right-4' : 'left-4' }}">
        @include('partials.language-switcher')
    </div>

    @yield('content')
</body>
</html>
