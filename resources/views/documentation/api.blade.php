<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ ($is_rtl ?? false) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Documentation API PME') }} - {{ config('branding.platform_name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
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
            --doc-primary:#2563eb;
            --doc-primary-dark:#1d4ed8;
            --doc-secondary:#7c3aed;
            --doc-accent:#0ea5e9;
            --doc-bg:#eff6ff;
            --doc-surface:#ffffff;
            --doc-surface-soft:rgba(255,255,255,0.72);
            --doc-border:rgba(148,163,184,0.22);
            --doc-text:#0f172a;
            --doc-muted:#475569;
            --doc-shadow:0 24px 60px rgba(37,99,235,0.18);
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
        body{
            color:var(--doc-text);
            background:
                radial-gradient(circle at top left, rgba(14,165,233,.18), transparent 28%),
                radial-gradient(circle at top right, rgba(124,58,237,.16), transparent 30%),
                linear-gradient(180deg, #f8fbff 0%, var(--doc-bg) 100%);
        }
        .doc-glass{
            background:var(--doc-surface-soft);
            border:1px solid var(--doc-border);
            box-shadow:var(--doc-shadow);
            backdrop-filter:blur(16px);
        }
        .doc-card{
            background:var(--doc-surface);
            border:1px solid var(--doc-border);
            box-shadow:0 20px 45px rgba(15,23,42,0.08);
        }
        .doc-code{
            direction:ltr;
            unicode-bidi:isolate;
            text-align:left;
            white-space:pre-wrap;
            overflow-wrap:anywhere;
            background:#0f172a;
            color:#dbeafe;
            border:1px solid rgba(148,163,184,.18);
        }
        .doc-code-inline{
            direction:ltr;
            unicode-bidi:isolate;
            text-align:left;
            display:inline-block;
        }
        .doc-anchor{
            scroll-margin-top:110px;
        }
        .doc-method-get{background:rgba(14,165,233,.12); color:#0369a1; border-color:rgba(14,165,233,.22);}
        .doc-method-post{background:rgba(34,197,94,.12); color:#15803d; border-color:rgba(34,197,94,.22);}
        .doc-method-put{background:rgba(245,158,11,.12); color:#b45309; border-color:rgba(245,158,11,.24);}
        .doc-method-delete{background:rgba(244,63,94,.12); color:#be123c; border-color:rgba(244,63,94,.22);}
        [dir="rtl"] .doc-sticky-nav{
            padding-left:0;
            padding-right:1rem;
        }
    </style>
</head>
@php($platformBranding = $platform_branding ?? [])
@php($platformLogoUrl = trim((string) ($platformBranding['logo_url'] ?? '')))
@php($platformName = config('branding.platform_name'))
@php($platformInitials = config('branding.platform_initials'))
@php($publicBaseUrl = url('/api/v1'))
@php($pmeBaseUrl = url('/api/v1/pme'))
@php($navItems = [
    ['id' => 'overview', 'label' => __('Vue d\'ensemble')],
    ['id' => 'auth', 'label' => __('Authentification')],
    ['id' => 'onboarding', 'label' => __('Onboarding boutique')],
    ['id' => 'clients', 'label' => __('Clients PME')],
    ['id' => 'produits', 'label' => __('Produits PME')],
    ['id' => 'commandes', 'label' => __('Commandes PME')],
    ['id' => 'erreurs', 'label' => __('Erreurs fréquentes')],
])
@php($workflow = [
    ['icon' => 'fa-key', 'title' => __('Authentifier la boutique'), 'text' => __('Utilisez le token PME de la boutique dans l\'en-tête Authorization Bearer pour tous les endpoints protégés.')],
    ['icon' => 'fa-users', 'title' => __('Synchroniser les clients'), 'text' => __('Importez ou mettez à jour les clients abonnés depuis votre logiciel avec des appels unitaires ou en masse.')],
    ['icon' => 'fa-boxes-stacked', 'title' => __('Publier le catalogue'), 'text' => __('Envoyez le stock, les prix, les catégories et les paliers tarifaires directement depuis votre PME.')],
    ['icon' => 'fa-cart-shopping', 'title' => __('Récupérer les commandes'), 'text' => __('Lisez les commandes web non synchronisées, puis confirmez l\'import et poussez le statut logistique.')],
])
@php($clientEndpoints = [
    [
        'method' => 'GET',
        'path' => '/clients?synced=0&type_client=simple',
        'title' => __('Lister les clients'),
        'access' => __('Bearer PME requis'),
        'description' => __('Liste les clients de la boutique connectée. Filtres supportés : synced=0|1 et type_client=simple|abonne.'),
        'request' => null,
        'response' => "{\n  \"success\": true,\n  \"data\": [\n    {\n      \"id\": 15,\n      \"code_client\": \"C12536\",\n      \"nom\": \"Client 1\",\n      \"email\": \"client1@example.com\",\n      \"telephone\": \"0656232454\",\n      \"type_client\": \"simple\",\n      \"tarif\": 1,\n      \"synced_pme\": 0\n    }\n  ],\n  \"message\": \"Clients PME\"\n}",
    ],
    [
        'method' => 'POST',
        'path' => '/clients',
        'title' => __('Créer un client'),
        'access' => __('Bearer PME requis'),
        'description' => __('Crée un client abonné côté plateforme. Les valeurs par défaut utiles sont type_client=abonne, tarif=1, synced_pme=1 et actif=1.'),
        'request' => "{\n  \"code_client\": \"C12536\",\n  \"nom\": \"Client 1\",\n  \"email\": \"client1@example.com\",\n  \"password\": \"12345678\",\n  \"telephone\": \"0656232454\",\n  \"id_wilaya\": 16,\n  \"id_commune\": 1601,\n  \"tarif\": 2\n}",
        'response' => null,
    ],
    [
        'method' => 'PUT',
        'path' => '/clients/{id}',
        'title' => __('Mettre à jour un client'),
        'access' => __('Bearer PME requis'),
        'description' => __('Accepte les mises à jour partielles. Vous pouvez envoyer uniquement les champs modifiés comme code_client, téléphone ou tarif.'),
        'request' => "{\n  \"code_client\": \"55666332459659\"\n}",
        'response' => null,
    ],
    [
        'method' => 'POST',
        'path' => '/sync-clients',
        'title' => __('Synchroniser des clients en masse'),
        'access' => __('Bearer PME requis'),
        'description' => __('Recommandé pour les imports automatiques quotidiens. Le matching se fait d’abord par code_client, puis par email dans la même boutique.'),
        'request' => "{\n  \"synced\": 1,\n  \"clients\": [\n    {\n      \"code_client\": \"C12536\",\n      \"nom\": \"Client 1\",\n      \"email\": \"client1@example.com\",\n      \"telephone\": \"0656232454\",\n      \"tarif\": 2\n    }\n  ]\n}",
        'response' => "{\n  \"success\": true,\n  \"data\": {\n    \"nb_inseres\": 1,\n    \"nb_mis_a_jour\": 0\n  },\n  \"message\": \"Sync clients terminé\"\n}",
    ],
])
@php($productEndpoints = [
    [
        'method' => 'GET',
        'path' => '/produits?actif=1&search=gel&per_page=20',
        'title' => __('Lister les produits'),
        'access' => __('Bearer PME requis'),
        'description' => __('Retourne la liste paginée des produits de la boutique. Filtres supportés : synced, categorie, search, actif, abonne_only et per_page.'),
        'request' => null,
        'response' => "{\n  \"success\": true,\n  \"data\": {\n    \"items\": [\n      {\n        \"id\": 21,\n        \"reference\": \"GEL-001\",\n        \"designation\": \"Gel coiffant\",\n        \"pv_1\": 250,\n        \"pv_2\": 230,\n        \"pv_3\": 220,\n        \"stock\": 14,\n        \"categorie\": \"Cosmetique\",\n        \"abonne_only\": 0,\n        \"enable_tier_pricing\": false,\n        \"quantity_prices\": []\n      }\n    ],\n    \"pagination\": {\n      \"current_page\": 1,\n      \"last_page\": 1,\n      \"per_page\": 20,\n      \"total\": 1\n    }\n  },\n  \"message\": \"Produits PME\"\n}",
    ],
    [
        'method' => 'POST',
        'path' => '/produits',
        'title' => __('Créer ou mettre à jour un produit'),
        'access' => __('Bearer PME requis'),
        'description' => __('Le matching se fait par reference dans la boutique courante. Si le produit existe, il est mis à jour, sinon il est créé avec synced_pme=1.'),
        'request' => "{\n  \"reference\": \"GEL-001\",\n  \"designation\": \"Gel coiffant\",\n  \"description\": \"Fixation forte\",\n  \"pv_1\": 250,\n  \"pv_2\": 230,\n  \"pv_3\": 220,\n  \"stock\": 14,\n  \"categorie\": \"Cosmetique\",\n  \"abonne_only\": 0,\n  \"actif\": 1,\n  \"enable_tier_pricing\": true,\n  \"quantity_prices\": [\n    {\n      \"quantity_min\": 1,\n      \"quantity_max\": 5,\n      \"price\": 250\n    },\n    {\n      \"quantity_min\": 6,\n      \"quantity_max\": null,\n      \"price\": 210\n    }\n  ]\n}",
        'response' => null,
    ],
    [
        'method' => 'PUT',
        'path' => '/produits/{id}/sync',
        'title' => __('Marquer un produit comme synchronisé'),
        'access' => __('Bearer PME requis'),
        'description' => __('Aucun body n’est requis. Cet appel est utile après traitement ou import réussi du produit dans votre logiciel PME.'),
        'request' => null,
        'response' => "{\n  \"success\": true,\n  \"data\": {\n    \"id\": 21,\n    \"reference\": \"GEL-001\",\n    \"synced_pme\": 1\n  },\n  \"message\": \"Produit synchronise\"\n}",
    ],
    [
        'method' => 'POST',
        'path' => '/sync-produits',
        'title' => __('Synchroniser des produits en masse'),
        'access' => __('Bearer PME requis'),
        'description' => __('Permet de pousser un catalogue complet depuis votre PME. Chaque élément utilise la même logique de matching sur reference.'),
        'request' => "{\n  \"produits\": [\n    {\n      \"reference\": \"R1\",\n      \"designation\": \"Prod 1\",\n      \"pv_1\": 100,\n      \"pv_2\": 95,\n      \"pv_3\": 90,\n      \"stock\": 10,\n      \"categorie\": \"Cat\",\n      \"abonne_only\": 1,\n      \"actif\": 1\n    }\n  ]\n}",
        'response' => "{\n  \"success\": true,\n  \"data\": {\n    \"nb_inseres\": 1,\n    \"nb_mis_a_jour\": 1\n  },\n  \"message\": \"Sync produits terminé\"\n}",
    ],
])
@php($orderEndpoints = [
    [
        'method' => 'GET',
        'path' => '/commandes?synced=0',
        'title' => __('Récupérer les commandes'),
        'access' => __('Bearer PME requis'),
        'description' => __('Retourne les commandes de la boutique avec leurs lignes. Par défaut, seules les commandes non synchronisées sont renvoyées.'),
        'request' => null,
        'response' => "{\n  \"success\": true,\n  \"data\": [\n    {\n      \"id\": 101,\n      \"id_client\": 15,\n      \"nom_client\": \"Client 1\",\n      \"date_cmd\": \"2026-05-12 14:05:22\",\n      \"statut\": \"en_attente\",\n      \"montant_total\": 2800,\n      \"synced_pme\": 0,\n      \"lignes\": [\n        {\n          \"id_produit\": 1,\n          \"reference\": \"R1\",\n          \"designation\": \"Prod 1\",\n          \"quantite\": 2,\n          \"prix_unitaire\": 900,\n          \"sous_total\": 1800\n        }\n      ]\n    }\n  ],\n  \"message\": \"Commandes PME\"\n}",
    ],
    [
        'method' => 'PUT',
        'path' => '/commandes/{id}/sync',
        'title' => __('Confirmer la synchronisation d’une commande'),
        'access' => __('Bearer PME requis'),
        'description' => __('Marque la commande comme synchronisée après import réussi dans votre logiciel de gestion.'),
        'request' => null,
        'response' => "{\n  \"success\": true,\n  \"data\": {\n    \"id\": 101,\n    \"synced_pme\": 1\n  },\n  \"message\": \"Commande synchronisee\"\n}",
    ],
    [
        'method' => 'PUT',
        'path' => '/commandes/{id}/statut',
        'title' => __('Mettre à jour le statut logistique'),
        'access' => __('Bearer PME requis'),
        'description' => __('Valeurs autorisées : en_attente, expediee, livree, annulee. Le stock reste géré par le logiciel PME.'),
        'request' => "{\n  \"statut\": \"expediee\"\n}",
        'response' => null,
    ],
    [
        'method' => 'GET',
        'path' => '/commandes/export-csv?synced=0',
        'title' => __('Exporter les commandes en CSV'),
        'access' => __('Bearer PME requis'),
        'description' => __('Pratique pour les logiciels plus anciens qui importent un fichier CSV UTF-8 avec séparateur ; au lieu d’une API JSON.'),
        'request' => null,
        'response' => null,
    ],
])
@php($onboardingEndpoints = [
    [
        'method' => 'POST',
        'path' => '/fournisseurs',
        'title' => __('Créer une boutique'),
        'access' => __('Public + X-API-KEY'),
        'description' => __('Crée une nouvelle boutique depuis un système tiers. Cet endpoint exige une API key active de type create_fournisseur.'),
        'request' => "{\n  \"nom_boutique\": \"Boutique Ahmed\",\n  \"boutique_category_id\": 2,\n  \"email\": \"boutique.ahmed@gmail.com\",\n  \"telephone\": \"0550123456\",\n  \"code_wilaya\": 16,\n  \"code_commune\": 1601\n}",
        'response' => "{\n  \"success\": true,\n  \"data\": {\n    \"id\": 12,\n    \"nom_boutique\": \"Boutique Ahmed\",\n    \"password_par_defaut\": \"12345678\",\n    \"pme_token\": \"uuid-token\"\n  },\n  \"message\": \"Boutique creee\"\n}",
    ],
    [
        'method' => 'POST',
        'path' => '/fournisseurs/info',
        'title' => __('Récupérer les informations d’une boutique'),
        'access' => __('Public + X-API-KEY'),
        'description' => __('Retourne les informations de la boutique à partir de email et password. Cet endpoint est utile pour connecter automatiquement un logiciel tiers après création.'),
        'request' => "{\n  \"email\": \"boutique.ahmed@gmail.com\",\n  \"password\": \"12345678\"\n}",
        'response' => "{\n  \"success\": true,\n  \"data\": {\n    \"id\": 12,\n    \"nom_boutique\": \"Boutique Ahmed\",\n    \"telephone\": \"0550123456\",\n    \"pme_token\": \"uuid-token\",\n    \"is_visible\": 1\n  },\n  \"message\": \"Boutique trouvee\"\n}",
    ],
    [
        'method' => 'POST',
        'path' => '/sync-fournisseur',
        'title' => __('Mettre à jour la fiche boutique'),
        'access' => __('Bearer PME requis'),
        'description' => __('Met à jour le nom, le téléphone, l’adresse, la géolocalisation, la visibilité et le logo de la boutique. Utilisez multipart/form-data pour envoyer un logo.'),
        'request' => "{\n  \"telephone\": \"0550123456\",\n  \"adresse\": \"Alger\",\n  \"id_wilaya\": 16,\n  \"id_commune\": 1601,\n  \"latitude\": 36.7538,\n  \"longitude\": 3.0588,\n  \"is_visible\": 1,\n  \"remove_logo\": 0\n}",
        'response' => "{\n  \"success\": true,\n  \"data\": {\n    \"id\": 3,\n    \"nom_frs\": \"Amantek\",\n    \"telephone\": \"0550123456\",\n    \"adresse\": \"Alger\",\n    \"logo_url\": \"https://...\",\n    \"is_visible\": 1\n  },\n  \"message\": \"Sync boutique termine\"\n}",
    ],
])
@php($errors = [
    ['code' => '401', 'title' => __('Non autorisé'), 'text' => __('Token Bearer PME manquant, expiré ou invalide.')],
    ['code' => '422', 'title' => __('Validation échouée'), 'text' => __('Consultez response.errors pour identifier le champ à corriger.')],
    ['code' => '404', 'title' => __('Ressource introuvable'), 'text' => __('Identifiant incorrect ou ressource inactive/supprimée pour la boutique courante.')],
])
<body class="min-h-screen">
    <header class="sticky top-0 z-40 border-b border-white/40 bg-white/75 backdrop-blur-xl">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                @if($platformLogoUrl !== '')
                    <img src="{{ $platformLogoUrl }}" alt="{{ $platformName }}" class="h-11 w-11 rounded-2xl border border-slate-200 bg-white object-cover p-1">
                @else
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-[var(--doc-primary)] via-[var(--doc-secondary)] to-[var(--doc-accent)] font-extrabold text-white">
                        {{ $platformInitials }}
                    </div>
                @endif
                <div class="leading-tight">
                    <div class="text-base font-extrabold tracking-wide sm:text-lg">{{ $platformName }}</div>
                    <div class="text-xs font-semibold text-slate-500">{{ __('Documentation PME API') }}</div>
                </div>
            </a>

            <div class="flex items-center gap-3">
                <a href="{{ url('/admin/login') }}" class="hidden rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 sm:inline-flex">
                    {{ __('Accès admin') }}
                </a>
                @include('partials.language-switcher', ['compact' => true])
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
        <section class="doc-glass overflow-hidden rounded-[2rem] p-6 sm:p-8 lg:p-10">
            <div class="grid gap-8 lg:grid-cols-[minmax(0,1.4fr)_minmax(320px,0.8fr)] lg:items-center">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-sky-200 bg-white/80 px-3 py-1 text-xs font-extrabold uppercase tracking-[0.2em] text-sky-700">
                        <i class="fa-solid fa-plug-circle-bolt"></i>
                        {{ __('API publique pour intégration logicielle') }}
                    </div>
                    <h1 class="mt-4 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl lg:text-5xl">
                        {{ __('Connectez votre logiciel PME à :platform en toute simplicité.', ['platform' => $platformName]) }}
                    </h1>
                    <p class="mt-4 max-w-3xl text-sm leading-7 text-slate-600 sm:text-base">
                        {{ __('Cette page publique vous aide à synchroniser clients, produits, commandes et informations boutique avec la plateforme. Tout est organisé pour une intégration rapide, stable et claire, avec exemples de payloads, règles d’authentification et endpoints essentiels.') }}
                    </p>

                    <div class="mt-6 grid gap-3 sm:grid-cols-3">
                        <div class="doc-card rounded-2xl p-4">
                            <div class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">{{ __('Base URL') }}</div>
                            <div class="doc-code-inline mt-2 text-sm font-extrabold text-slate-900">{{ $pmeBaseUrl }}</div>
                        </div>
                        <div class="doc-card rounded-2xl p-4">
                            <div class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">{{ __('Auth') }}</div>
                            <div class="mt-2 text-sm font-extrabold text-slate-900">{{ __('Bearer token PME') }}</div>
                        </div>
                        <div class="doc-card rounded-2xl p-4">
                            <div class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">{{ __('Langues') }}</div>
                            <div class="mt-2 text-sm font-extrabold text-slate-900">{{ __('Français / العربية') }}</div>
                        </div>
                    </div>
                </div>

                <div class="doc-card rounded-[1.75rem] p-5 sm:p-6">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-[var(--doc-primary)] to-[var(--doc-secondary)] text-white">
                            <i class="fa-solid fa-route"></i>
                        </div>
                        <div>
                            <div class="text-lg font-extrabold text-slate-900">{{ __('Flux d’intégration recommandé') }}</div>
                            <div class="text-sm text-slate-500">{{ __('Du premier échange jusqu’à la synchronisation continue') }}</div>
                        </div>
                    </div>

                    <div class="mt-5 space-y-3">
                        @foreach($workflow as $index => $step)
                            <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white text-[var(--doc-primary)] shadow-sm">
                                        <i class="fa-solid {{ $step['icon'] }}"></i>
                                    </div>
                                    <div>
                                        <div class="text-xs font-extrabold uppercase tracking-[0.18em] text-slate-400">{{ __('Étape') }} {{ $index + 1 }}</div>
                                        <div class="mt-1 font-bold text-slate-900">{{ $step['title'] }}</div>
                                        <div class="mt-1 text-sm leading-6 text-slate-600">{{ $step['text'] }}</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <div class="mt-8 grid gap-8 lg:grid-cols-[250px_minmax(0,1fr)]">
            <aside class="doc-sticky-nav lg:sticky lg:top-28 lg:h-fit">
                <div class="doc-card rounded-[1.5rem] p-5">
                    <div class="text-xs font-extrabold uppercase tracking-[0.2em] text-slate-400">{{ __('Navigation') }}</div>
                    <div class="mt-4 space-y-2">
                        @foreach($navItems as $navItem)
                            <a href="#{{ $navItem['id'] }}" class="flex items-center justify-between rounded-2xl border border-transparent bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-200 hover:bg-white">
                                <span>{{ $navItem['label'] }}</span>
                                <i class="fa-solid fa-arrow-right-long text-xs text-slate-400 {{ ($is_rtl ?? false) ? 'fa-flip-horizontal' : '' }}"></i>
                            </a>
                        @endforeach
                    </div>
                </div>
            </aside>

            <div class="space-y-8">
                <section id="overview" class="doc-anchor doc-card rounded-[1.75rem] p-6 sm:p-8">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <div class="text-xs font-extrabold uppercase tracking-[0.2em] text-sky-600">{{ __('Vue d\'ensemble') }}</div>
                            <h2 class="mt-2 text-2xl font-black text-slate-900">{{ __('Ce que couvre la PME API') }}</h2>
                        </div>
                        <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-bold text-slate-600">/api/v1/pme</span>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded-3xl bg-gradient-to-br from-sky-500 to-blue-600 p-5 text-white">
                            <div class="text-xs font-bold uppercase tracking-[0.18em] text-white/70">{{ __('Clients PME') }}</div>
                            <div class="mt-2 text-sm leading-6 text-white/90">{{ __('Création, mise à jour partielle, suppression logique et synchronisation en masse.') }}</div>
                        </div>
                        <div class="rounded-3xl bg-gradient-to-br from-emerald-500 to-green-600 p-5 text-white">
                            <div class="text-xs font-bold uppercase tracking-[0.18em] text-white/70">{{ __('Produits PME') }}</div>
                            <div class="mt-2 text-sm leading-6 text-white/90">{{ __('Catalogue, prix, stock, paliers tarifaires et synchronisation par référence.') }}</div>
                        </div>
                        <div class="rounded-3xl bg-gradient-to-br from-amber-500 to-orange-600 p-5 text-white">
                            <div class="text-xs font-bold uppercase tracking-[0.18em] text-white/70">{{ __('Commandes PME') }}</div>
                            <div class="mt-2 text-sm leading-6 text-white/90">{{ __('Récupération des commandes web, export CSV, sync import et retour de statut logistique.') }}</div>
                        </div>
                        <div class="rounded-3xl bg-gradient-to-br from-violet-500 to-fuchsia-600 p-5 text-white">
                            <div class="text-xs font-bold uppercase tracking-[0.18em] text-white/70">{{ __('Boutique PME') }}</div>
                            <div class="mt-2 text-sm leading-6 text-white/90">{{ __('Création publique sécurisée par API key et mise à jour de la fiche boutique.') }}</div>
                        </div>
                    </div>
                </section>

                <section id="auth" class="doc-anchor doc-card rounded-[1.75rem] p-6 sm:p-8">
                    <div class="text-xs font-extrabold uppercase tracking-[0.2em] text-sky-600">{{ __('Authentification') }}</div>
                    <h2 class="mt-2 text-2xl font-black text-slate-900">{{ __('Headers et règles d’accès') }}</h2>
                    <div class="mt-6 grid gap-4 lg:grid-cols-2">
                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                            <div class="font-bold text-slate-900">{{ __('Endpoints protégés PME') }}</div>
                            <pre class="doc-code mt-4 rounded-2xl p-4 text-[12px] leading-6">Authorization: Bearer &lt;boutique_token&gt;
Accept: application/json
Content-Type: application/json</pre>
                            <p class="mt-3 text-sm leading-6 text-slate-600">{{ __('Chaque boutique voit et modifie uniquement ses propres données. Tous les endpoints clients, produits et commandes sont automatiquement filtrés par le token PME.') }}</p>
                        </div>
                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                            <div class="font-bold text-slate-900">{{ __('Endpoints publics de création boutique') }}</div>
                            <pre class="doc-code mt-4 rounded-2xl p-4 text-[12px] leading-6">X-API-KEY: &lt;create_fournisseur_api_key&gt;
Accept: application/json
Content-Type: application/json</pre>
                            <p class="mt-3 text-sm leading-6 text-slate-600">{{ __('Les endpoints publics POST /pme/fournisseurs et POST /pme/fournisseurs/info exigent une API key active de type create_fournisseur.') }}</p>
                        </div>
                    </div>
                </section>

                <section id="onboarding" class="doc-anchor doc-card rounded-[1.75rem] p-6 sm:p-8">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <div class="text-xs font-extrabold uppercase tracking-[0.2em] text-violet-600">{{ __('Onboarding boutique') }}</div>
                            <h2 class="mt-2 text-2xl font-black text-slate-900">{{ __('Démarrage public de l’intégration') }}</h2>
                        </div>
                        <span class="rounded-full border border-violet-200 bg-violet-50 px-3 py-1 text-xs font-bold text-violet-700">{{ __('Public + protégé') }}</span>
                    </div>

                    <div class="mt-6 space-y-4">
                        @foreach($onboardingEndpoints as $endpoint)
                            <article class="rounded-[1.5rem] border border-slate-200 bg-slate-50/80 p-5">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <span class="rounded-full border px-3 py-1 text-xs font-extrabold {{ $endpoint['method'] === 'POST' ? 'doc-method-post' : 'doc-method-put' }}">{{ $endpoint['method'] }}</span>
                                        <div>
                                            <div class="font-bold text-slate-900">{{ $endpoint['title'] }}</div>
                                            <div class="doc-code-inline text-sm text-slate-500">{{ $endpoint['path'] }}</div>
                                        </div>
                                    </div>
                                    <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-600">{{ $endpoint['access'] }}</span>
                                </div>
                                <p class="mt-4 text-sm leading-6 text-slate-600">{{ $endpoint['description'] }}</p>
                                @if($endpoint['request'])
                                    <div class="mt-4">
                                        <div class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-slate-400">{{ __('Exemple requête') }}</div>
                                        <pre class="doc-code rounded-2xl p-4 text-[12px] leading-6">{{ $endpoint['request'] }}</pre>
                                    </div>
                                @endif
                                @if($endpoint['response'])
                                    <div class="mt-4">
                                        <div class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-slate-400">{{ __('Exemple réponse') }}</div>
                                        <pre class="doc-code rounded-2xl p-4 text-[12px] leading-6">{{ $endpoint['response'] }}</pre>
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>

                <section id="clients" class="doc-anchor doc-card rounded-[1.75rem] p-6 sm:p-8">
                    <div class="text-xs font-extrabold uppercase tracking-[0.2em] text-sky-600">{{ __('Clients PME') }}</div>
                    <h2 class="mt-2 text-2xl font-black text-slate-900">{{ __('Synchronisation des clients') }}</h2>
                    <p class="mt-3 text-sm leading-7 text-slate-600">{{ __('Cette partie permet de lister, créer, modifier, supprimer et synchroniser les clients d’une boutique authentifiée. Le champ nom contient directement le nom complet du client.') }}</p>
                    <div class="mt-6 space-y-4">
                        @foreach($clientEndpoints as $endpoint)
                            <article class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <span class="rounded-full border px-3 py-1 text-xs font-extrabold {{ $endpoint['method'] === 'GET' ? 'doc-method-get' : ($endpoint['method'] === 'POST' ? 'doc-method-post' : 'doc-method-put') }}">{{ $endpoint['method'] }}</span>
                                        <div>
                                            <div class="font-bold text-slate-900">{{ $endpoint['title'] }}</div>
                                            <div class="doc-code-inline text-sm text-slate-500">{{ $endpoint['path'] }}</div>
                                        </div>
                                    </div>
                                    <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600">{{ $endpoint['access'] }}</span>
                                </div>
                                <p class="mt-4 text-sm leading-6 text-slate-600">{{ $endpoint['description'] }}</p>
                                @if($endpoint['request'])
                                    <div class="mt-4">
                                        <div class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-slate-400">{{ __('Exemple requête') }}</div>
                                        <pre class="doc-code rounded-2xl p-4 text-[12px] leading-6">{{ $endpoint['request'] }}</pre>
                                    </div>
                                @endif
                                @if($endpoint['response'])
                                    <div class="mt-4">
                                        <div class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-slate-400">{{ __('Exemple réponse') }}</div>
                                        <pre class="doc-code rounded-2xl p-4 text-[12px] leading-6">{{ $endpoint['response'] }}</pre>
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>

                <section id="produits" class="doc-anchor doc-card rounded-[1.75rem] p-6 sm:p-8">
                    <div class="text-xs font-extrabold uppercase tracking-[0.2em] text-emerald-600">{{ __('Produits PME') }}</div>
                    <h2 class="mt-2 text-2xl font-black text-slate-900">{{ __('Publication du catalogue et du stock') }}</h2>
                    <p class="mt-3 text-sm leading-7 text-slate-600">{{ __('Les produits sont gérés depuis votre logiciel PME. Vous pouvez envoyer les prix, les stocks, les catégories, le statut abonné et les paliers tarifaires.') }}</p>
                    <div class="mt-6 space-y-4">
                        @foreach($productEndpoints as $endpoint)
                            <article class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <span class="rounded-full border px-3 py-1 text-xs font-extrabold {{ $endpoint['method'] === 'GET' ? 'doc-method-get' : ($endpoint['method'] === 'POST' ? 'doc-method-post' : 'doc-method-put') }}">{{ $endpoint['method'] }}</span>
                                        <div>
                                            <div class="font-bold text-slate-900">{{ $endpoint['title'] }}</div>
                                            <div class="doc-code-inline text-sm text-slate-500">{{ $endpoint['path'] }}</div>
                                        </div>
                                    </div>
                                    <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600">{{ $endpoint['access'] }}</span>
                                </div>
                                <p class="mt-4 text-sm leading-6 text-slate-600">{{ $endpoint['description'] }}</p>
                                @if($endpoint['request'])
                                    <div class="mt-4">
                                        <div class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-slate-400">{{ __('Exemple requête') }}</div>
                                        <pre class="doc-code rounded-2xl p-4 text-[12px] leading-6">{{ $endpoint['request'] }}</pre>
                                    </div>
                                @endif
                                @if($endpoint['response'])
                                    <div class="mt-4">
                                        <div class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-slate-400">{{ __('Exemple réponse') }}</div>
                                        <pre class="doc-code rounded-2xl p-4 text-[12px] leading-6">{{ $endpoint['response'] }}</pre>
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>

                <section id="commandes" class="doc-anchor doc-card rounded-[1.75rem] p-6 sm:p-8">
                    <div class="text-xs font-extrabold uppercase tracking-[0.2em] text-amber-600">{{ __('Commandes PME') }}</div>
                    <h2 class="mt-2 text-2xl font-black text-slate-900">{{ __('Import des commandes et retour de statut') }}</h2>
                    <p class="mt-3 text-sm leading-7 text-slate-600">{{ __('Les commandes web sont exportées vers votre logiciel. Une fois importées, vous pouvez marquer la synchronisation puis renvoyer le statut logistique final vers la plateforme.') }}</p>
                    <div class="mt-6 space-y-4">
                        @foreach($orderEndpoints as $endpoint)
                            <article class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <span class="rounded-full border px-3 py-1 text-xs font-extrabold {{ $endpoint['method'] === 'GET' ? 'doc-method-get' : ($endpoint['method'] === 'POST' ? 'doc-method-post' : 'doc-method-put') }}">{{ $endpoint['method'] }}</span>
                                        <div>
                                            <div class="font-bold text-slate-900">{{ $endpoint['title'] }}</div>
                                            <div class="doc-code-inline text-sm text-slate-500">{{ $endpoint['path'] }}</div>
                                        </div>
                                    </div>
                                    <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600">{{ $endpoint['access'] }}</span>
                                </div>
                                <p class="mt-4 text-sm leading-6 text-slate-600">{{ $endpoint['description'] }}</p>
                                @if($endpoint['request'])
                                    <div class="mt-4">
                                        <div class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-slate-400">{{ __('Exemple requête') }}</div>
                                        <pre class="doc-code rounded-2xl p-4 text-[12px] leading-6">{{ $endpoint['request'] }}</pre>
                                    </div>
                                @endif
                                @if($endpoint['response'])
                                    <div class="mt-4">
                                        <div class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-slate-400">{{ __('Exemple réponse') }}</div>
                                        <pre class="doc-code rounded-2xl p-4 text-[12px] leading-6">{{ $endpoint['response'] }}</pre>
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>

                <section id="erreurs" class="doc-anchor doc-card rounded-[1.75rem] p-6 sm:p-8">
                    <div class="text-xs font-extrabold uppercase tracking-[0.2em] text-rose-600">{{ __('Erreurs fréquentes') }}</div>
                    <h2 class="mt-2 text-2xl font-black text-slate-900">{{ __('Réponses à surveiller pendant l’intégration') }}</h2>
                    <div class="mt-6 grid gap-4 md:grid-cols-3">
                        @foreach($errors as $error)
                            <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-5">
                                <div class="text-sm font-extrabold text-slate-400">{{ $error['code'] }}</div>
                                <div class="mt-2 text-lg font-black text-slate-900">{{ $error['title'] }}</div>
                                <p class="mt-2 text-sm leading-6 text-slate-600">{{ $error['text'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="doc-glass rounded-[1.75rem] p-6 sm:p-8">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <div class="text-xs font-extrabold uppercase tracking-[0.2em] text-sky-700">{{ __('Prêt à intégrer') }}</div>
                            <h2 class="mt-2 text-2xl font-black text-slate-900">{{ __('Commencez avec la bonne base URL et les bons headers') }}</h2>
                            <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600">{{ __('Pour les appels PME protégés, utilisez la base URL ci-dessous avec Authorization: Bearer <boutique_token>. Pour la création publique de boutique, utilisez X-API-KEY sur les endpoints prévus.') }}</p>
                        </div>
                        <div class="doc-card rounded-[1.5rem] p-5">
                            <div class="text-xs font-extrabold uppercase tracking-[0.16em] text-slate-400">{{ __('Base URL PME') }}</div>
                            <div class="doc-code-inline mt-2 text-sm font-extrabold text-slate-900">{{ $pmeBaseUrl }}</div>
                            <div class="mt-4 text-xs font-extrabold uppercase tracking-[0.16em] text-slate-400">{{ __('Base URL publique') }}</div>
                            <div class="doc-code-inline mt-2 text-sm font-extrabold text-slate-900">{{ $publicBaseUrl }}</div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </main>
</body>
</html>
