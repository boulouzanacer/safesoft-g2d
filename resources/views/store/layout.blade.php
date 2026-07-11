<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Boutique' }} - {{ config('app.name') }}</title>

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root{
            --store-primary:#1E6FD9;
            --store-bg:#F8FAFC;
            --store-card:#FFFFFF;
        }
        html,body{font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;}
    </style>
</head>
<body class="min-h-screen bg-[var(--store-bg)] text-slate-900">
@php($cartCount = is_array(session('cart')) ? count(session('cart')) : 0)
<div class="min-h-screen flex flex-col">
    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/80 backdrop-blur">
        <div class="max-w-7xl mx-auto px-4 py-3 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl flex items-center justify-center font-extrabold text-white"
                     style="background: linear-gradient(135deg, var(--store-primary), #0A3D7A);">
                    G2D
                </div>
                <div class="leading-tight">
                    <div class="font-extrabold tracking-wide">SafeSoft G2D</div>
                    <div class="text-xs text-slate-500">Store</div>
                </div>
            </a>

            <nav class="hidden lg:flex items-center gap-2">
                <a href="{{ url('/boutiques') }}"
                   class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold border border-slate-200 bg-white hover:bg-slate-50">
                    <i class="fa-solid fa-store text-[var(--store-primary)]"></i>
                    <span>Boutiques</span>
                </a>
                <a href="{{ url('/produits') }}"
                   class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold border border-slate-200 bg-white hover:bg-slate-50">
                    <i class="fa-solid fa-box-open text-[var(--store-primary)]"></i>
                    <span>Produits</span>
                </a>
            </nav>

            <div class="flex items-center gap-2">
                <a href="{{ url('/panier') }}"
                   class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold border border-slate-200 bg-white hover:bg-slate-50">
                    <i class="fa-solid fa-cart-shopping text-[var(--store-primary)]"></i>
                    <span>Panier</span>
                    <span class="ml-1 inline-flex items-center justify-center min-w-[22px] h-5 px-1.5 rounded-full text-xs font-extrabold bg-slate-100 text-slate-700">
                        {{ $cartCount }}
                    </span>
                </a>

                @if(($client ?? null))
                    <a href="{{ url('/profil') }}"
                       class="inline-flex items-center justify-center h-11 w-11 rounded-xl border border-slate-200 bg-white hover:bg-slate-50"
                       title="Mon profil"
                       aria-label="Mon profil">
                        <i class="fa-solid fa-user-circle text-lg text-[var(--store-primary)]"></i>
                    </a>
                    <a href="{{ url('/mes-commandes') }}"
                       class="hidden sm:inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold border border-slate-200 bg-white hover:bg-slate-50">
                        <i class="fa-solid fa-receipt text-[var(--store-primary)]"></i>
                        <span>Mes commandes</span>
                    </a>
                    <form method="POST" action="{{ url('/logout') }}">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold border border-slate-200 bg-white hover:bg-slate-50">
                            <i class="fa-solid fa-right-from-bracket text-red-600"></i>
                            <span class="hidden sm:inline">Déconnexion</span>
                        </button>
                    </form>
                @else
                    <a href="{{ url('/login') }}"
                       class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold border border-slate-200 bg-white hover:bg-slate-50">
                        <i class="fa-solid fa-user text-[var(--store-primary)]"></i>
                        <span>Connexion</span>
                    </a>
                    <a href="{{ url('/register') }}"
                       class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-extrabold text-white"
                       style="background: linear-gradient(135deg, var(--store-primary), #0A3D7A);">
                        <i class="fa-solid fa-user-plus"></i>
                        <span class="hidden sm:inline">Créer compte</span>
                    </a>
                @endif
            </div>
        </div>
    </header>

    <main class="flex-1">
        <div class="max-w-7xl mx-auto px-4 py-6">
            @if(session('success'))
                <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('info'))
                <div class="mb-4 rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sky-800">
                    {{ session('info') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <footer class="border-t border-slate-200 bg-white">
        <div class="max-w-7xl mx-auto px-4 py-6 text-sm text-slate-500">
            © {{ date('Y') }} {{ config('app.name') }}
        </div>
    </footer>
</div>
</body>
</html>
