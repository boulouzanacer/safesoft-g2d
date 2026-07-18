<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Boutique' }} - {{ config('branding.platform_name') }}</title>

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
            --store-primary:#1D4ED8;
            --store-primary-dark:#1E3A8A;
            --store-bg:#F4F8FF;
            --store-card:#FFFFFF;
            --store-card-soft:#EFF6FF;
            --store-text:#0F172A;
            --store-muted:#475569;
            --store-border:#BFDBFE;
            --store-accent:#DBEAFE;
            --store-accent-text:#1D4ED8;
            --store-hero-from:#1D4ED8;
            --store-hero-to:#0EA5E9;
            --store-button-text:#FFFFFF;
            --store-shadow:0 22px 45px rgba(37, 99, 235, 0.15);
            --store-radius-xl:1rem;
            --store-radius-2xl:1.5rem;
        }
        .store-theme--azure{
            --store-primary:#1D4ED8;
            --store-primary-dark:#1E3A8A;
            --store-bg:#F4F8FF;
            --store-card:#FFFFFF;
            --store-card-soft:#EFF6FF;
            --store-text:#0F172A;
            --store-muted:#475569;
            --store-border:#BFDBFE;
            --store-accent:#DBEAFE;
            --store-accent-text:#1D4ED8;
            --store-hero-from:#1D4ED8;
            --store-hero-to:#0EA5E9;
            --store-button-text:#FFFFFF;
            --store-shadow:0 22px 45px rgba(37, 99, 235, 0.15);
            --store-radius-xl:1rem;
            --store-radius-2xl:1.5rem;
        }
        .store-theme--emerald{
            --store-primary:#059669;
            --store-primary-dark:#065F46;
            --store-bg:#F2FBF7;
            --store-card:#FFFFFF;
            --store-card-soft:#ECFDF5;
            --store-text:#0F172A;
            --store-muted:#4B5563;
            --store-border:#A7F3D0;
            --store-accent:#D1FAE5;
            --store-accent-text:#047857;
            --store-hero-from:#047857;
            --store-hero-to:#34D399;
            --store-button-text:#FFFFFF;
            --store-shadow:0 22px 48px rgba(5, 150, 105, 0.16);
            --store-radius-xl:1rem;
            --store-radius-2xl:1.5rem;
        }
        .store-theme--sunset{
            --store-primary:#EA580C;
            --store-primary-dark:#9A3412;
            --store-bg:#FFF7ED;
            --store-card:#FFFFFF;
            --store-card-soft:#FFF1F2;
            --store-text:#431407;
            --store-muted:#7C2D12;
            --store-border:#FDBA74;
            --store-accent:#FFE4E6;
            --store-accent-text:#BE123C;
            --store-hero-from:#EA580C;
            --store-hero-to:#FB7185;
            --store-button-text:#FFFFFF;
            --store-shadow:0 24px 50px rgba(234, 88, 12, 0.16);
            --store-radius-xl:1rem;
            --store-radius-2xl:1.5rem;
        }
        .store-theme--violet{
            --store-primary:#7C3AED;
            --store-primary-dark:#4C1D95;
            --store-bg:#F8F5FF;
            --store-card:#FFFFFF;
            --store-card-soft:#F5F3FF;
            --store-text:#1F1635;
            --store-muted:#5B5570;
            --store-border:#DDD6FE;
            --store-accent:#EDE9FE;
            --store-accent-text:#6D28D9;
            --store-hero-from:#6D28D9;
            --store-hero-to:#A855F7;
            --store-button-text:#FFFFFF;
            --store-shadow:0 24px 52px rgba(124, 58, 237, 0.18);
            --store-radius-xl:1.15rem;
            --store-radius-2xl:1.75rem;
        }
        .store-theme--rose{
            --store-primary:#DB2777;
            --store-primary-dark:#9D174D;
            --store-bg:#FFF7FB;
            --store-card:#FFFFFF;
            --store-card-soft:#FDF2F8;
            --store-text:#3F1732;
            --store-muted:#6B3358;
            --store-border:#FBCFE8;
            --store-accent:#FCE7F3;
            --store-accent-text:#BE185D;
            --store-hero-from:#DB2777;
            --store-hero-to:#FB7185;
            --store-button-text:#FFFFFF;
            --store-shadow:0 24px 50px rgba(219, 39, 119, 0.16);
            --store-radius-xl:1rem;
            --store-radius-2xl:1.65rem;
        }
        .store-theme--graphite{
            --store-primary:#111827;
            --store-primary-dark:#020617;
            --store-bg:#F8FAFC;
            --store-card:#FFFFFF;
            --store-card-soft:#F1F5F9;
            --store-text:#0F172A;
            --store-muted:#475569;
            --store-border:#CBD5E1;
            --store-accent:#E2E8F0;
            --store-accent-text:#0F172A;
            --store-hero-from:#111827;
            --store-hero-to:#334155;
            --store-button-text:#FFFFFF;
            --store-shadow:0 22px 48px rgba(15, 23, 42, 0.18);
            --store-radius-xl:.95rem;
            --store-radius-2xl:1.35rem;
        }
        html,body{font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;}
        body{
            background:
                radial-gradient(circle at top left, rgba(255,255,255,.55), transparent 32%),
                radial-gradient(circle at top right, rgba(255,255,255,.35), transparent 26%),
                var(--store-bg);
            color:var(--store-text);
        }
        .store-panel{
            background:var(--store-card);
            border:1px solid var(--store-border);
            border-radius:var(--store-radius-2xl);
            box-shadow:var(--store-shadow);
        }
        .store-surface{
            background:rgba(255,255,255,.9);
            border:1px solid var(--store-border);
            border-radius:var(--store-radius-xl);
        }
        .store-soft{
            background:var(--store-card-soft);
            border:1px solid var(--store-border);
            border-radius:var(--store-radius-xl);
        }
        .store-input{
            background:#fff;
            border:1px solid var(--store-border);
            border-radius:var(--store-radius-2xl);
            color:var(--store-text);
        }
        .store-input::placeholder{
            color:var(--store-muted);
        }
        .store-input:focus{
            outline:none;
            border-color:var(--store-primary);
            box-shadow:0 0 0 3px rgba(37, 99, 235, 0.14);
        }
        .store-link{
            color:var(--store-primary);
        }
        .store-badge{
            background:var(--store-accent);
            color:var(--store-accent-text);
            border:1px solid var(--store-border);
        }
        .store-muted{
            color:var(--store-muted);
        }
        .store-button-primary{
            color:var(--store-button-text);
            background:linear-gradient(135deg, var(--store-primary), var(--store-hero-to));
            box-shadow:0 16px 32px rgba(37, 99, 235, 0.18);
        }
        .store-topbar,
        .store-footer{
            border-color:var(--store-border);
            background:rgba(255,255,255,.88);
        }
        .store-chip-active{
            border-color:var(--store-primary);
            background:var(--store-accent);
            color:var(--store-accent-text);
        }
        .store-logo-image{
            border-color:var(--store-border);
        }
        .store-logo-fallback{
            background:linear-gradient(135deg, var(--store-primary), var(--store-primary-dark));
        }
    </style>
</head>
@php($storeThemeClassMap = ['azure_modern' => 'store-theme--azure', 'emerald_bloom' => 'store-theme--emerald', 'sunset_pop' => 'store-theme--sunset', 'violet_luxe' => 'store-theme--violet', 'rose_boutique' => 'store-theme--rose', 'graphite_pro' => 'store-theme--graphite'])
@php($storeThemeClass = $storeThemeClassMap[$storefront_theme_key ?? 'azure_modern'] ?? 'store-theme--azure')
<body class="min-h-screen {{ $storeThemeClass }}">
@php($cartCount = is_array(session('cart')) ? count(session('cart')) : 0)
@php($isStorefrontMode = (bool) ($storefront_mode ?? false))
@php($storefrontHomeUrl = trim((string) ($storefront_home_url ?? '')))
@php($platformBranding = $platform_branding ?? [])
@php($logoHref = ($isStorefrontMode && $storefrontHomeUrl !== '') ? $storefrontHomeUrl : url('/'))
@php($headerBrandBoutique = $header_brand_boutique ?? ($store_theme_boutique ?? ($storefront_boutique ?? null)))
@php($headerBrandUrl = trim((string) ($header_brand_url ?? '')))
@php($brandHref = $headerBrandUrl !== '' ? $headerBrandUrl : $logoHref)
@php($platformLogoUrl = trim((string) ($platformBranding['logo_url'] ?? '')))
@php($brandLogoUrl = trim((string) ($headerBrandBoutique->logo_url ?? '')) !== '' ? trim((string) $headerBrandBoutique->logo_url) : $platformLogoUrl)
@php($brandName = trim((string) ($headerBrandBoutique->nom_frs ?? '')) !== '' ? trim((string) $headerBrandBoutique->nom_frs) : config('branding.platform_name'))
@php($brandSubtitle = trim((string) ($headerBrandBoutique->boutiqueCategory->name ?? '')) !== '' ? trim((string) $headerBrandBoutique->boutiqueCategory->name) : ($headerBrandBoutique ? 'Boutique' : 'Store'))
@php($brandInitial = strtoupper(mb_substr($brandName, 0, 1)))
<div class="min-h-screen flex flex-col">
    <header class="store-topbar sticky top-0 z-40 border-b backdrop-blur">
        <div class="max-w-7xl mx-auto px-4 py-3 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ $brandHref }}" class="flex items-center gap-3">
                @if($brandLogoUrl !== '')
                    <img src="{{ $brandLogoUrl }}"
                         alt="{{ $brandName }}"
                         class="store-logo-image h-10 w-10 rounded-xl object-cover border bg-white">
                @else
                    <div class="store-logo-fallback h-10 w-10 rounded-xl flex items-center justify-center font-extrabold text-white">
                        {{ $headerBrandBoutique ? $brandInitial : config('branding.platform_initials') }}
                    </div>
                @endif
                <div class="leading-tight">
                    <div class="font-extrabold tracking-wide">{{ $brandName }}</div>
                    <div class="store-muted text-xs">{{ $brandSubtitle }}</div>
                </div>
            </a>

            @unless($isStorefrontMode)
            <nav class="hidden lg:flex items-center gap-2">
                <a href="{{ url('/boutiques') }}"
                   class="store-surface inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold hover:opacity-95">
                    <i class="fa-solid fa-store text-[var(--store-primary)]"></i>
                    <span>Boutiques</span>
                </a>
                <a href="{{ url('/produits') }}"
                   class="store-surface inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold hover:opacity-95">
                    <i class="fa-solid fa-box-open text-[var(--store-primary)]"></i>
                    <span>Produits</span>
                </a>
            </nav>
            @endunless

            <div class="flex items-center gap-2">
                <a href="{{ url('/panier') }}"
                   class="store-surface inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold hover:opacity-95">
                    <i class="fa-solid fa-cart-shopping text-[var(--store-primary)]"></i>
                    <span>Panier</span>
                    <span class="store-soft ml-1 inline-flex items-center justify-center min-w-[22px] h-5 px-1.5 rounded-full text-xs font-extrabold">
                        {{ $cartCount }}
                    </span>
                </a>

                @if(($client ?? null))
                    @unless($isStorefrontMode)
                        <a href="{{ url('/profil') }}"
                           class="store-surface inline-flex items-center justify-center h-11 w-11 hover:opacity-95"
                           title="Mon profil"
                           aria-label="Mon profil">
                            <i class="fa-solid fa-user-circle text-lg text-[var(--store-primary)]"></i>
                        </a>
                        <a href="{{ url('/mes-commandes') }}"
                           class="store-surface hidden sm:inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold hover:opacity-95">
                            <i class="fa-solid fa-receipt text-[var(--store-primary)]"></i>
                            <span>Mes commandes</span>
                        </a>
                    @endunless
                    <form method="POST" action="{{ url('/logout') }}">
                        @csrf
                        <button type="submit"
                                class="store-surface inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold hover:opacity-95">
                            <i class="fa-solid fa-right-from-bracket text-red-600"></i>
                            <span class="hidden sm:inline">Déconnexion</span>
                        </button>
                    </form>
                @else
                    <a href="{{ url('/login') }}"
                       class="store-surface inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold hover:opacity-95">
                        <i class="fa-solid fa-user text-[var(--store-primary)]"></i>
                        <span>Connexion</span>
                    </a>
                    <a href="{{ url('/register') }}"
                       class="store-button-primary inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-extrabold">
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

    <footer class="store-footer border-t">
        <div class="store-muted max-w-7xl mx-auto px-4 py-6 text-sm">
            © {{ date('Y') }} {{ config('branding.platform_name') }}
        </div>
    </footer>
</div>
</body>
</html>
