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
                    X-API-KEY: &lt;API_KEY&gt; (required for POST /pme/fournisseurs and /pme/fournisseurs/info)<br>
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
                                        { "nom": "...", "email": "...", "password": "...", "telephone": "..." }<br>
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
                                        { "nom": "...", "adresse": "...", "id_wilaya": 1, "id_commune": 1 }<br>
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
                                        <span class="font-bold">GET /boutiques/categories</span>
                                        <span class="text-xs text-white/60">Public (token optional)</span>
                                    </div>
                                    <div class="mt-2 text-xs text-white/70 leading-relaxed">
                                        Retourne toute la liste des catégories boutiques définies dans l'administration,
                                        y compris les catégories qui n'ont encore aucune boutique liée.
                                        Cet endpoint est utile pour alimenter les filtres catégories dans l'application mobile.
                                    </div>
                                    <div class="mt-2 text-xs text-white/60">
                                        Champs retournés :
                                        <span class="font-mono">id</span>,
                                        <span class="font-mono">name</span>,
                                        <span class="font-mono">slug</span>,
                                        <span class="font-mono">image_path</span>,
                                        <span class="font-mono">image_url</span>,
                                        <span class="font-mono">nb_boutiques</span>.
                                    </div>
                                    <div class="mt-3 font-mono text-xs leading-relaxed break-all">{{ url('/api/v1/boutiques/categories') }}</div>
                                    <pre class="mt-3 w-full max-w-full rounded-xl border border-white/10 bg-black/40 p-3 font-mono text-[11px] leading-relaxed whitespace-pre-wrap break-words overflow-x-hidden">{
  "success": true,
  "data": [
    {
      "id": 2,
      "name": "Cosmétique",
      "slug": "cosmetique",
      "image_path": "boutique-categories/2/categorie.jpg",
      "image_url": "https://g2d-dz.com/storage/boutique-categories/2/categorie.jpg",
      "nb_boutiques": 5
    }
  ],
  "message": "Liste des categories boutiques",
  "errors": null
}</pre>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                        <span class="font-bold">GET /boutiques</span>
                                        <span class="text-xs text-white/60">Public (token optional)</span>
                                    </div>
                                    <div class="mt-2 text-xs text-white/70 leading-relaxed">
                                        Retourne la liste des boutiques visibles et actives.
                                        Si un token client abonné est envoyé, la liste peut être restreinte à sa boutique.
                                        Cet endpoint ne demande aucun paramètre.
                                    </div>
                                    <div class="mt-2 text-xs text-white/60">
                                        Champs retournés par boutique :
                                        <span class="font-mono">id</span>,
                                        <span class="font-mono">nom_frs</span>,
                                        <span class="font-mono">telephone</span>,
                                        <span class="font-mono">logo_path</span>,
                                        <span class="font-mono">adresse</span>,
                                        <span class="font-mono">id_wilaya</span>,
                                        <span class="font-mono">id_commune</span>,
                                        <span class="font-mono">latitude</span>,
                                        <span class="font-mono">longitude</span>,
                                        <span class="font-mono">wilaya</span>,
                                        <span class="font-mono">commune</span>,
                                        <span class="font-mono">nb_produits</span>.
                                    </div>
                                    <div class="mt-3 font-mono text-xs leading-relaxed break-all">{{ url('/api/v1/boutiques') }}</div>
                                    <pre class="mt-3 w-full max-w-full rounded-xl border border-white/10 bg-black/40 p-3 font-mono text-[11px] leading-relaxed whitespace-pre-wrap break-words overflow-x-hidden">{
  "success": true,
  "data": [
    {
      "id": 3,
      "nom_frs": "Boutique Ahmed",
      "telephone": "0550123456",
      "logo_path": "frs/3/logo_1720700000.jpg",
      "adresse": "Alger centre",
      "id_wilaya": 16,
      "id_commune": 1601,
      "latitude": 36.7538,
      "longitude": 3.0588,
      "wilaya": "ALGER",
      "commune": "SIDI M'HAMED",
      "nb_produits": 24
    }
  ],
  "message": "Liste des boutiques",
  "errors": null
}</pre>
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
                                        &nbsp;&nbsp;"tele_livraison": "...",<br>
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
                        Authorization: Bearer &lt;boutique_token&gt;<br>
                        Accept: application/json
                    </div>
                    <div class="mt-3 text-xs text-white/65 leading-relaxed">
                        Each boutique only sees and modifies its own PME data. All client, product and order endpoints are automatically restricted by the boutique token.
                    </div>
                </div>

                <div class="mt-4 rounded-xl border border-sky-400/20 bg-sky-500/10 p-4 text-sm text-sky-100">
                    <div class="font-bold">Api key create boutique</div>
                    <div class="mt-2 text-xs leading-relaxed">
                        Les endpoints publics <span class="font-mono">POST /api/v1/pme/fournisseurs</span> et
                        <span class="font-mono">POST /api/v1/pme/fournisseurs/info</span> exigent maintenant une Api Key active de type
                        <span class="font-mono">create_fournisseur</span>.
                    </div>
                    <div class="mt-2 font-mono text-xs leading-relaxed break-all">
                        X-API-KEY: &lt;create_fournisseur_api_key&gt;
                    </div>
                </div>

                <div class="mt-4 space-y-3 text-sm text-white/80">
                    <details open class="group rounded-2xl border border-white/10 bg-black/20 p-4 sm:p-5">
                        <summary class="cursor-pointer list-none flex items-start justify-between gap-3">
                            <div class="flex flex-wrap items-center gap-3 min-w-0">
                                <span class="inline-flex items-center rounded-lg bg-sky-500/15 border border-sky-400/20 px-2.5 py-1 text-xs font-extrabold text-sky-200">CLIENTS</span>
                                <span class="font-bold">Clients PME</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-white/60 transition group-open:rotate-180 shrink-0"></i>
                        </summary>
                        <div class="mt-4 space-y-3">
                            <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                <div class="font-bold">Objectif</div>
                                <div class="mt-2 text-xs text-white/70 leading-relaxed">
                                    Cette rubrique permet a votre logiciel PME de lister, creer, modifier, supprimer et synchroniser en masse les clients de la boutique authentifiee.
                                    Tous les enregistrements sont automatiquement limites a la boutique du token PME. Le champ
                                    <span class="font-mono">nom</span>
                                    contient maintenant directement le nom client complet. Les champs retournes par l'API sont:
                                    <span class="font-mono">id</span>, <span class="font-mono">code_client</span>, <span class="font-mono">nom</span>,
                                    <span class="font-mono">email</span>, <span class="font-mono">telephone</span>, <span class="font-mono">adresse</span>,
                                    <span class="font-mono">id_wilaya</span>, <span class="font-mono">id_commune</span>, <span class="font-mono">type_client</span>,
                                    <span class="font-mono">tarif</span>, <span class="font-mono">actif</span>, <span class="font-mono">synced_pme</span>,
                                    <span class="font-mono">created_at</span>, <span class="font-mono">updated_at</span>.
                                </div>
                            </div>

                            <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                    <span class="font-bold">GET /clients</span>
                                    <span class="text-xs text-white/60">Lister les clients</span>
                                </div>
                                <div class="mt-2 text-xs text-white/70 leading-relaxed">
                                    Utilisez cet endpoint pour recuperer les clients de la boutique connectee. Filtres supportes:
                                    <span class="font-mono">synced=0|1</span> et <span class="font-mono">type_client=simple|abonne</span>.
                                    Limite actuelle: 500 lignes, triees par <span class="font-mono">updated_at DESC</span>.
                                </div>
                                <div class="mt-2 text-xs text-white/60">
                                    Exemple usage: recuperer uniquement les clients simples non synchronises.
                                </div>
                                <div class="mt-3 font-mono text-xs leading-relaxed break-all">{{ url('/api/v1/pme/clients?synced=0&type_client=simple') }}</div>
                                <pre class="mt-3 w-full max-w-full rounded-xl border border-white/10 bg-black/40 p-3 font-mono text-[11px] leading-relaxed whitespace-pre-wrap break-words overflow-x-hidden">{
  "success": true,
  "data": [
    {
      "id": 15,
      "code_client": "C12536",
      "nom": "Client 1",
      "email": "client1@example.com",
      "telephone": "0656232454",
      "adresse": "",
      "id_wilaya": 1,
      "nom_wilaya": "Adrar",
      "id_commune": 1,
      "nom_commune": "Adrar",
      "type_client": "simple",
      "tarif": 1,
      "actif": 1,
      "synced_pme": 0
    }
  ],
  "message": "Clients PME"
}</pre>
                            </div>

                            <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                    <span class="font-bold">GET /clients/{id}</span>
                                    <span class="text-xs text-white/60">Detail client</span>
                                </div>
                                <div class="mt-2 text-xs text-white/70 leading-relaxed">
                                    Retourne le detail d'un client unique appartenant a la boutique du token PME.
                                    Si l'id n'appartient pas a la boutique, l'API retourne
                                    <span class="font-mono">Ressource introuvable</span>.
                                </div>
                                <div class="mt-2 text-xs text-white/60">
                                    Cas d'usage: ouvrir la fiche client depuis le logiciel PME apres avoir liste les clients.
                                </div>
                                <div class="mt-3 font-mono text-xs leading-relaxed break-all">{{ url('/api/v1/pme/clients/6') }}</div>
                                <pre class="mt-3 w-full max-w-full rounded-xl border border-white/10 bg-black/40 p-3 font-mono text-[11px] leading-relaxed whitespace-pre-wrap break-words overflow-x-hidden">{
  "success": true,
  "data": {
    "id": 6,
    "code_client": "C10006",
    "nom": "Client 6",
    "email": "client6@example.com",
    "telephone": "0550000006",
    "adresse": "Alger",
    "id_wilaya": 16,
    "nom_wilaya": "Alger",
    "id_commune": 1601,
    "nom_commune": "Sidi M'Hamed",
    "type_client": "abonne",
    "tarif": 2,
    "actif": 1,
    "synced_pme": 1
  },
  "message": "Client PME"
}</pre>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="font-bold">POST /clients</div>
                                    <div class="mt-2 text-xs text-white/70 leading-relaxed">
                                        Cree un client unitaire depuis le logiciel PME.
                                        Si vous n'envoyez pas certains champs, les valeurs par defaut sont:
                                        <span class="font-mono">type_client=abonne</span>,
                                        <span class="font-mono">tarif=1</span>,
                                        <span class="font-mono">synced_pme=1</span>,
                                        <span class="font-mono">actif=1</span>.
                                        Le mot de passe peut etre envoye en clair; l'API le hash automatiquement.
                                        Le logiciel PME cree toujours des clients
                                        <span class="font-mono">abonne</span>.
                                    </div>
                                    <div class="mt-2 text-xs text-white/60">
                                        Cas d'usage: ajout manuel d'un nouveau client.
                                    </div>
                                    <pre class="mt-3 w-full max-w-full rounded-xl border border-white/10 bg-black/40 p-3 font-mono text-[11px] leading-relaxed whitespace-pre-wrap break-words overflow-x-hidden">{
  "code_client": "C12536",
  "nom": "Client 1",
  "email": "client1@example.com",
  "password": "12345678",
  "telephone": "0656232454",
  "adresse": "Alger centre",
  "id_wilaya": 16,
  "id_commune": 1601,
  "type_client": "abonne",
  "tarif": 2,
  "synced_pme": 1,
  "actif": 1
}</pre>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="font-bold">PUT /clients/{id}</div>
                                    <div class="mt-2 text-xs text-white/70 leading-relaxed">
                                        Met a jour un client existant appartenant a la boutique du token. Vous pouvez envoyer uniquement les champs modifies.
                                        Si <span class="font-mono">password</span> est envoye, il sera remplace; sinon il reste inchangé.
                                        Le type client reste toujours
                                        <span class="font-mono">abonne</span>
                                        pour les clients venant du logiciel PME.
                                    </div>
                                    <div class="mt-2 text-xs text-white/60">
                                        Cas d'usage: mise a jour du code client, telephone, tarif ou statut de synchronisation.
                                    </div>
                                    <pre class="mt-3 w-full max-w-full rounded-xl border border-white/10 bg-black/40 p-3 font-mono text-[11px] leading-relaxed whitespace-pre-wrap break-words overflow-x-hidden">{
  "code_client": "55666332459659"
}</pre>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="font-bold">DELETE /clients/{id}</div>
                                    <div class="mt-2 text-xs text-white/70 leading-relaxed">
                                        Supprime logiquement le client (soft delete). L'historique des commandes reste conserve.
                                    </div>
                                    <div class="mt-2 text-xs text-white/60">
                                        Cas d'usage: client obsolete ou bloque dans le logiciel PME.
                                    </div>
                                    <pre class="mt-3 w-full max-w-full rounded-xl border border-white/10 bg-black/40 p-3 font-mono text-[11px] leading-relaxed whitespace-pre-wrap break-words overflow-x-hidden">{
  "success": true,
  "data": { "id": 15, "deleted": true },
  "message": "Client supprime"
}</pre>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="font-bold">POST /sync-clients</div>
                                    <div class="mt-2 text-xs text-white/70 leading-relaxed">
                                        Synchronisation en masse des clients. C'est l'endpoint recommande pour les imports PME automatiques.
                                        Le matching se fait d'abord par <span class="font-mono">code_client</span>, puis par
                                        <span class="font-mono">email</span> chez la meme boutique pour eviter les doublons.
                                        Le parametre racine <span class="font-mono">synced</span> accepte <span class="font-mono">0|1</span> et vaut <span class="font-mono">1</span> par defaut.
                                        Chaque element cree ou met a jour un client en <span class="font-mono">type_client=abonne</span>.
                                    </div>
                                    <div class="mt-2 text-xs text-white/60">
                                        Cas d'usage: push quotidien depuis le logiciel de gestion.
                                    </div>
                                    <pre class="mt-3 w-full max-w-full rounded-xl border border-white/10 bg-black/40 p-3 font-mono text-[11px] leading-relaxed whitespace-pre-wrap break-words overflow-x-hidden">{
  "synced": 1,
  "clients": [
    {
      "code_client": "C12536",
      "nom": "Client 1",
      "email": "client1@example.com",
      "password": "12345678",
      "telephone": "0656232454",
      "id_wilaya": 1,
      "id_commune": 1,
      "tarif": 2
    }
  ]
}</pre>
                                </div>
                            </div>
                        </div>
                    </details>

                    <details open class="group rounded-2xl border border-white/10 bg-black/20 p-4 sm:p-5">
                        <summary class="cursor-pointer list-none flex items-start justify-between gap-3">
                            <div class="flex flex-wrap items-center gap-3 min-w-0">
                                <span class="inline-flex items-center rounded-lg bg-amber-500/15 border border-amber-400/20 px-2.5 py-1 text-xs font-extrabold text-amber-200">COMMANDES</span>
                                <span class="font-bold">Commandes PME</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-white/60 transition group-open:rotate-180 shrink-0"></i>
                        </summary>
                        <div class="mt-4 space-y-3">
                            <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                <div class="font-bold">Objectif</div>
                                <div class="mt-2 text-xs text-white/70 leading-relaxed">
                                    Cette rubrique permet d'exporter les commandes web vers le logiciel PME, soit en JSON, soit en CSV.
                                    Les commandes sont filtrees par boutique et par etat de synchronisation
                                    <span class="font-mono">synced_pme</span>.
                                </div>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="font-bold">GET /commandes</div>
                                    <div class="mt-2 text-xs text-white/70 leading-relaxed">
                                        Liste les commandes de la boutique avec leurs lignes. Parametre supporte:
                                        <span class="font-mono">synced=0|1</span>.
                                        Par defaut, l'API retourne uniquement les commandes non synchronisees.
                                    </div>
                                    <div class="mt-2 text-xs text-white/60">
                                        Cas d'usage: recuperer les nouvelles commandes a importer dans le logiciel PME.
                                    </div>
                                    <div class="mt-3 font-mono text-xs leading-relaxed break-all">{{ url('/api/v1/pme/commandes?synced=0') }}</div>
                                    <pre class="mt-3 w-full max-w-full rounded-xl border border-white/10 bg-black/40 p-3 font-mono text-[11px] leading-relaxed whitespace-pre-wrap break-words overflow-x-hidden">{
  "success": true,
  "data": [
    {
      "id": 101,
      "id_client": 15,
      "nom_client": "client1",
      "date_cmd": "2026-05-12 14:05:22",
      "statut": "en_attente",
      "montant_total": 2800,
      "adresse_livraison": "Alger centre",
      "tele_livraison": "0550123456",
      "id_wilaya": 16,
      "nom_wilaya": "Alger",
      "id_commune": 1601,
      "nom_commune": "Sidi M'Hamed",
      "notes": "",
      "synced_pme": 0,
      "lignes": [
        {
          "id_cmd": 101,
          "id_produit": 1,
          "reference": "R1",
          "designation": "Prod 1",
          "quantite": 2,
          "prix_unitaire": 900,
          "sous_total": 1800
        }
      ]
    }
  ],
  "message": "Commandes PME"
}</pre>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="font-bold">PUT /commandes/{id}/sync</div>
                                    <div class="mt-2 text-xs text-white/70 leading-relaxed">
                                        Marque une commande comme synchronisee apres import reussi dans le logiciel PME.
                                        L'API retourne <span class="font-mono">synced_pme=1</span>.
                                    </div>
                                    <div class="mt-2 text-xs text-white/60">
                                        Cas d'usage: confirmation d'import unitaire apres traitement comptable/stock.
                                    </div>
                                    <div class="mt-3 font-mono text-xs leading-relaxed break-all">{{ url('/api/v1/pme/commandes/1/sync') }}</div>
                                    <pre class="mt-3 w-full max-w-full rounded-xl border border-white/10 bg-black/40 p-3 font-mono text-[11px] leading-relaxed whitespace-pre-wrap break-words overflow-x-hidden">{
  "success": true,
  "data": {
    "id": 101,
    "synced_pme": 1
  },
  "message": "Commande synchronisee"
}</pre>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="font-bold">PUT /commandes/{id}/statut</div>
                                    <div class="mt-2 text-xs text-white/70 leading-relaxed">
                                        Met a jour le statut d une commande de la boutique.
                                        Valeurs autorisees:
                                        <span class="font-mono">en_attente</span>,
                                        <span class="font-mono">expediee</span>,
                                        <span class="font-mono">livree</span>,
                                        <span class="font-mono">annulee</span>.
                                    </div>
                                    <div class="mt-2 text-xs text-white/60">
                                        Cas d'usage: le logiciel PME pousse l'etat logistique final de la commande vers la plateforme.
                                    </div>
                                    <div class="mt-3 font-mono text-xs leading-relaxed break-all">{{ url('/api/v1/pme/commandes/101/statut') }}</div>
                                    <pre class="mt-3 w-full max-w-full rounded-xl border border-white/10 bg-black/40 p-3 font-mono text-[11px] leading-relaxed whitespace-pre-wrap break-words overflow-x-hidden">{
  "statut": "expediee"
}</pre>
                                    <pre class="mt-3 w-full max-w-full rounded-xl border border-white/10 bg-black/40 p-3 font-mono text-[11px] leading-relaxed whitespace-pre-wrap break-words overflow-x-hidden">{
  "success": true,
  "data": {
    "id": 101,
    "id_client": 15,
    "nom_client": "client1",
    "date_cmd": "2026-05-12 14:05:22",
    "statut": "expediee",
    "montant_total": 2800,
    "adresse_livraison": "Alger centre",
    "tele_livraison": "0550123456",
    "id_wilaya": 16,
    "nom_wilaya": "Alger",
    "id_commune": 1601,
    "nom_commune": "Sidi M'Hamed",
    "notes": "",
    "synced_pme": 0
  },
  "message": "Statut commande mis a jour"
}</pre>
                                </div>
                            </div>

                            <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                <div class="font-bold">GET /commandes/export-csv</div>
                                <div class="mt-2 text-xs text-white/70 leading-relaxed">
                                    Exporte les memes commandes au format CSV UTF-8 avec separateur <span class="font-mono">;</span>.
                                    Parametre supporte: <span class="font-mono">synced=0|1</span>.
                                    Le fichier contient a la fois l'entete commande et les lignes produit.
                                </div>
                                <div class="mt-2 text-xs text-white/60">
                                    Cas d'usage: logiciels PME anciens qui importent un fichier CSV plutot qu'une API JSON.
                                </div>
                                <div class="mt-3 font-mono text-xs leading-relaxed break-all">{{ url('/api/v1/pme/commandes/export-csv?synced=0') }}</div>
                            </div>
                        </div>
                    </details>

                    <details open class="group rounded-2xl border border-white/10 bg-black/20 p-4 sm:p-5">
                        <summary class="cursor-pointer list-none flex items-start justify-between gap-3">
                            <div class="flex flex-wrap items-center gap-3 min-w-0">
                                <span class="inline-flex items-center rounded-lg bg-emerald-500/15 border border-emerald-400/20 px-2.5 py-1 text-xs font-extrabold text-emerald-200">PRODUITS</span>
                                <span class="font-bold">Produits PME</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-white/60 transition group-open:rotate-180 shrink-0"></i>
                        </summary>
                        <div class="mt-4 space-y-3">
                            <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                <div class="font-bold">Objectif</div>
                                <div class="mt-2 text-xs text-white/70 leading-relaxed">
                                    La partie produits permet maintenant de lister les produits de la boutique du token PME,
                                    d'envoyer un produit unitaire, de marquer un produit comme synchronise,
                                    puis de creer ou mettre a jour plusieurs produits en matchant sur
                                    <span class="font-mono">reference</span>.
                                </div>
                            </div>

                            <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                <div class="font-bold">GET /produits</div>
                                <div class="mt-2 text-xs text-white/70 leading-relaxed">
                                    Retourne la liste paginee des produits appartenant a la boutique du token PME.
                                    Filtres supportes:
                                    <span class="font-mono">synced=0|1</span>,
                                    <span class="font-mono">categorie</span>,
                                    <span class="font-mono">search</span>,
                                    <span class="font-mono">actif=0|1</span>,
                                    <span class="font-mono">abonne_only=0|1</span>,
                                    <span class="font-mono">per_page</span> (max 100).
                                </div>
                                <div class="mt-2 text-xs text-white/60">
                                    Champs retournes:
                                    <span class="font-mono">id</span>,
                                    <span class="font-mono">synced_pme</span>,
                                    <span class="font-mono">reference</span>,
                                    <span class="font-mono">designation</span>,
                                    <span class="font-mono">description</span>,
                                    <span class="font-mono">pv_1</span>,
                                    <span class="font-mono">pv_2</span>,
                                    <span class="font-mono">pv_3</span>,
                                    <span class="font-mono">stock</span>,
                                    <span class="font-mono">categorie</span>,
                                    <span class="font-mono">abonne_only</span>,
                                    <span class="font-mono">enable_tier_pricing</span>,
                                    <span class="font-mono">quantity_prices</span>,
                                    <span class="font-mono">actif</span>,
                                    <span class="font-mono">images</span>.
                                </div>
                                <div class="mt-3 font-mono text-xs leading-relaxed break-all">{{ url('/api/v1/pme/produits?actif=1&search=gel&per_page=20') }}</div>
                                <pre class="mt-3 w-full max-w-full rounded-xl border border-white/10 bg-black/40 p-3 font-mono text-[11px] leading-relaxed whitespace-pre-wrap break-words overflow-x-hidden">{
  "success": true,
  "data": {
    "items": [
      {
        "id": 21,
        "id_frs": 3,
        "synced_pme": 0,
        "reference": "GEL-001",
        "designation": "Gel coiffant",
        "description": "",
        "pv_1": 250,
        "pv_2": 230,
        "pv_3": 220,
        "stock": 14,
        "image_principale": "produits/3/gel-001.jpg",
        "categorie": "Cosmetique",
        "abonne_only": 0,
        "enable_tier_pricing": false,
        "quantity_prices": [],
        "actif": 1,
        "images": []
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 20,
      "total": 1
    }
  },
  "message": "Produits PME"
}</pre>
                            </div>

                            <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                <div class="font-bold">POST /produits</div>
                                <div class="mt-2 text-xs text-white/70 leading-relaxed">
                                    Cree ou met a jour un seul produit de la boutique du token PME.
                                    Le matching se fait par
                                    <span class="font-mono">reference</span>
                                    dans la boutique courante.
                                    Si le produit existe deja, il est mis a jour, sinon il est cree.
                                    A la reception, le serveur positionne automatiquement
                                    <span class="font-mono">synced_pme=1</span>.
                                </div>
                                <div class="mt-2 text-xs text-white/60">
                                    Champs d'envoi supportes:
                                    <span class="font-mono">reference</span>,
                                    <span class="font-mono">designation</span>,
                                    <span class="font-mono">description</span>,
                                    <span class="font-mono">prix</span> ou <span class="font-mono">pv_1</span>,
                                    <span class="font-mono">pv_2</span>,
                                    <span class="font-mono">pv_3</span>,
                                    <span class="font-mono">stock</span>,
                                    <span class="font-mono">categorie</span>,
                                    <span class="font-mono">abonne_only</span>,
                                    <span class="font-mono">actif</span>,
                                    <span class="font-mono">enable_tier_pricing</span>,
                                    <span class="font-mono">quantity_prices[]</span>.
                                </div>
                                <div class="mt-2 text-xs text-white/60">
                                    Regles:
                                    si <span class="font-mono">pv_2</span> et <span class="font-mono">pv_3</span> ne sont pas envoyes,
                                    ils reprennent la valeur de <span class="font-mono">pv_1</span> ou <span class="font-mono">prix</span>.
                                    Si <span class="font-mono">enable_tier_pricing=1</span>,
                                    il faut envoyer au moins un palier dans <span class="font-mono">quantity_prices</span>.
                                </div>
                                <div class="mt-3 font-mono text-xs leading-relaxed break-all">{{ url('/api/v1/pme/produits') }}</div>
                                <pre class="mt-3 w-full max-w-full rounded-xl border border-white/10 bg-black/40 p-3 font-mono text-[11px] leading-relaxed whitespace-pre-wrap break-words overflow-x-hidden">{
  "reference": "GEL-001",
  "designation": "Gel coiffant",
  "description": "Fixation forte",
  "pv_1": 250,
  "pv_2": 230,
  "pv_3": 220,
  "stock": 14,
  "categorie": "Cosmetique",
  "abonne_only": 0,
  "actif": 1,
  "enable_tier_pricing": true,
  "quantity_prices": [
    {
      "quantity_min": 1,
      "quantity_max": 5,
      "price": 250
    },
    {
      "quantity_min": 6,
      "quantity_max": null,
      "price": 210
    }
  ]
}</pre>
                                <pre class="mt-3 w-full max-w-full rounded-xl border border-white/10 bg-black/40 p-3 font-mono text-[11px] leading-relaxed whitespace-pre-wrap break-words overflow-x-hidden">{
  "success": true,
  "data": {
    "id": 21,
    "id_frs": 3,
    "synced_pme": 1,
    "reference": "GEL-001",
    "designation": "Gel coiffant",
    "description": "Fixation forte",
    "pv_1": 250,
    "pv_2": 230,
    "pv_3": 220,
    "stock": 14,
    "image_principale": null,
    "categorie": "Cosmetique",
    "abonne_only": 0,
    "enable_tier_pricing": true,
    "quantity_prices": [
      {
        "quantity_min": 1,
        "quantity_max": 5,
        "price": 250
      },
      {
        "quantity_min": 6,
        "quantity_max": null,
        "price": 210
      }
    ],
    "actif": 1,
    "images": [],
    "created_at": "2026-07-13T10:15:00.000000Z",
    "updated_at": "2026-07-13T10:15:00.000000Z"
  },
  "message": "Produit cree"
}</pre>
                            </div>

                            <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                <div class="font-bold">PUT /produits/{id}/sync</div>
                                <div class="mt-2 text-xs text-white/70 leading-relaxed">
                                    Marque un produit de la boutique du token PME comme synchronise dans le serveur.
                                    Utilisez cet endpoint apres traitement ou import reussi du produit dans votre logiciel PME.
                                </div>
                                <div class="mt-2 text-xs text-white/60">
                                    Aucun body n'est requis. Le serveur positionne
                                    <span class="font-mono">synced_pme=1</span>.
                                </div>
                                <div class="mt-3 font-mono text-xs leading-relaxed break-all">{{ url('/api/v1/pme/produits/21/sync') }}</div>
                                <pre class="mt-3 w-full max-w-full rounded-xl border border-white/10 bg-black/40 p-3 font-mono text-[11px] leading-relaxed whitespace-pre-wrap break-words overflow-x-hidden">{
  "success": true,
  "data": {
    "id": 21,
    "reference": "GEL-001",
    "synced_pme": 1
  },
  "message": "Produit synchronise"
}</pre>
                            </div>

                            <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                <div class="font-bold">POST /sync-produits</div>
                                <div class="mt-2 text-xs text-white/70 leading-relaxed">
                                    Cree ou met a jour une liste de produits dans la boutique du token PME.
                                    Chaque element est traite comme l'endpoint
                                    <span class="font-mono">POST /produits</span>,
                                    avec matching sur
                                    <span class="font-mono">reference</span>.
                                    Pour chaque produit recu, le serveur positionne automatiquement
                                    <span class="font-mono">synced_pme=1</span>.
                                </div>
                                <div class="mt-2 text-xs text-white/60">
                                    Champs d'envoi supportes par produit:
                                    <span class="font-mono">reference</span>, <span class="font-mono">designation</span>,
                                    <span class="font-mono">description</span>,
                                    <span class="font-mono">prix</span> ou <span class="font-mono">pv_1</span>,
                                    <span class="font-mono">pv_2</span>, <span class="font-mono">pv_3</span>,
                                    <span class="font-mono">stock</span>, <span class="font-mono">categorie</span>,
                                    <span class="font-mono">abonne_only</span>,
                                    <span class="font-mono">actif</span>,
                                    <span class="font-mono">enable_tier_pricing</span>,
                                    <span class="font-mono">quantity_prices[]</span>.
                                    Si <span class="font-mono">pv_2</span> et <span class="font-mono">pv_3</span> ne sont pas fournis,
                                    ils reprennent la valeur de <span class="font-mono">pv_1</span> ou <span class="font-mono">prix</span>.
                                </div>
                                <div class="mt-2 text-xs text-white/60">
                                    Cas d'usage: synchronisation catalogue depuis PME. Pas encore d'endpoint dedie pour supprimer un produit.
                                </div>
                                <pre class="mt-3 w-full max-w-full rounded-xl border border-white/10 bg-black/40 p-3 font-mono text-[11px] leading-relaxed whitespace-pre-wrap break-words overflow-x-hidden">{
  "produits": [
    {
      "reference": "R1",
      "designation": "Prod 1",
      "description": "Description du produit 1",
      "pv_1": 100.0,
      "pv_2": 95.0,
      "pv_3": 90.0,
      "stock": 10,
      "categorie": "Cat",
      "abonne_only": 1,
      "actif": 1
    },
    {
      "reference": "R2",
      "designation": "Prod 2",
      "description": "Description du produit 2",
      "prix": 250.0,
      "stock": 4,
      "categorie": "Accessoires",
      "abonne_only": 0,
      "actif": 1,
      "enable_tier_pricing": true,
      "quantity_prices": [
        {
          "quantity_min": 1,
          "quantity_max": 9,
          "price": 250
        },
        {
          "quantity_min": 10,
          "quantity_max": null,
          "price": 225
        }
      ]
    }
  ]
}</pre>
                                <pre class="mt-3 w-full max-w-full rounded-xl border border-white/10 bg-black/40 p-3 font-mono text-[11px] leading-relaxed whitespace-pre-wrap break-words overflow-x-hidden">{
  "success": true,
  "data": {
    "nb_inseres": 1,
    "nb_mis_a_jour": 1
  },
  "message": "Sync produits terminé"
}</pre>
                            </div>
                        </div>
                    </details>

                    <details open class="group rounded-2xl border border-white/10 bg-black/20 p-4 sm:p-5">
                        <summary class="cursor-pointer list-none flex items-start justify-between gap-3">
                            <div class="flex flex-wrap items-center gap-3 min-w-0">
                                <span class="inline-flex items-center rounded-lg bg-violet-500/15 border border-violet-400/20 px-2.5 py-1 text-xs font-extrabold text-violet-200">FOURNISSEUR</span>
                                <span class="font-bold">Boutique PME</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-white/60 transition group-open:rotate-180 shrink-0"></i>
                        </summary>
                        <div class="mt-4 space-y-3">
                            <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                <div class="font-bold">Objectif</div>
                                <div class="mt-2 text-xs text-white/70 leading-relaxed">
                                    Cette rubrique sert a mettre a jour les informations de la boutique depuis le logiciel PME:
                                    nom, telephone, adresse, geolocalisation, visibilite et logo.
                                </div>
                            </div>

                            <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                <div class="font-bold">POST /fournisseurs</div>
                                <div class="mt-2 text-xs text-white/70 leading-relaxed">
                                    Cree une nouvelle boutique depuis le systeme PME.
                                    Cet endpoint est public mais protege par une Api Key de type
                                    <span class="font-mono">create_fournisseur</span>.
                                    Champs requis:
                                    <span class="font-mono">nom_boutique</span>,
                                    <span class="font-mono">boutique_category_id</span>,
                                    <span class="font-mono">email</span>,
                                    <span class="font-mono">telephone</span>,
                                    <span class="font-mono">code_wilaya</span>,
                                    <span class="font-mono">code_commune</span>.
                                    Valeurs automatiques:
                                    mot de passe <span class="font-mono">12345678</span>,
                                    <span class="font-mono">actif=1</span>,
                                    expiration demo a <span class="font-mono">+1 mois</span>,
                                    token PME genere automatiquement.
                                </div>
                                <div class="mt-2 font-mono text-xs leading-relaxed break-all">
                                    Header requis: X-API-KEY: &lt;create_fournisseur_api_key&gt;
                                </div>
                                <pre class="mt-3 w-full max-w-full rounded-xl border border-white/10 bg-black/40 p-3 font-mono text-[11px] leading-relaxed whitespace-pre-wrap break-words overflow-x-hidden">{
  "nom_boutique": "Boutique Ahmed",
  "boutique_category_id": 2,
  "email": "boutique.ahmed@gmail.com",
  "telephone": "0550123456",
  "code_wilaya": 16,
  "code_commune": 1601
}</pre>
                                <pre class="mt-3 w-full max-w-full rounded-xl border border-white/10 bg-black/40 p-3 font-mono text-[11px] leading-relaxed whitespace-pre-wrap break-words overflow-x-hidden">{
  "success": true,
  "data": {
    "id": 12,
    "nom_boutique": "Boutique Ahmed",
    "boutique_category_id": 2,
    "boutique_category_name": "Cosmétique",
    "email": "boutique.ahmed@gmail.com",
    "telephone": "0550123456",
    "code_wilaya": 16,
    "code_commune": 1601,
    "actif": 1,
    "date_expiration": "2026-08-04",
    "password_par_defaut": "12345678",
    "pme_token": "uuid-token"
  },
  "message": "Boutique creee"
}</pre>
                            </div>

                            <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                <div class="font-bold">POST /fournisseurs/info</div>
                                <div class="mt-2 text-xs text-white/70 leading-relaxed">
                                    Retourne les informations de la boutique a partir de
                                    <span class="font-mono">email</span> et
                                    <span class="font-mono">password</span>.
                                    Cet endpoint exige aussi une Api Key de type
                                    <span class="font-mono">create_fournisseur</span>.
                                    Si le compte est expire ou desactive, une erreur est retournee.
                                </div>
                                <div class="mt-2 font-mono text-xs leading-relaxed break-all">
                                    Header requis: X-API-KEY: &lt;create_fournisseur_api_key&gt;
                                </div>
                                <pre class="mt-3 w-full max-w-full rounded-xl border border-white/10 bg-black/40 p-3 font-mono text-[11px] leading-relaxed whitespace-pre-wrap break-words overflow-x-hidden">{
  "email": "boutique.ahmed@gmail.com",
  "password": "12345678"
}</pre>
                                <pre class="mt-3 w-full max-w-full rounded-xl border border-white/10 bg-black/40 p-3 font-mono text-[11px] leading-relaxed whitespace-pre-wrap break-words overflow-x-hidden">{
  "success": true,
  "data": {
    "id": 12,
    "nom_boutique": "Boutique Ahmed",
    "boutique_category_id": 2,
    "boutique_category_name": "Cosmétique",
    "email": "boutique.ahmed@gmail.com",
    "telephone": "0550123456",
    "adresse": "",
    "code_wilaya": 16,
    "code_commune": 1601,
    "actif": 1,
    "date_expiration": "2026-08-04",
    "pme_token": "uuid-token",
    "logo_url": "",
    "is_visible": 1
  },
  "message": "Boutique trouvee"
}</pre>
                            </div>

                            <div class="rounded-xl border border-white/10 bg-black/30 p-4">
                                <div class="font-bold">POST /sync-fournisseur</div>
                                <div class="mt-2 text-xs text-white/70 leading-relaxed">
                                    Met a jour la boutique du token PME.
                                    Pour les champs texte utilisez JSON classique.
                                    Pour charger un logo, utilisez <span class="font-mono">multipart/form-data</span>.
                                    Champs supportes:
                                    <span class="font-mono">nom_frs</span>, <span class="font-mono">telephone</span>, <span class="font-mono">adresse</span>,
                                    <span class="font-mono">id_wilaya</span>, <span class="font-mono">id_commune</span>,
                                    <span class="font-mono">latitude</span>, <span class="font-mono">longitude</span>,
                                    <span class="font-mono">is_visible</span>, <span class="font-mono">remove_logo</span>, <span class="font-mono">logo</span>.
                                </div>
                                <div class="mt-2 text-xs text-white/60">
                                    Cas d'usage: mise a jour de la fiche boutique ou remplacement/suppression du logo depuis PME.
                                </div>
                                <pre class="mt-3 w-full max-w-full rounded-xl border border-white/10 bg-black/40 p-3 font-mono text-[11px] leading-relaxed whitespace-pre-wrap break-words overflow-x-hidden">{
  "telephone": "0550123456",
  "adresse": "Alger",
  "id_wilaya": 16,
  "id_commune": 1601,
  "latitude": 36.7538,
  "longitude": 3.0588,
  "is_visible": 1,
  "remove_logo": 0
}</pre>
                                <pre class="mt-3 w-full max-w-full rounded-xl border border-white/10 bg-black/40 p-3 font-mono text-[11px] leading-relaxed whitespace-pre-wrap break-words overflow-x-hidden">{
  "success": true,
  "data": {
    "id": 3,
    "nom_frs": "Amantek",
    "telephone": "0550123456",
    "adresse": "Alger",
    "id_wilaya": 16,
    "id_commune": 1601,
    "latitude": 36.7538,
    "longitude": 3.0588,
    "logo_url": "https://...",
    "is_visible": 1
  },
  "message": "Sync boutique termine"
}</pre>
                            </div>
                        </div>
                    </details>
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
