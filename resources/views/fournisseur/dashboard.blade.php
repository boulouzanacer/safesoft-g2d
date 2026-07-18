@extends('layouts.fournisseur')

@section('content')
@php
    $dashboardThemePreviewClasses = [
        'azure_modern' => 'frs-theme-preview--azure',
        'emerald_bloom' => 'frs-theme-preview--emerald',
        'sunset_pop' => 'frs-theme-preview--sunset',
        'violet_luxe' => 'frs-theme-preview--violet',
        'rose_boutique' => 'frs-theme-preview--rose',
        'graphite_pro' => 'frs-theme-preview--graphite',
    ];
    $dashboardThemePreviewClass = $dashboardThemePreviewClasses[$storefront_theme_key ?? 'azure_modern'] ?? 'frs-theme-preview--azure';
@endphp

<style>
    .frs-theme-preview-primary,
    .frs-theme-preview-accent {
        transition: background-color 150ms ease;
    }
    .frs-theme-preview--azure .frs-theme-preview-primary { background: linear-gradient(135deg, #1D4ED8, #0EA5E9); }
    .frs-theme-preview--azure .frs-theme-preview-accent { background: #DBEAFE; }

    .frs-theme-preview--emerald .frs-theme-preview-primary { background: linear-gradient(135deg, #059669, #34D399); }
    .frs-theme-preview--emerald .frs-theme-preview-accent { background: #D1FAE5; }

    .frs-theme-preview--sunset .frs-theme-preview-primary { background: linear-gradient(135deg, #EA580C, #FB7185); }
    .frs-theme-preview--sunset .frs-theme-preview-accent { background: #FFE4E6; }

    .frs-theme-preview--violet .frs-theme-preview-primary { background: linear-gradient(135deg, #7C3AED, #A855F7); }
    .frs-theme-preview--violet .frs-theme-preview-accent { background: #EDE9FE; }

    .frs-theme-preview--rose .frs-theme-preview-primary { background: linear-gradient(135deg, #DB2777, #FB7185); }
    .frs-theme-preview--rose .frs-theme-preview-accent { background: #FCE7F3; }

    .frs-theme-preview--graphite .frs-theme-preview-primary { background: linear-gradient(135deg, #111827, #334155); }
    .frs-theme-preview--graphite .frs-theme-preview-accent { background: #E2E8F0; }
</style>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
    <a href="{{ url('/fournisseur/commandes?statut=en_attente') }}"
       class="rounded-2xl p-5 border border-white/10 bg-[var(--frs-card)] hover:bg-white/5 transition">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-sm text-white/60">Commandes en attente</div>
                <div class="text-3xl font-extrabold mt-1">{{ $cmd_en_attente }}</div>
            </div>
            <div class="h-12 w-12 rounded-2xl flex items-center justify-center"
                 style="background: linear-gradient(135deg, #fb923c, #f97316);">
                <i class="fa-solid fa-hourglass-half text-white text-lg"></i>
            </div>
        </div>
        <div class="mt-3 text-xs text-white/60">Cliquez pour afficher la liste filtrée</div>
    </a>

    <div class="rounded-2xl p-5 border border-white/10 bg-[var(--frs-card)]">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-sm text-white/60">Commandes du jour</div>
                <div class="text-3xl font-extrabold mt-1">{{ $cmd_du_jour }}</div>
            </div>
            <div class="h-12 w-12 rounded-2xl flex items-center justify-center"
                 style="background: linear-gradient(135deg, var(--frs-primary), #0A3D7A);">
                <i class="fa-solid fa-cart-shopping text-white text-lg"></i>
            </div>
        </div>
    </div>

    <div class="rounded-2xl p-5 border border-white/10 bg-[var(--frs-card)]">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-sm text-white/60">Clients abonnés</div>
                <div class="text-3xl font-extrabold mt-1">{{ $clients_abonnes }}</div>
            </div>
            <div class="h-12 w-12 rounded-2xl flex items-center justify-center"
                 style="background: linear-gradient(135deg, #22c55e, #16a34a);">
                <i class="fa-solid fa-users text-white text-lg"></i>
            </div>
        </div>
    </div>

    <div class="rounded-2xl p-5 border border-white/10 bg-[var(--frs-card)]">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-sm text-white/60">Produits actifs</div>
                <div class="text-3xl font-extrabold mt-1">{{ $produits_actifs }}</div>
            </div>
            <div class="h-12 w-12 rounded-2xl flex items-center justify-center"
                 style="background: linear-gradient(135deg, #a855f7, #7c3aed);">
                <i class="fa-solid fa-box-open text-white text-lg"></i>
            </div>
        </div>
    </div>

    <a href="{{ url('/fournisseur/visites/planning') }}"
       class="rounded-2xl p-5 border border-white/10 bg-[var(--frs-card)] hover:bg-white/5 transition">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-sm text-white/60">Visites du jour</div>
                <div class="text-3xl font-extrabold mt-1">{{ $visites_du_jour }}</div>
            </div>
            <div class="h-12 w-12 rounded-2xl flex items-center justify-center" style="background: linear-gradient(135deg, #14b8a6, #0f766e);">
                <i class="fa-solid fa-route text-white text-lg"></i>
            </div>
        </div>
    </a>

    <div class="rounded-2xl p-5 border border-white/10 bg-[var(--frs-card)]">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-sm text-white/60">Plans actifs</div>
                <div class="text-3xl font-extrabold mt-1">{{ $plans_actifs }}</div>
            </div>
            <div class="h-12 w-12 rounded-2xl flex items-center justify-center" style="background: linear-gradient(135deg, #38bdf8, #2563eb);">
                <i class="fa-solid fa-calendar-days text-white text-lg"></i>
            </div>
        </div>
    </div>

    <div class="rounded-2xl p-5 border border-white/10 bg-[var(--frs-card)]">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-sm text-white/60">Clients sans planning</div>
                <div class="text-3xl font-extrabold mt-1">{{ $clients_sans_planning }}</div>
            </div>
            <div class="h-12 w-12 rounded-2xl flex items-center justify-center" style="background: linear-gradient(135deg, #ef4444, #b91c1c);">
                <i class="fa-solid fa-triangle-exclamation text-white text-lg"></i>
            </div>
        </div>
    </div>
</div>

<div class="mt-4 rounded-2xl p-5 border border-white/10 bg-[var(--frs-card)]">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <div class="font-extrabold tracking-wide">Lien spécial de ma boutique</div>
            <div class="mt-1 text-sm text-white/60">Ce lien affiche uniquement les produits de votre boutique, sans renvoyer le visiteur vers la plateforme globale.</div>
            @if(! empty($primary_custom_domain))
                <div class="mt-3 inline-flex items-center gap-2 rounded-full border border-emerald-400/20 bg-emerald-500/10 px-3 py-1 text-xs font-bold text-emerald-200">
                    <i class="fa-solid fa-globe"></i>
                    <span>Domaine principal : {{ $primary_custom_domain }}</span>
                </div>
            @else
                <div class="mt-3 text-xs text-white/50">
                    Les domaines personnalisés se gèrent maintenant depuis l'administration, dans l'édition de la boutique.
                </div>
            @endif
        </div>
        <div class="flex flex-col sm:flex-row gap-2 w-full lg:w-auto">
            <input type="text"
                   id="storefrontLinkInput"
                   value="{{ $storefront_url }}"
                   readonly
                   class="min-w-0 w-full lg:w-[420px] rounded-2xl border border-white/10 bg-black/20 px-4 py-3 text-sm text-white/80 outline-none">
            <button type="button"
                    id="copyStorefrontLinkButton"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl px-4 py-3 text-sm font-extrabold text-white"
                    style="background: linear-gradient(135deg, var(--frs-primary), #0A3D7A);">
                <i class="fa-regular fa-copy"></i>
                Copier le lien
            </button>
        </div>
    </div>
</div>

<div class="mt-4 rounded-2xl p-5 border border-white/10 bg-[var(--frs-card)]">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="min-w-0">
            <div class="font-extrabold tracking-wide">Theme actuel du storefront</div>
            <div class="mt-1 text-sm text-white/60">{{ $storefront_theme_name }}{{ $storefront_theme_tagline ? ' - '.$storefront_theme_tagline : '' }}</div>
            <div class="mt-3 inline-flex items-center gap-2 rounded-full border border-white/10 bg-black/20 px-3 py-1 text-xs font-bold text-white/70">
                <i class="fa-solid fa-palette"></i>
                <span>Modifiable depuis votre profil ou depuis l administration</span>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <div class="{{ $dashboardThemePreviewClass }} flex items-center gap-2 rounded-3xl border border-white/10 bg-black/20 p-3">
                <div class="frs-theme-preview-primary h-10 w-10 rounded-2xl"></div>
                <div class="frs-theme-preview-accent h-10 w-16 rounded-2xl"></div>
            </div>
            <a href="{{ url('/fournisseur/profil') }}"
               class="inline-flex items-center justify-center gap-2 rounded-2xl px-4 py-3 text-sm font-extrabold text-white"
               style="background: linear-gradient(135deg, var(--frs-primary), #0A3D7A);">
                <i class="fa-solid fa-palette"></i>
                Changer le theme
            </a>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mt-4">
    <div class="rounded-2xl p-5 border border-white/10 bg-[var(--frs-card)] xl:col-span-2">
        <div class="flex items-center justify-between gap-3 mb-4">
            <div>
                <div class="font-extrabold tracking-wide">Planning de visite du jour</div>
                <div class="text-sm text-white/60">Clients calcules depuis le cache journalier genere.</div>
            </div>
            <a href="{{ url('/fournisseur/visites/planning') }}" class="text-sm text-[var(--frs-primary)] hover:opacity-90">Gerer le planning</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @forelse($clients_a_visiter as $visite)
                <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                    <div class="font-bold">{{ $visite->nom ?: '-' }}</div>
                    <div class="text-xs text-white/60 mt-1">Code: {{ $visite->code_client ?: '-' }}</div>
                </div>
            @empty
                <div class="text-white/60">Aucune visite generee pour aujourd'hui.</div>
            @endforelse
        </div>
    </div>

    <div class="rounded-2xl p-5 border border-white/10 bg-[var(--frs-card)]">
        <div class="flex items-center justify-between mb-4">
            <div class="font-extrabold tracking-wide">Charge sur 7 jours</div>
            <span class="text-xs text-white/50">Projection</span>
        </div>
        <div class="space-y-3">
            @forelse($prochaines_visites as $item)
                <div class="flex items-center justify-between text-sm rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                    <span>{{ \Illuminate\Support\Carbon::parse($item->visit_date)->format('d/m/Y') }}</span>
                    <span class="font-extrabold">{{ $item->total }} visite(s)</span>
                </div>
            @empty
                <div class="text-white/60">Aucune visite planifiee sur les 7 prochains jours.</div>
            @endforelse
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mt-4">
    <div class="rounded-2xl p-5 border border-white/10 bg-[var(--frs-card)] overflow-hidden">
        <div class="flex items-center justify-between mb-4">
            <div class="font-extrabold tracking-wide">Dernières commandes</div>
            <a href="{{ url('/fournisseur/commandes') }}" class="text-sm text-[var(--frs-primary)] hover:opacity-90">Voir tout</a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-white/60">
                    <tr>
                        <th class="text-left py-3 pr-4 font-semibold">#</th>
                        <th class="text-left py-3 pr-4 font-semibold">Date</th>
                        <th class="text-left py-3 pr-4 font-semibold">Client</th>
                        <th class="text-left py-3 pr-4 font-semibold">Statut</th>
                        <th class="text-right py-3 font-semibold">Montant</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse($dernieres_commandes as $c)
                        @php
                            $statut = $c->statut;
                            $badge = match($statut) {
                                'en_attente' => 'bg-amber-500/15 text-amber-300 border border-amber-400/20',
                                'confirmee' => 'bg-sky-500/15 text-sky-300 border border-sky-400/20',
                                'expediee' => 'bg-indigo-500/15 text-indigo-300 border border-indigo-400/20',
                                'livree' => 'bg-emerald-500/15 text-emerald-300 border border-emerald-400/20',
                                'annulee' => 'bg-red-500/15 text-red-300 border border-red-400/20',
                                default => 'bg-white/10 text-white/70 border border-white/10'
                            };
                        @endphp
                        <tr class="hover:bg-white/5">
                            <td class="py-3 pr-4 font-semibold">#{{ $c->id }}</td>
                            <td class="py-3 pr-4 text-white/80">{{ \Illuminate\Support\Carbon::parse($c->date_cmd)->format('d/m/Y H:i') }}</td>
                            <td class="py-3 pr-4 text-white/80">{{ $c->client_nom ?: '-' }}</td>
                            <td class="py-3 pr-4">
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $badge }}">{{ $statut }}</span>
                            </td>
                            <td class="py-3 text-right font-bold">{{ number_format((float)$c->montant_total, 2, '.', ' ') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-white/60">Aucune commande</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-2xl p-5 border border-white/10 bg-[var(--frs-card)]">
        <div class="flex items-center justify-between mb-4">
            <div class="font-extrabold tracking-wide">Produits en rupture de stock</div>
            <a href="{{ url('/fournisseur/produits') }}" class="text-sm text-[var(--frs-primary)] hover:opacity-90">Voir tout</a>
        </div>

        <div class="space-y-3">
            @forelse($rupture_stock as $p)
                @php
                    $stock = (int) $p->stock;
                    $badge = $stock === 0
                        ? 'bg-red-500/15 text-red-300 border border-red-400/20'
                        : 'bg-amber-500/15 text-amber-300 border border-amber-400/20';
                    $label = $stock === 0 ? 'Rupture' : 'Stock faible';
                @endphp
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <div class="font-semibold truncate">{{ $p->designation }}</div>
                        <div class="text-xs text-white/60 truncate">{{ $p->reference }}</div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $badge }}">{{ $label }} ({{ $stock }})</span>
                    </div>
                </div>
            @empty
                <div class="text-white/60">Aucun produit en alerte stock.</div>
            @endforelse
        </div>
    </div>
</div>

<script>
    (() => {
        const button = document.getElementById('copyStorefrontLinkButton');
        const input = document.getElementById('storefrontLinkInput');
        if (!button || !input) return;

        button.addEventListener('click', async () => {
            const value = input.value || '';
            if (value === '') return;

            try {
                await navigator.clipboard.writeText(value);
                button.innerHTML = '<i class="fa-solid fa-check"></i> Lien copié';
            } catch (_) {
                input.focus();
                input.select();
                document.execCommand('copy');
                button.innerHTML = '<i class="fa-solid fa-check"></i> Lien copié';
            }

            window.setTimeout(() => {
                button.innerHTML = '<i class="fa-regular fa-copy"></i> Copier le lien';
            }, 1800);
        });
    })();
</script>
@endsection
