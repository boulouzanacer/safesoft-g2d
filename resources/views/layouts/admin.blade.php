<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="adminTheme()"
      x-init="init()"
      :class="{ 'dark': dark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Administration' }} - {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script>
        window.tailwind = window.tailwind || {};
        window.tailwind.config = {
            darkMode: 'class',
        };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root{
            --admin-primary:#1E6FD9;
            --admin-bg:#1A1A2E;
            --admin-card:#252543;
        }
        html:not(.dark){
            --admin-bg:#F8FAFC;
            --admin-card:#FFFFFF;
        }
        html:not(.dark) .text-white\/80{color:rgb(30 41 59 / 1);}
        html:not(.dark) .text-white\/70{color:rgb(71 85 105 / 1);}
        html:not(.dark) .text-white\/60{color:rgb(100 116 139 / 1);}
        html:not(.dark) .text-white\/50{color:rgb(100 116 139 / 1);}
        html:not(.dark) .border-white\/10{border-color:rgb(226 232 240 / 1);}
        html:not(.dark) .divide-white\/10 > :not([hidden]) ~ :not([hidden]){border-color:rgb(226 232 240 / 1);}
        html:not(.dark) .bg-black\/20{background-color:rgb(248 250 252 / 1);}
        html:not(.dark) .bg-black\/30{background-color:rgb(241 245 249 / 1);}
        html:not(.dark) .bg-white\/10{background-color:rgb(241 245 249 / 1);}
        html:not(.dark) .hover\:bg-white\/10:hover{background-color:rgb(241 245 249 / 1);}
        html:not(.dark) .text-red-200{color:rgb(185 28 28 / 1);}
        html:not(.dark) .text-emerald-200{color:rgb(4 120 87 / 1);}
        html:not(.dark) .text-amber-200{color:rgb(180 83 9 / 1);}
        html:not(.dark) .text-sky-200{color:rgb(3 105 161 / 1);}
        html:not(.dark) .text-violet-200{color:rgb(109 40 217 / 1);}
        html,body{font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;}
    </style>

    <script>
        function adminTheme() {
            return {
                dark: true,
                profileOpen: false,
                init() {
                    const stored = localStorage.getItem('admin_theme');
                    if (stored === 'light') this.dark = false;
                    if (stored === 'dark') this.dark = true;
                    if (!stored) this.dark = true;
                },
                toggleTheme() {
                    this.dark = !this.dark;
                    localStorage.setItem('admin_theme', this.dark ? 'dark' : 'light');
                }
            }
        }
    </script>
</head>
<body class="min-h-screen text-slate-100"
      :class="dark ? 'bg-[var(--admin-bg)]' : 'bg-slate-100 text-slate-900'">
@php($admin = \App\Models\Admin::find(session('admin_id')))
<div class="flex min-h-screen">
    <aside class="fixed inset-y-0 left-0 w-[260px] border-r bg-[var(--admin-bg)]"
           :class="dark ? 'border-white/10' : 'border-slate-200'">
        <div class="h-16 px-6 flex items-center gap-3 border-b"
             :class="dark ? 'border-white/10' : 'border-slate-200'">
            <div class="h-10 w-10 rounded-xl flex items-center justify-center font-extrabold text-white"
                 style="background: linear-gradient(135deg, var(--admin-primary), #0A3D7A);">
                G2D
            </div>
            <div class="leading-tight">
                <div class="font-extrabold tracking-wide">SafeSoft G2D</div>
                <div class="text-xs" :class="dark ? 'text-white/60' : 'text-slate-500'">Administration</div>
            </div>
        </div>

        <nav class="px-4 py-4 space-y-1 text-sm" :class="dark ? 'text-slate-100' : 'text-slate-900'">
            <a href="{{ url('/admin/dashboard') }}"
               class="flex items-center gap-3 rounded-xl px-4 py-3 border border-transparent {{ request()->is('admin/dashboard') ? 'border-[var(--admin-primary)] bg-[color:rgba(30,111,217,0.12)]' : '' }}"
               :class="dark ? 'hover:bg-white/10' : 'hover:bg-slate-100'">
                <i class="fa-solid fa-chart-line w-5 text-[var(--admin-primary)]"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ url('/admin/fournisseurs') }}"
               class="flex items-center gap-3 rounded-xl px-4 py-3 border border-transparent {{ request()->is('admin/fournisseurs*') ? 'border-[var(--admin-primary)] bg-[color:rgba(30,111,217,0.12)]' : '' }}"
               :class="dark ? 'hover:bg-white/10' : 'hover:bg-slate-100'">
                <i class="fa-solid fa-store w-5 text-[var(--admin-primary)]"></i>
                <span>Fournisseurs</span>
            </a>

            <a href="{{ url('/admin/clients') }}"
               class="flex items-center gap-3 rounded-xl px-4 py-3 border border-transparent {{ request()->is('admin/clients*') ? 'border-[var(--admin-primary)] bg-[color:rgba(30,111,217,0.12)]' : '' }}"
               :class="dark ? 'hover:bg-white/10' : 'hover:bg-slate-100'">
                <i class="fa-solid fa-users w-5 text-[var(--admin-primary)]"></i>
                <span>Clients</span>
            </a>

            <a href="{{ url('/admin/produits') }}"
               class="flex items-center gap-3 rounded-xl px-4 py-3 border border-transparent {{ request()->is('admin/produits*') ? 'border-[var(--admin-primary)] bg-[color:rgba(30,111,217,0.12)]' : '' }}"
               :class="dark ? 'hover:bg-white/10' : 'hover:bg-slate-100'">
                <i class="fa-solid fa-boxes-stacked w-5 text-[var(--admin-primary)]"></i>
                <span>Produits</span>
            </a>

            <a href="{{ url('/admin/commandes') }}"
               class="flex items-center gap-3 rounded-xl px-4 py-3 border border-transparent {{ request()->is('admin/commandes*') ? 'border-[var(--admin-primary)] bg-[color:rgba(30,111,217,0.12)]' : '' }}"
               :class="dark ? 'hover:bg-white/10' : 'hover:bg-slate-100'">
                <i class="fa-solid fa-cart-shopping w-5 text-[var(--admin-primary)]"></i>
                <span>Commandes</span>
            </a>

            <a href="{{ url('/admin/api-docs') }}"
               class="flex items-center gap-3 rounded-xl px-4 py-3 border border-transparent {{ request()->is('admin/api-docs') ? 'border-[var(--admin-primary)] bg-[color:rgba(30,111,217,0.12)]' : '' }}"
               :class="dark ? 'hover:bg-white/10' : 'hover:bg-slate-100'">
                <i class="fa-solid fa-book-open w-5 text-[var(--admin-primary)]"></i>
                <span>API Doc</span>
            </a>

            <a href="{{ url('/admin/parametres') }}"
               class="flex items-center gap-3 rounded-xl px-4 py-3 border border-transparent {{ request()->is('admin/parametres*') ? 'border-[var(--admin-primary)] bg-[color:rgba(30,111,217,0.12)]' : '' }}"
               :class="dark ? 'hover:bg-white/10' : 'hover:bg-slate-100'">
                <i class="fa-solid fa-gear w-5 text-[var(--admin-primary)]"></i>
                <span>Paramètres</span>
            </a>

            <form method="POST" action="{{ url('/admin/logout') }}" class="pt-2">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-3 rounded-xl px-4 py-3 text-left border border-transparent"
                        :class="dark ? 'hover:bg-white/10' : 'hover:bg-slate-100'">
                    <i class="fa-solid fa-right-from-bracket w-5 text-red-300"></i>
                    <span>Déconnexion</span>
                </button>
            </form>
        </nav>
    </aside>

    <div class="flex-1 ml-[260px]">
        <header class="sticky top-0 z-40 h-16 flex items-center justify-between px-6 border-b border-white/10 backdrop-blur"
                :class="dark ? 'bg-[color:rgba(26,26,46,0.85)]' : 'bg-white/80 border-slate-200'">
            <div class="font-extrabold tracking-wide text-lg">
                {{ $title ?? 'Administration' }}
            </div>

            <div class="flex items-center gap-4">
                <button type="button"
                        class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold border border-white/10 hover:bg-white/10"
                        :class="dark ? 'text-white' : 'border-slate-200 hover:bg-slate-100'"
                        @click="toggleTheme()">
                    <i class="fa-solid" :class="dark ? 'fa-sun' : 'fa-moon'"></i>
                    <span x-text="dark ? 'Clair' : 'Sombre'"></span>
                </button>

                <div class="relative" @click.outside="profileOpen = false">
                    <button type="button"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 border border-white/10 hover:bg-white/10"
                            :class="dark ? '' : 'border-slate-200 hover:bg-slate-100'"
                            @click="profileOpen = !profileOpen">
                        <div class="h-9 w-9 rounded-full flex items-center justify-center font-bold"
                             style="background: linear-gradient(135deg, var(--admin-primary), #0A3D7A);">
                            {{ strtoupper(substr($admin?->prenom ?? 'A', 0, 1)) }}{{ strtoupper(substr($admin?->nom ?? 'D', 0, 1)) }}
                        </div>
                        <div class="text-left leading-tight hidden sm:block">
                            <div class="text-sm font-bold">{{ ($admin?->prenom ?? 'Admin').' '.($admin?->nom ?? '') }}</div>
                            <div class="text-xs opacity-70">Admin</div>
                        </div>
                        <i class="fa-solid fa-chevron-down text-xs opacity-70"></i>
                    </button>

                    <div x-show="profileOpen"
                         x-transition
                         class="absolute right-0 mt-2 w-48 rounded-xl border border-white/10 shadow-2xl overflow-hidden"
                         :class="dark ? 'bg-[var(--admin-card)]' : 'bg-white border-slate-200'">
                        <a href="{{ url('/admin/profil') }}"
                           class="block px-4 py-3 text-sm hover:bg-white/10"
                           :class="dark ? '' : 'hover:bg-slate-100'">
                            Profil
                        </a>
                        <form method="POST" action="{{ url('/admin/logout') }}">
                            @csrf
                            <button type="submit"
                                    class="w-full text-left px-4 py-3 text-sm hover:bg-white/10"
                                    :class="dark ? '' : 'hover:bg-slate-100'">
                                Déconnexion
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="p-6 overflow-x-hidden">
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
