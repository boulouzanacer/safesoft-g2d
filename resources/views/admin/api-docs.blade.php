@extends('layouts.admin')

@section('content')
<div class="space-y-4">
    <div class="rounded-2xl border border-white/10 bg-[var(--admin-card)] p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <div class="text-2xl font-extrabold tracking-wide">API Documentation</div>
                <div class="mt-1 text-sm text-white/70">
                    Base URL:
                    <span class="font-mono text-white">{{ url('/api/v1') }}</span>
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
            <div class="rounded-2xl border border-white/10 bg-black/20 p-5">
                <div class="font-extrabold tracking-wide">Headers</div>
                <div class="mt-3 rounded-xl border border-white/10 bg-black/30 p-4 font-mono text-xs leading-relaxed overflow-x-auto">
                    Accept: application/json<br>
                    Content-Type: application/json<br>
                    Authorization: Bearer &lt;TOKEN&gt; (required for protected endpoints)
                </div>
            </div>
            <div class="rounded-2xl border border-white/10 bg-black/20 p-5">
                <div class="font-extrabold tracking-wide">Response format</div>
                <div class="mt-3 rounded-xl border border-white/10 bg-black/30 p-4 font-mono text-xs leading-relaxed overflow-x-auto">
                    {<br>
                    &nbsp;&nbsp;"success": true|false,<br>
                    &nbsp;&nbsp;"data": ... | null,<br>
                    &nbsp;&nbsp;"message": "OK" | "Validation échouée" | ...,<br>
                    &nbsp;&nbsp;"errors": ... | null<br>
                    }
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
        <div class="xl:col-span-2 space-y-4">
            <div class="rounded-2xl border border-white/10 bg-[var(--admin-card)] p-6">
                <div class="flex items-center justify-between gap-3">
                    <div class="text-lg font-extrabold tracking-wide">Mobile API (v1)</div>
                    <span class="text-xs text-white/60">Prefix: /api/v1</span>
                </div>

                <div class="mt-4 space-y-3">
                    <details class="group rounded-2xl border border-white/10 bg-black/20 p-5">
                        <summary class="cursor-pointer list-none flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center rounded-lg bg-emerald-500/15 border border-emerald-400/20 px-2.5 py-1 text-xs font-extrabold text-emerald-200">AUTH</span>
                                <span class="font-bold">Authentication</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-white/60 transition group-open:rotate-180"></i>
                        </summary>
                        <div class="mt-4 space-y-3 text-sm text-white/80">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold">POST /auth/register</span>
                                        <span class="text-xs text-white/60">Public</span>
                                    </div>
                                    <div class="mt-3 font-mono text-xs leading-relaxed">
                                        { "nom": "...", "prenom": "...", "email": "...", "password": "..." }<br>
                                        Optional: telephone, adresse, id_wilaya, id_commune
                                    </div>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold">POST /auth/login</span>
                                        <span class="text-xs text-white/60">Public</span>
                                    </div>
                                    <div class="mt-3 font-mono text-xs leading-relaxed">
                                        { "email": "...", "password": "..." }<br>
                                        Response contains: token + client
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold">GET /auth/me</span>
                                        <span class="text-xs text-white/60">Bearer required</span>
                                    </div>
                                    <div class="mt-3 font-mono text-xs leading-relaxed">Returns current client profile (+ fournisseur if abonnee)</div>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold">POST /auth/logout</span>
                                        <span class="text-xs text-white/60">Bearer required</span>
                                    </div>
                                    <div class="mt-3 font-mono text-xs leading-relaxed">Revokes current token</div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold">PUT /auth/profil</span>
                                        <span class="text-xs text-white/60">Bearer required</span>
                                    </div>
                                    <div class="mt-3 font-mono text-xs leading-relaxed">
                                        { "nom": "...", "prenom": "...", "adresse": "...", "id_wilaya": 1, "id_commune": 1 }<br>
                                        Optional: telephone
                                    </div>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex items-center justify-between">
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

                    <details class="group rounded-2xl border border-white/10 bg-black/20 p-5">
                        <summary class="cursor-pointer list-none flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center rounded-lg bg-sky-500/15 border border-sky-400/20 px-2.5 py-1 text-xs font-extrabold text-sky-200">CATALOG</span>
                                <span class="font-bold">Boutiques & Produits</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-white/60 transition group-open:rotate-180"></i>
                        </summary>
                        <div class="mt-4 space-y-3 text-sm text-white/80">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold">GET /boutiques</span>
                                        <span class="text-xs text-white/60">Public</span>
                                    </div>
                                    <div class="mt-3 text-xs text-white/70">List active fournisseurs (boutiques) + nb_produits</div>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold">GET /boutiques/{id}</span>
                                        <span class="text-xs text-white/60">Public</span>
                                    </div>
                                    <div class="mt-3 text-xs text-white/70">Single boutique detail</div>
                                </div>
                            </div>

                            <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold">GET /produits</span>
                                    <span class="text-xs text-white/60">Public</span>
                                </div>
                                <div class="mt-3 font-mono text-xs leading-relaxed overflow-x-auto">
                                    Query params: frs_id (optional), categorie (optional), search (optional), page (optional)<br>
                                    Returns: data.items[] + data.pagination
                                </div>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold">GET /produits/categories</span>
                                        <span class="text-xs text-white/60">Public</span>
                                    </div>
                                    <div class="mt-3 font-mono text-xs leading-relaxed">Query param: frs_id (optional)</div>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold">GET /produits/{id}</span>
                                        <span class="text-xs text-white/60">Public</span>
                                    </div>
                                    <div class="mt-3 text-xs text-white/70">Product detail + images</div>
                                </div>
                            </div>
                        </div>
                    </details>

                    <details class="group rounded-2xl border border-white/10 bg-black/20 p-5">
                        <summary class="cursor-pointer list-none flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center rounded-lg bg-violet-500/15 border border-violet-400/20 px-2.5 py-1 text-xs font-extrabold text-violet-200">GEO</span>
                                <span class="font-bold">Geo (Wilayas / Communes)</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-white/60 transition group-open:rotate-180"></i>
                        </summary>
                        <div class="mt-4 space-y-3 text-sm text-white/80">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold">GET /wilayas</span>
                                        <span class="text-xs text-white/60">Public</span>
                                    </div>
                                    <div class="mt-3 text-xs text-white/70">Returns list of wilayas (cached)</div>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold">GET /communes/{wilaya}</span>
                                        <span class="text-xs text-white/60">Public</span>
                                    </div>
                                    <div class="mt-3 text-xs text-white/70">Returns communes for a wilaya (cached)</div>
                                </div>
                            </div>
                        </div>
                    </details>

                    <details class="group rounded-2xl border border-white/10 bg-black/20 p-5">
                        <summary class="cursor-pointer list-none flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center rounded-lg bg-amber-500/15 border border-amber-400/20 px-2.5 py-1 text-xs font-extrabold text-amber-200">ORDERS</span>
                                <span class="font-bold">Commandes</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-white/60 transition group-open:rotate-180"></i>
                        </summary>
                        <div class="mt-4 space-y-3 text-sm text-white/80">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold">POST /commandes</span>
                                        <span class="text-xs text-white/60">Bearer required</span>
                                    </div>
                                    <div class="mt-3 font-mono text-xs leading-relaxed overflow-x-auto">
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
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold">GET /commandes</span>
                                        <span class="text-xs text-white/60">Bearer required</span>
                                    </div>
                                    <div class="mt-3 text-xs text-white/70">Lists current client orders</div>
                                </div>
                            </div>
                            <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold">GET /commandes/{id}</span>
                                    <span class="text-xs text-white/60">Bearer required</span>
                                </div>
                                <div class="mt-3 text-xs text-white/70">Order detail (header + lines)</div>
                            </div>
                        </div>
                    </details>

                    <details class="group rounded-2xl border border-white/10 bg-black/20 p-5">
                        <summary class="cursor-pointer list-none flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center rounded-lg bg-rose-500/15 border border-rose-400/20 px-2.5 py-1 text-xs font-extrabold text-rose-200">NOTIF</span>
                                <span class="font-bold">Notifications & FCM</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-white/60 transition group-open:rotate-180"></i>
                        </summary>
                        <div class="mt-4 space-y-3 text-sm text-white/80">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold">GET /notifications</span>
                                        <span class="text-xs text-white/60">Bearer required</span>
                                    </div>
                                    <div class="mt-3 text-xs text-white/70">Returns last 50 notifications + non_lues</div>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold">PUT /notifications/{id}/lu</span>
                                        <span class="text-xs text-white/60">Bearer required</span>
                                    </div>
                                    <div class="mt-3 text-xs text-white/70">Mark one as read</div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold">PUT /notifications/tout-lire</span>
                                        <span class="text-xs text-white/60">Bearer required</span>
                                    </div>
                                    <div class="mt-3 text-xs text-white/70">Mark all as read</div>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold">DELETE /notifications/{id}</span>
                                        <span class="text-xs text-white/60">Bearer required</span>
                                    </div>
                                    <div class="mt-3 text-xs text-white/70">Delete notification</div>
                                </div>
                            </div>

                            <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                <div class="flex items-center justify-between">
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

            <div class="rounded-2xl border border-white/10 bg-[var(--admin-card)] p-6">
                <div class="text-lg font-extrabold tracking-wide">Implementation (Flutter)</div>
                <div class="mt-2 text-sm text-white/70">
                    Use your mobile base URL (LAN/device) and always attach the Bearer token after login.
                </div>

                <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div class="rounded-2xl border border-white/10 bg-black/20 p-5">
                        <div class="font-bold">1) Configure base URL</div>
                        <div class="mt-3 rounded-xl border border-white/10 bg-black/30 p-4 font-mono text-xs leading-relaxed overflow-x-auto">
                            API_BASE_URL = http://&lt;IP&gt;:8000/api/v1
                        </div>
                        <div class="mt-2 text-xs text-white/60">
                            Example: flutter run --dart-define=API_BASE_URL=http://192.168.1.104:8000/api/v1
                        </div>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-black/20 p-5">
                        <div class="font-bold">2) Handle auth token</div>
                        <div class="mt-3 rounded-xl border border-white/10 bg-black/30 p-4 font-mono text-xs leading-relaxed overflow-x-auto">
                            Authorization: Bearer &lt;token_from_login&gt;
                        </div>
                        <div class="mt-2 text-xs text-white/60">
                            Token is returned by /auth/login and /auth/register.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="rounded-2xl border border-white/10 bg-[var(--admin-card)] p-6">
                <div class="text-lg font-extrabold tracking-wide">PME API (integration)</div>
                <div class="mt-2 text-sm text-white/70">
                    Prefix: <span class="font-mono text-white">/api/v1/pme</span> • Authentication: <span class="font-mono text-white">Bearer &lt;fournisseur_token&gt;</span>
                </div>

                <div class="mt-4 space-y-3 text-sm text-white/80">
                    <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                        <div class="font-bold">POST /pme/sync-clients</div>
                        <div class="mt-2 font-mono text-xs leading-relaxed overflow-x-auto">
                            { "clients": [ { "code_client": "...", "nom": "...", "prenom": "...", "email": "...", "password": "...", "id_wilaya": 1, "id_commune": 1 } ] }
                        </div>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                        <div class="font-bold">POST /pme/sync-produits</div>
                        <div class="mt-2 font-mono text-xs leading-relaxed overflow-x-auto">
                            { "produits": [ { "reference": "...", "designation": "...", "prix": 100.0, "stock": 5, "categorie": "..." } ] }
                        </div>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                        <div class="font-bold">GET /pme/commandes</div>
                        <div class="mt-1 text-xs text-white/70">Query: synced=0|1 (optional)</div>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                        <div class="font-bold">GET /pme/commandes/export-csv</div>
                        <div class="mt-1 text-xs text-white/70">Downloads CSV (UTF-8 BOM)</div>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                        <div class="font-bold">PUT /pme/commandes/{id}/sync</div>
                        <div class="mt-1 text-xs text-white/70">Marks order as synced</div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-white/10 bg-[var(--admin-card)] p-6">
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
</div>
@endsection
