@extends('layouts.admin')

@section('content')
<div class="space-y-4 max-w-full overflow-x-hidden">
    <div class="rounded-2xl border border-white/10 bg-[var(--admin-card)] p-4 sm:p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <div class="text-2xl font-extrabold tracking-wide">API Documentation</div>
                <div class="mt-1 text-sm text-white/70">
                    Base URL:
                    <span class="font-mono text-white break-all">{{ url('/api/v1') }}</span>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center gap-2 rounded-full border border-emerald-400/20 bg-emerald-500/10 px-3 py-1 text-xs font-bold text-emerald-200">
                    <i class="fa-solid fa-circle-check"></i>
                    JSON
                </span>
                <span class="inline-flex items-center gap-2 rounded-full border border-amber-400/20 bg-amber-500/10 px-3 py-1 text-xs font-bold text-amber-200">
                    <i class="fa-solid fa-gauge-high"></i>
                    Throttling enabled
                </span>
                <span class="inline-flex items-center gap-2 rounded-full border border-sky-400/20 bg-sky-500/10 px-3 py-1 text-xs font-bold text-sky-200">
                    <i class="fa-solid fa-lock"></i>
                    Sanctum (mobile)
                </span>
            </div>
        </div>

        <div class="mt-5 grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="rounded-2xl border border-white/10 bg-black/20 p-4 sm:p-5">
                <div class="font-extrabold tracking-wide">Headers</div>
                <div class="mt-3 rounded-xl border border-white/10 bg-black/30 p-3 sm:p-4 font-mono text-xs leading-relaxed break-words">
                    Accept: application/json<br>
                    Content-Type: application/json<br>
                    Authorization: Bearer &lt;TOKEN&gt; (required for protected endpoints)<br>
                    Authorization: Bearer &lt;TOKEN&gt; (optional for catalog endpoints to show abonnee pricing/visibility)
                </div>
            </div>
            <div class="rounded-2xl border border-white/10 bg-black/20 p-4 sm:p-5">
                <div class="font-extrabold tracking-wide">Response format</div>
                <div class="mt-3 rounded-xl border border-white/10 bg-black/30 p-3 sm:p-4 font-mono text-xs leading-relaxed break-words">
                    {<br>
                    &nbsp;&nbsp;"success": true|false,<br>
                    &nbsp;&nbsp;"data": ... | null,<br>
                    &nbsp;&nbsp;"message": "OK" | "Validation échouée" | ...,<br>
                    &nbsp;&nbsp;"errors": ... | null<br>
                    }
                </div>
                <div class="mt-3 text-xs text-white/60">
                    Notes: <span class="font-mono text-white/80">data</span> shape depends on endpoint. For validation errors (422), check <span class="font-mono text-white/80">errors</span>.
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-4">
            <div class="rounded-2xl border border-white/10 bg-[var(--admin-card)] p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                    <div class="text-lg font-extrabold tracking-wide">Mobile API (v1)</div>
                    <span class="text-xs text-white/60">Prefix: /api/v1</span>
                </div>

                <div class="mt-4 space-y-3">
                    <details class="group rounded-2xl border border-white/10 bg-black/20 p-4 sm:p-5">
                        <summary class="cursor-pointer list-none flex items-start justify-between gap-3">
                            <div class="flex flex-wrap items-center gap-3 min-w-0">
                                <span class="inline-flex items-center rounded-lg bg-emerald-500/15 border border-emerald-400/20 px-2.5 py-1 text-xs font-extrabold text-emerald-200">AUTH</span>
                                <span class="font-bold">Authentication</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-white/60 transition group-open:rotate-180 shrink-0"></i>
                        </summary>
                        <div class="mt-4 space-y-3 text-sm text-white/80">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                        <span class="font-bold">POST /auth/register</span>
                                        <span class="text-xs text-white/60">Public</span>
                                    </div>
                                    <div class="mt-3 font-mono text-xs leading-relaxed">
                                        { "nom": "...", "prenom": "...", "email": "...", "password": "...", "telephone": "..." }<br>
                                        Optional: adresse, id_wilaya, id_commune
                                    </div>
                                    <div class="mt-2 text-xs text-white/60">Inscription client <span class="font-mono">type_client=simple</span> avec vérification email par code (6 chiffres).</div>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                        <span class="font-bold">POST /auth/login</span>
                                        <span class="text-xs text-white/60">Public</span>
                                    </div>
                                    <div class="mt-3 font-mono text-xs leading-relaxed">
                                        { "email": "...", "password": "..." }<br>
                                        Response contains: token + client
                                    </div>
                                    <div class="mt-2 text-xs text-white/60">If simple client email not verified, returns 403.</div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                        <span class="font-bold">POST /auth/verify-email</span>
                                        <span class="text-xs text-white/60">Public</span>
                                    </div>
                                    <div class="mt-3 font-mono text-xs leading-relaxed">
                                        { "email": "...", "code": "123456" }<br>
                                        Response contains: token + client
                                    </div>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                        <span class="font-bold">POST /auth/resend-email-code</span>
                                        <span class="text-xs text-white/60">Public</span>
                                    </div>
                                    <div class="mt-3 font-mono text-xs leading-relaxed">
                                        { "email": "..." }<br>
                                        Resends code (expires in 10 min)
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                        <span class="font-bold">GET /auth/me</span>
                                        <span class="text-xs text-white/60">Bearer required</span>
                                    </div>
                                    <div class="mt-3 font-mono text-xs leading-relaxed">Returns current client profile (+ fournisseur if abonnee)</div>
                                    <div class="mt-2 text-xs text-white/60">Includes: <span class="font-mono">type_client</span>, <span class="font-mono">tarif</span>, <span class="font-mono">id_frs</span>.</div>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                        <span class="font-bold">POST /auth/logout</span>
                                        <span class="text-xs text-white/60">Bearer required</span>
                                    </div>
                                    <div class="mt-3 font-mono text-xs leading-relaxed">Revokes current token</div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                        <span class="font-bold">PUT /auth/profil</span>
                                        <span class="text-xs text-white/60">Bearer required</span>
                                    </div>
                                    <div class="mt-3 font-mono text-xs leading-relaxed">
                                        { "nom": "...", "prenom": "...", "adresse": "...", "id_wilaya": 1, "id_commune": 1 }<br>
                                        Optional: telephone
                                    </div>
                                    <div class="mt-2 text-xs text-white/60">Note: <span class="font-mono">tarif</span> is managed by PME sync/admin, not by this endpoint.</div>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                        <span class="font-bold">PUT /auth/password</span>
                                        <span class="text-xs text-white/60">Bearer required</span>
                                    </div>
                                    <div class="mt-3 font-mono text-xs leading-relaxed">
                                        { "current_password": "...", "password": "...", "password_confirmation": "..." }
                                    </div>
                                </div>
                            </div>
                        </div>
                    </details>

                    <details class="group rounded-2xl border border-white/10 bg-black/20 p-4 sm:p-5">
                        <summary class="cursor-pointer list-none flex items-start justify-between gap-3">
                            <div class="flex flex-wrap items-center gap-3 min-w-0">
                                <span class="inline-flex items-center rounded-lg bg-sky-500/15 border border-sky-400/20 px-2.5 py-1 text-xs font-extrabold text-sky-200">CATALOG</span>
                                <span class="font-bold">Boutiques & Produits</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-white/60 transition group-open:rotate-180 shrink-0"></i>
                        </summary>
                        <div class="mt-4 space-y-3 text-sm text-white/80">
                            <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                <div class="font-bold">Catalog rules</div>
                                <div class="mt-2 text-xs text-white/70 leading-relaxed">
                                    - If no token (public): products show <span class="font-mono">prix=PV_1</span> and hide <span class="font-mono">abonne_only=1</span> products.<br>
                                    - If token of an abonnee: API computes <span class="font-mono">prix</span> from client <span class="font-mono">tarif</span> (1|2|3) and can return abonnee-only products.<br>
                                    - If client is abonnee with <span class="font-mono">id_frs</span>, catalog is restricted to that fournisseur (boutique + products).
                                </div>
                            </div>
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                        <span class="font-bold">GET /boutiques</span>
                                        <span class="text-xs text-white/60">Public (token optional)</span>
                                    </div>
                                    <div class="mt-3 text-xs text-white/70">
                                        Lists fournisseurs where <span class="font-mono">actif=1</span> and <span class="font-mono">is_visible=1</span> + <span class="font-mono">nb_produits</span>.
                                    </div>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                        <span class="font-bold">GET /boutiques/{id}</span>
                                        <span class="text-xs text-white/60">Public (token optional)</span>
                                    </div>
                                    <div class="mt-3 text-xs text-white/70">
                                        Single boutique detail + stats. If abonnee token with different <span class="font-mono">id_frs</span>, returns 403.
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                    <span class="font-bold">GET /produits</span>
                                    <span class="text-xs text-white/60">Public (token optional)</span>
                                </div>
                                <div class="mt-3 font-mono text-xs leading-relaxed break-words">
                                    Query params: frs_id (optional), categorie (optional), search (optional), page (optional)<br>
                                    Returns: data.items[] + data.pagination<br>
                                    Each item fields: pv_1, pv_2, pv_3, prix (computed), abonne_only, stock, images[]
                                </div>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                        <span class="font-bold">GET /produits/categories</span>
                                        <span class="text-xs text-white/60">Public (token optional)</span>
                                    </div>
                                    <div class="mt-3 font-mono text-xs leading-relaxed">Query param: frs_id (optional). If abonnee with id_frs, restricted to that boutique.</div>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                        <span class="font-bold">GET /produits/{id}</span>
                                        <span class="text-xs text-white/60">Public (token optional)</span>
                                    </div>
                                    <div class="mt-3 text-xs text-white/70">Product detail + images + pricing fields (pv_1..pv_3 + prix computed)</div>
                                </div>
                            </div>
                        </div>
                    </details>

                    <details class="group rounded-2xl border border-white/10 bg-black/20 p-4 sm:p-5">
                        <summary class="cursor-pointer list-none flex items-start justify-between gap-3">
                            <div class="flex flex-wrap items-center gap-3 min-w-0">
                                <span class="inline-flex items-center rounded-lg bg-violet-500/15 border border-violet-400/20 px-2.5 py-1 text-xs font-extrabold text-violet-200">GEO</span>
                                <span class="font-bold">Geo (Wilayas / Communes)</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-white/60 transition group-open:rotate-180 shrink-0"></i>
                        </summary>
                        <div class="mt-4 space-y-3 text-sm text-white/80">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                        <span class="font-bold">GET /wilayas</span>
                                        <span class="text-xs text-white/60">Public</span>
                                    </div>
                                    <div class="mt-3 text-xs text-white/70">Returns list of wilayas (cached)</div>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                        <span class="font-bold">GET /communes/{wilaya}</span>
                                        <span class="text-xs text-white/60">Public</span>
                                    </div>
                                    <div class="mt-3 text-xs text-white/70">Returns communes for a wilaya (cached)</div>
                                </div>
                            </div>
                        </div>
                    </details>

                    <details class="group rounded-2xl border border-white/10 bg-black/20 p-4 sm:p-5">
                        <summary class="cursor-pointer list-none flex items-start justify-between gap-3">
                            <div class="flex flex-wrap items-center gap-3 min-w-0">
                                <span class="inline-flex items-center rounded-lg bg-amber-500/15 border border-amber-400/20 px-2.5 py-1 text-xs font-extrabold text-amber-200">ORDERS</span>
                                <span class="font-bold">Commandes</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-white/60 transition group-open:rotate-180 shrink-0"></i>
                        </summary>
                        <div class="mt-4 space-y-3 text-sm text-white/80">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                        <span class="font-bold">POST /commandes</span>
                                        <span class="text-xs text-white/60">Bearer required</span>
                                    </div>
                                    <div class="mt-3 font-mono text-xs leading-relaxed break-words">
                                        {<br>
                                        &nbsp;&nbsp;"id_frs": 1,<br>
                                        &nbsp;&nbsp;"adresse_livraison": "...",<br>
                                        &nbsp;&nbsp;"id_wilaya": 1,<br>
                                        &nbsp;&nbsp;"id_commune": 1,<br>
                                        &nbsp;&nbsp;"notes": "...",<br>
                                        &nbsp;&nbsp;"panier": [{ "id_produit": 10, "quantite": 2 }]<br>
                                        }
                                    </div>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                        <span class="font-bold">GET /commandes</span>
                                        <span class="text-xs text-white/60">Bearer required</span>
                                    </div>
                                    <div class="mt-3 text-xs text-white/70">Lists current client orders</div>
                                </div>
                            </div>
                            <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                    <span class="font-bold">GET /commandes/{id}</span>
                                    <span class="text-xs text-white/60">Bearer required</span>
                                </div>
                                <div class="mt-3 text-xs text-white/70">Order detail (header + lines)</div>
                            </div>
                        </div>
                    </details>

                    <details class="group rounded-2xl border border-white/10 bg-black/20 p-4 sm:p-5">
                        <summary class="cursor-pointer list-none flex items-start justify-between gap-3">
                            <div class="flex flex-wrap items-center gap-3 min-w-0">
                                <span class="inline-flex items-center rounded-lg bg-rose-500/15 border border-rose-400/20 px-2.5 py-1 text-xs font-extrabold text-rose-200">NOTIF</span>
                                <span class="font-bold">Notifications & FCM</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-white/60 transition group-open:rotate-180 shrink-0"></i>
                        </summary>
                        <div class="mt-4 space-y-3 text-sm text-white/80">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                        <span class="font-bold">GET /notifications</span>
                                        <span class="text-xs text-white/60">Bearer required</span>
                                    </div>
                                    <div class="mt-3 text-xs text-white/70">Returns last 50 notifications + non_lues</div>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                        <span class="font-bold">PUT /notifications/{id}/lu</span>
                                        <span class="text-xs text-white/60">Bearer required</span>
                                    </div>
                                    <div class="mt-3 text-xs text-white/70">Mark one as read</div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                        <span class="font-bold">PUT /notifications/tout-lire</span>
                                        <span class="text-xs text-white/60">Bearer required</span>
                                    </div>
                                    <div class="mt-3 text-xs text-white/70">Mark all as read</div>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                        <span class="font-bold">DELETE /notifications/{id}</span>
                                        <span class="text-xs text-white/60">Bearer required</span>
                                    </div>
                                    <div class="mt-3 text-xs text-white/70">Delete notification</div>
                                </div>
                            </div>

                            <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                    <span class="font-bold">POST /fcm/token</span>
                                    <span class="text-xs text-white/60">Bearer required</span>
                                </div>
                                <div class="mt-3 font-mono text-xs leading-relaxed">
                                    { "token": "...", "device_type": "android" | "ios" }
                                </div>
                            </div>
                        </div>
                    </details>

                </div>
            </div>

            <div class="rounded-2xl border border-white/10 bg-[var(--admin-card)] p-4 sm:p-6">
                <div class="text-lg font-extrabold tracking-wide">Implementation (Flutter)</div>
                <div class="mt-2 text-sm text-white/70">
                    Use your mobile base URL (LAN/device) and always attach the Bearer token after login.
                </div>

                <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div class="rounded-2xl border border-white/10 bg-black/20 p-4 sm:p-5">
                        <div class="font-bold">1) Configure base URL</div>
                        <div class="mt-3 rounded-xl border border-white/10 bg-black/30 p-3 sm:p-4 font-mono text-xs leading-relaxed break-words">
                            DEV: API_BASE_URL = http://&lt;IP&gt;:8000/api/v1<br>
                            PROD: API_BASE_URL = https://g2d-dz.com/api/v1
                        </div>
                        <div class="mt-2 text-xs text-white/60">
                            Example: flutter run --dart-define=API_BASE_URL=http://192.168.1.104:8000/api/v1
                        </div>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-black/20 p-4 sm:p-5">
                        <div class="font-bold">2) Handle auth token</div>
                        <div class="mt-3 rounded-xl border border-white/10 bg-black/30 p-3 sm:p-4 font-mono text-xs leading-relaxed break-words">
                            Authorization: Bearer &lt;token_from_login&gt;
                        </div>
                        <div class="mt-2 text-xs text-white/60">
                            Token is returned by /auth/login and /auth/register. Pass it also to catalog endpoints to get abonnee pricing/visibility.
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-white/10 bg-[var(--admin-card)] p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                    <div class="text-lg font-extrabold tracking-wide">PME API (integration)</div>
                    <span class="text-xs text-white/60">Prefix: /api/v1/pme</span>
                </div>

                <div class="mt-3 rounded-xl border border-white/10 bg-black/20 p-4 text-sm text-white/80">
                    <div class="font-bold">Authentication</div>
                    <div class="mt-2 font-mono text-xs leading-relaxed break-all">
                        Base URL: {{ url('/api/v1/pme') }}<br>
                        Authorization: Bearer &lt;fournisseur_token&gt;<br>
                        Accept: application/json
                    </div>
                </div>

                <div class="mt-4 space-y-3 text-sm text-white/80">
                    <div class="rounded-xl border border-white/10 bg-black/20 p-4">
                        <div class="font-bold">POST /pme/sync-clients</div>
                        <div class="mt-2 text-xs text-white/70">Synchroniser des clients abonnés (tarif 1|2|3).</div>
                        <div class="mt-3 font-mono text-xs leading-relaxed break-words">
                            { "clients": [ { "code_client": "C001", "nom": "A", "prenom": "B", "email": "abonne@example.com", "password": "Pass@12345", "id_wilaya": 16, "id_commune": 1601, "tarif": 2 } ] }
                        </div>

                        <div class="mt-4" x-data="{ lang: 'python' }">
                            <div class="flex flex-wrap gap-2">
                                <button type="button" class="rounded-lg border border-white/10 px-3 py-1 text-xs font-bold"
                                        @click="lang='delphi'"
                                        :class="lang==='delphi' ? 'bg-white/10 text-white' : 'text-white/70 hover:bg-white/10'">Delphi</button>
                                <button type="button" class="rounded-lg border border-white/10 px-3 py-1 text-xs font-bold"
                                        @click="lang='python'"
                                        :class="lang==='python' ? 'bg-white/10 text-white' : 'text-white/70 hover:bg-white/10'">Python</button>
                                <button type="button" class="rounded-lg border border-white/10 px-3 py-1 text-xs font-bold"
                                        @click="lang='node'"
                                        :class="lang==='node' ? 'bg-white/10 text-white' : 'text-white/70 hover:bg-white/10'">Node.js</button>
                                <button type="button" class="rounded-lg border border-white/10 px-3 py-1 text-xs font-bold"
                                        @click="lang='php'"
                                        :class="lang==='php' ? 'bg-white/10 text-white' : 'text-white/70 hover:bg-white/10'">PHP</button>
                                <button type="button" class="rounded-lg border border-white/10 px-3 py-1 text-xs font-bold"
                                        @click="lang='dotnet'"
                                        :class="lang==='dotnet' ? 'bg-white/10 text-white' : 'text-white/70 hover:bg-white/10'">C# (.NET)</button>
                                <button type="button" class="rounded-lg border border-white/10 px-3 py-1 text-xs font-bold"
                                        @click="lang='ruby'"
                                        :class="lang==='ruby' ? 'bg-white/10 text-white' : 'text-white/70 hover:bg-white/10'">Ruby</button>
                                <button type="button" class="rounded-lg border border-white/10 px-3 py-1 text-xs font-bold"
                                        @click="lang='go'"
                                        :class="lang==='go' ? 'bg-white/10 text-white' : 'text-white/70 hover:bg-white/10'">Go</button>
                                <button type="button" class="rounded-lg border border-white/10 px-3 py-1 text-xs font-bold"
                                        @click="lang='kotlin'"
                                        :class="lang==='kotlin' ? 'bg-white/10 text-white' : 'text-white/70 hover:bg-white/10'">Kotlin</button>
                                <button type="button" class="rounded-lg border border-white/10 px-3 py-1 text-xs font-bold"
                                        @click="lang='dart'"
                                        :class="lang==='dart' ? 'bg-white/10 text-white' : 'text-white/70 hover:bg-white/10'">Dart</button>
                            </div>

                            <pre x-show="lang==='python'" class="mt-3 w-full max-w-full rounded-xl border border-white/10 bg-black/30 p-3 sm:p-4 font-mono text-[10px] sm:text-[11px] leading-relaxed whitespace-pre-wrap break-words overflow-x-hidden">import requests

url = "{{ url('/api/v1/pme/sync-clients') }}"
headers = {"Accept":"application/json","Authorization":"Bearer YOUR_FOURNISSEUR_TOKEN","Content-Type":"application/json"}
payload = {"clients":[{"code_client":"C001","nom":"A","prenom":"B","email":"abonne@example.com","password":"Pass@12345","id_wilaya":16,"id_commune":1601,"tarif":2}]}
res = requests.post(url, headers=headers, json=payload)
print(res.status_code, res.json())</pre>

                            <pre x-show="lang==='node'" class="mt-3 w-full max-w-full rounded-xl border border-white/10 bg-black/30 p-3 sm:p-4 font-mono text-[10px] sm:text-[11px] leading-relaxed whitespace-pre-wrap break-words overflow-x-hidden">const url = "{{ url('/api/v1/pme/sync-clients') }}";
const payload = { clients: [{ code_client:"C001", nom:"A", prenom:"B", email:"abonne@example.com", password:"Pass@12345", id_wilaya:16, id_commune:1601, tarif:2 }] };

const res = await fetch(url, {
  method: "POST",
  headers: {
    Accept: "application/json",
    "Content-Type": "application/json",
    Authorization: "Bearer YOUR_FOURNISSEUR_TOKEN",
  },
  body: JSON.stringify(payload),
});
console.log(res.status, await res.json());</pre>

                            <pre x-show="lang==='php'" class="mt-3 w-full max-w-full rounded-xl border border-white/10 bg-black/30 p-3 sm:p-4 font-mono text-[10px] sm:text-[11px] leading-relaxed whitespace-pre-wrap break-words overflow-x-hidden">$url = "{{ url('/api/v1/pme/sync-clients') }}";
$payload = json_encode([
  "clients" =&gt; [[
    "code_client" =&gt; "C001","nom" =&gt; "A","prenom" =&gt; "B","email" =&gt; "abonne@example.com",
    "password" =&gt; "Pass@12345","id_wilaya" =&gt; 16,"id_commune" =&gt; 1601,"tarif" =&gt; 2
  ]]
]);

$ch = curl_init($url);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER =&gt; true,
  CURLOPT_POST =&gt; true,
  CURLOPT_HTTPHEADER =&gt; [
    "Accept: application/json",
    "Content-Type: application/json",
    "Authorization: Bearer YOUR_FOURNISSEUR_TOKEN",
  ],
  CURLOPT_POSTFIELDS =&gt; $payload,
]);
$out = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo $code . PHP_EOL . $out;</pre>

                            <pre x-show="lang==='dotnet'" class="mt-3 w-full max-w-full rounded-xl border border-white/10 bg-black/30 p-3 sm:p-4 font-mono text-[10px] sm:text-[11px] leading-relaxed whitespace-pre-wrap break-words overflow-x-hidden">using System.Net.Http.Headers;
using System.Text;
using System.Text.Json;

var url = "{{ url('/api/v1/pme/sync-clients') }}";
using var http = new HttpClient();
http.DefaultRequestHeaders.Accept.Add(new MediaTypeWithQualityHeaderValue("application/json"));
http.DefaultRequestHeaders.Authorization = new AuthenticationHeaderValue("Bearer","YOUR_FOURNISSEUR_TOKEN");

var payload = new {
  clients = new[] { new { code_client="C001", nom="A", prenom="B", email="abonne@example.com", password="Pass@12345", id_wilaya=16, id_commune=1601, tarif=2 } }
};
var json = JsonSerializer.Serialize(payload);
var res = await http.PostAsync(url, new StringContent(json, Encoding.UTF8, "application/json"));
Console.WriteLine((int)res.StatusCode);
Console.WriteLine(await res.Content.ReadAsStringAsync());</pre>

                            <pre x-show="lang==='ruby'" class="mt-3 w-full max-w-full rounded-xl border border-white/10 bg-black/30 p-3 sm:p-4 font-mono text-[10px] sm:text-[11px] leading-relaxed whitespace-pre-wrap break-words overflow-x-hidden">require "net/http"
require "json"
require "uri"

uri = URI("{{ url('/api/v1/pme/sync-clients') }}")
http = Net::HTTP.new(uri.host, uri.port)
http.use_ssl = (uri.scheme == "https")

req = Net::HTTP::Post.new(uri)
req["Accept"] = "application/json"
req["Content-Type"] = "application/json"
req["Authorization"] = "Bearer YOUR_FOURNISSEUR_TOKEN"
req.body = JSON.generate({ clients: [{ code_client:"C001", nom:"A", prenom:"B", email:"abonne@example.com", password:"Pass@12345", id_wilaya:16, id_commune:1601, tarif:2 }] })

res = http.request(req)
puts res.code
puts res.body</pre>

                            <pre x-show="lang==='go'" class="mt-3 w-full max-w-full rounded-xl border border-white/10 bg-black/30 p-3 sm:p-4 font-mono text-[10px] sm:text-[11px] leading-relaxed whitespace-pre-wrap break-words overflow-x-hidden">package main

import (
  "bytes"
  "fmt"
  "net/http"
)

func main() {
  url := "{{ url('/api/v1/pme/sync-clients') }}"
  body := []byte(`{"clients":[{"code_client":"C001","nom":"A","prenom":"B","email":"abonne@example.com","password":"Pass@12345","id_wilaya":16,"id_commune":1601,"tarif":2}]}`)
  req, _ := http.NewRequest("POST", url, bytes.NewBuffer(body))
  req.Header.Set("Accept", "application/json")
  req.Header.Set("Content-Type", "application/json")
  req.Header.Set("Authorization", "Bearer YOUR_FOURNISSEUR_TOKEN")

  res, err := http.DefaultClient.Do(req)
  if err != nil { panic(err) }
  defer res.Body.Close()
  fmt.Println(res.StatusCode)
}</pre>

                            <pre x-show="lang==='kotlin'" class="mt-3 w-full max-w-full rounded-xl border border-white/10 bg-black/30 p-3 sm:p-4 font-mono text-[10px] sm:text-[11px] leading-relaxed whitespace-pre-wrap break-words overflow-x-hidden">import okhttp3.MediaType.Companion.toMediaType
import okhttp3.OkHttpClient
import okhttp3.Request
import okhttp3.RequestBody.Companion.toRequestBody

val url = "{{ url('/api/v1/pme/sync-clients') }}"
val json = """{"clients":[{"code_client":"C001","nom":"A","prenom":"B","email":"abonne@example.com","password":"Pass@12345","id_wilaya":16,"id_commune":1601,"tarif":2}]}"""
val client = OkHttpClient()
val req = Request.Builder()
  .url(url)
  .addHeader("Accept","application/json")
  .addHeader("Authorization","Bearer YOUR_FOURNISSEUR_TOKEN")
  .post(json.toRequestBody("application/json".toMediaType()))
  .build()
client.newCall(req).execute().use { res -&gt;
  println(res.code)
  println(res.body?.string())
}</pre>

                            <pre x-show="lang==='dart'" class="mt-3 w-full max-w-full rounded-xl border border-white/10 bg-black/30 p-3 sm:p-4 font-mono text-[10px] sm:text-[11px] leading-relaxed whitespace-pre-wrap break-words overflow-x-hidden">import "dart:convert";
import "package:http/http.dart" as http;

final url = Uri.parse("{{ url('/api/v1/pme/sync-clients') }}");
final payload = {
  "clients": [
    {"code_client":"C001","nom":"A","prenom":"B","email":"abonne@example.com","password":"Pass@12345","id_wilaya":16,"id_commune":1601,"tarif":2}
  ]
};

final res = await http.post(
  url,
  headers: {
    "Accept": "application/json",
    "Content-Type": "application/json",
    "Authorization": "Bearer YOUR_FOURNISSEUR_TOKEN",
  },
  body: jsonEncode(payload),
);
print(res.statusCode);
print(res.body);</pre>

                            <pre x-show="lang==='delphi'" class="mt-3 w-full max-w-full rounded-xl border border-white/10 bg-black/30 p-3 sm:p-4 font-mono text-[10px] sm:text-[11px] leading-relaxed whitespace-pre-wrap break-words overflow-x-hidden">uses
  System.SysUtils, System.Net.HttpClient, System.Net.URLClient, System.JSON;

var
  Http: THTTPClient;
  ReqBody: TStringStream;
  Resp: IHTTPResponse;
  Url: string;
  Json: string;
begin
  Url := '{{ url('/api/v1/pme/sync-clients') }}';
  Json := '{"clients":[{"code_client":"C001","nom":"A","prenom":"B","email":"abonne@example.com","password":"Pass@12345","id_wilaya":16,"id_commune":1601,"tarif":2}]}';

  Http := THTTPClient.Create;
  try
    Http.CustomHeaders['Accept'] := 'application/json';
    Http.CustomHeaders['Authorization'] := 'Bearer YOUR_FOURNISSEUR_TOKEN';
    ReqBody := TStringStream.Create(Json, TEncoding.UTF8);
    try
      Resp := Http.Post(Url, ReqBody, nil, [TNameValuePair.Create('Content-Type','application/json')]);
      Writeln(Resp.StatusCode);
      Writeln(Resp.ContentAsString(TEncoding.UTF8));
    finally
      ReqBody.Free;
    end;
  finally
    Http.Free;
  end;
end;</pre>
                        </div>
                    </div>

                    <div class="rounded-xl border border-white/10 bg-black/20 p-4">
                        <div class="font-bold">POST /pme/sync-produits</div>
                        <div class="mt-2 text-xs text-white/70">Synchroniser produits (pv_1/pv_2/pv_3 + abonne_only). Compatible: envoyer prix au lieu de pv_1.</div>
                        <div class="mt-3 font-mono text-xs leading-relaxed break-words">
                            { "produits": [ { "reference": "R1", "designation": "Prod 1", "pv_1": 100.0, "pv_2": 95.0, "pv_3": 90.0, "stock": 10, "categorie": "Cat", "abonne_only": true } ] }
                        </div>
                    </div>

                    <div class="rounded-xl border border-white/10 bg-black/20 p-4">
                        <div class="font-bold">POST /pme/sync-fournisseur</div>
                        <div class="mt-2 text-xs text-white/70">
                            Mise à jour fournisseur. Pour logo: <span class="font-mono">multipart/form-data</span>. Champs: nom_frs, telephone, adresse, id_wilaya, id_commune, latitude, longitude, is_visible, remove_logo.
                        </div>
                        <div class="mt-3 font-mono text-xs leading-relaxed break-words">
                            JSON (sans logo): { "telephone": "0550...", "is_visible": 1 }
                        </div>
                    </div>

                    <div class="rounded-xl border border-white/10 bg-black/20 p-4">
                        <div class="font-bold">GET /pme/commandes?synced=0|1</div>
                        <div class="mt-2 text-xs text-white/70">Lister commandes à synchroniser.</div>
                        <div class="mt-3 font-mono text-xs leading-relaxed break-all">{{ url('/api/v1/pme/commandes?synced=0') }}</div>
                    </div>

                    <div class="rounded-xl border border-white/10 bg-black/20 p-4">
                        <div class="font-bold">GET /pme/commandes/export-csv?synced=0|1</div>
                        <div class="mt-2 text-xs text-white/70">Télécharger CSV.</div>
                        <div class="mt-3 font-mono text-xs leading-relaxed break-all">{{ url('/api/v1/pme/commandes/export-csv?synced=0') }}</div>
                    </div>

                    <div class="rounded-xl border border-white/10 bg-black/20 p-4">
                        <div class="font-bold">PUT /pme/commandes/{id}/sync</div>
                        <div class="mt-2 text-xs text-white/70">Marquer commande synchronisée.</div>
                        <div class="mt-3 font-mono text-xs leading-relaxed break-all">{{ url('/api/v1/pme/commandes/1/sync') }}</div>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-white/10 bg-[var(--admin-card)] p-4 sm:p-6">
                <div class="text-lg font-extrabold tracking-wide">Common errors</div>
                <div class="mt-3 space-y-3 text-sm text-white/80">
                    <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                        <div class="font-bold text-rose-200">401 Non autorisé</div>
                        <div class="mt-1 text-xs text-white/70">Missing/invalid Bearer token</div>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                        <div class="font-bold text-amber-200">422 Validation échouée</div>
                        <div class="mt-1 text-xs text-white/70">Check response.errors for field messages</div>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                        <div class="font-bold text-sky-200">404 Ressource introuvable</div>
                        <div class="mt-1 text-xs text-white/70">Invalid id or inactive/deleted resource</div>
                    </div>
                </div>
            </div>
    </div>
</div>
@endsection
