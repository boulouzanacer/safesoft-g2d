@extends('layouts.fournisseur')

@section('content')
@php
    $steps = ['en_attente', 'confirmee', 'expediee', 'livree'];
    $current = (string) $commande->statut;
    $currentIndex = array_search($current, $steps, true);
    $statusBadge = match ($current) {
        'en_attente' => 'border-amber-400/20 bg-amber-500/15 text-amber-200',
        'confirmee' => 'border-sky-400/20 bg-sky-500/15 text-sky-200',
        'expediee' => 'border-indigo-400/20 bg-indigo-500/15 text-indigo-200',
        'livree' => 'border-emerald-400/20 bg-emerald-500/15 text-emerald-200',
        'annulee' => 'border-red-400/20 bg-red-500/15 text-red-200',
        default => 'border-white/10 bg-white/5 text-white/80',
    };
@endphp
<div class="max-w-7xl space-y-6">
    <div class="rounded-3xl border border-white/10 bg-[var(--frs-card)] p-5 md:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start gap-4">
                <div class="h-16 w-16 shrink-0 rounded-2xl bg-[var(--frs-primary)]/15 text-[var(--frs-primary)] flex items-center justify-center text-2xl">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-2xl font-extrabold tracking-wide break-words">Commande #{{ $commande->id }}</div>
                    <div class="mt-1 text-sm text-white/60">{{ \Illuminate\Support\Carbon::parse($commande->date_cmd)->format('d/m/Y H:i') }}</div>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-bold {{ $statusBadge }}">
                            {{ $current }}
                        </span>
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-bold {{ (int)$commande->synced_pme === 1 ? 'border-emerald-400/20 bg-emerald-500/15 text-emerald-200' : 'border-amber-400/20 bg-amber-500/15 text-amber-200' }}">
                            {{ (int)$commande->synced_pme === 1 ? 'Synchronisé PME' : 'En attente sync' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ url('/fournisseur/commandes') }}"
                   class="rounded-2xl px-4 py-3 font-bold border border-white/10 hover:bg-white/10">
                    Retour
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="rounded-3xl border border-sky-400/20 bg-gradient-to-br from-sky-500/20 to-sky-500/5 p-5">
            <div class="text-xs font-bold uppercase tracking-[0.2em] text-sky-200/80">Montant total</div>
            <div class="mt-2 text-3xl font-extrabold text-sky-100">{{ number_format((float)$commande->montant_total, 2, '.', ' ') }}</div>
            <div class="mt-3 text-xs text-sky-100/70">Valeur globale de la commande.</div>
        </div>
        <div class="rounded-3xl border border-indigo-400/20 bg-gradient-to-br from-indigo-500/20 to-indigo-500/5 p-5">
            <div class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-200/80">Lignes</div>
            <div class="mt-2 text-3xl font-extrabold text-indigo-100">{{ $lignes->count() }}</div>
            <div class="mt-3 text-xs text-indigo-100/70">Nombre de produits commandés.</div>
        </div>
        <div class="rounded-3xl border border-emerald-400/20 bg-gradient-to-br from-emerald-500/20 to-emerald-500/5 p-5">
            <div class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-200/80">Client</div>
            <div class="mt-2 text-xl font-extrabold text-emerald-100 break-words">{{ $client?->display_name ?: '-' }}</div>
            <div class="mt-3 text-xs text-emerald-100/70">Acheteur lié à cette commande.</div>
        </div>
        <div class="rounded-3xl border border-amber-400/20 bg-gradient-to-br from-amber-500/20 to-amber-500/5 p-5">
            <div class="text-xs font-bold uppercase tracking-[0.2em] text-amber-200/80">Date</div>
            <div class="mt-2 text-xl font-extrabold text-amber-100">{{ \Illuminate\Support\Carbon::parse($commande->date_cmd)->format('d/m/Y') }}</div>
            <div class="mt-3 text-xs text-amber-100/70">{{ \Illuminate\Support\Carbon::parse($commande->date_cmd)->format('H:i') }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 rounded-3xl border border-white/10 bg-[var(--frs-card)] p-5 md:p-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div class="font-extrabold tracking-wide">Client & Livraison</div>
                @if((int)($client?->id ?? 0) > 0)
                    <a href="{{ url('/fournisseur/clients/'.$client->id) }}"
                       class="inline-flex items-center gap-2 text-sm font-bold text-[var(--frs-primary)] hover:opacity-90">
                        <span>Ouvrir fiche client</span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                    </a>
                @endif
            </div>
            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-white/50">Nom</div>
                    <div class="mt-2 font-extrabold break-words">
                        @if((int)($client?->id ?? 0) > 0)
                            <a href="{{ url('/fournisseur/clients/'.$client->id) }}"
                               class="inline-flex items-center gap-2 text-[var(--frs-primary)] hover:opacity-90">
                                <span>{{ $client?->display_name ?: '-' }}</span>
                                <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                            </a>
                        @else
                            {{ $client?->display_name ?: '-' }}
                        @endif
                    </div>
                </div>
                <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-white/50">Email</div>
                    <div class="mt-2 font-extrabold break-all">{{ $client?->email ?: '-' }}</div>
                </div>
                <div class="rounded-2xl border border-white/10 bg-black/20 p-4 md:col-span-2">
                    <div class="text-xs font-bold uppercase tracking-wide text-white/50">Adresse livraison</div>
                    <div class="mt-2 font-semibold text-white/85 break-words">{{ trim((string)$commande->adresse_livraison) !== '' ? $commande->adresse_livraison : '-' }}</div>
                </div>
                <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-white/50">Téléphone livraison</div>
                    <div class="mt-2 font-semibold text-white/85 break-words">{{ trim((string)($commande->tele_shipping ?? '')) !== '' ? $commande->tele_shipping : '-' }}</div>
                </div>
                @if(trim((string)($commande->notes ?? '')) !== '')
                    <div class="rounded-2xl border border-white/10 bg-black/20 p-4 md:col-span-2">
                        <div class="text-xs font-bold uppercase tracking-wide text-white/50">Notes</div>
                        <div class="mt-2 font-semibold text-white/85 break-words">{{ $commande->notes }}</div>
                    </div>
                @endif
            </div>
        </div>

        <div class="rounded-3xl border border-white/10 bg-[var(--frs-card)] p-5 md:p-6">
            <div class="font-extrabold tracking-wide">Statut</div>

            <div class="mt-5 space-y-3">
                @foreach($steps as $i => $s)
                    @php
                        $done = $currentIndex !== false && $i <= $currentIndex;
                        $isCurrent = $current === $s;
                    @endphp
                    <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                        <div class="h-9 w-9 rounded-xl flex items-center justify-center border shrink-0"
                             @if($done)
                                 style="background: linear-gradient(135deg, var(--frs-primary), #0A3D7A); border-color: transparent;"
                             @else
                                 style="background: rgba(255,255,255,0.06); border-color: rgba(255,255,255,0.10);"
                             @endif>


                            <i class="fa-solid {{ $done ? 'fa-check' : 'fa-circle' }} text-white text-xs"></i>
                        </div>
                        <div class="{{ $isCurrent ? 'font-extrabold text-white' : 'font-semibold text-white/80' }}">
                            {{ $s }}
                        </div>
                    </div>
                @endforeach

                @if($current === 'annulee')
                    <div class="flex items-center gap-3 rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3">
                        <div class="h-9 w-9 rounded-xl flex items-center justify-center border border-red-400/20 bg-red-500/10 shrink-0">
                            <i class="fa-solid fa-xmark text-red-300 text-xs"></i>
                        </div>
                        <div class="font-extrabold text-red-200">annulee</div>
                    </div>
                @endif
            </div>

            <form method="POST" action="{{ url('/fournisseur/commandes/'.$commande->id.'/statut') }}" class="mt-5">
                @csrf
                @method('PUT')
                <label class="block text-sm font-semibold text-white/70 mb-2">Changer statut</label>
                <div class="flex flex-col sm:flex-row gap-2">
                    <select name="statut"
                            class="flex-1 rounded-2xl border border-white/10 bg-black/20 px-4 py-3 outline-none focus:border-[var(--frs-primary)]">
                        @foreach($statuts as $s)
                            <option value="{{ $s }}" @selected($current === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                    <button type="submit"
                            class="rounded-2xl px-4 py-3 font-extrabold text-white"
                            style="background: linear-gradient(135deg, var(--frs-primary), #0A3D7A);">
                        OK
                    </button>
                </div>
                <div class="mt-2 text-xs text-white/50">Une notification client sera créée lors du changement.</div>
            </form>
        </div>
    </div>

    <div class="rounded-3xl border border-white/10 bg-[var(--frs-card)] overflow-hidden">
        <div class="flex flex-col gap-2 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="font-extrabold tracking-wide">Produits commandés</div>
                <div class="text-sm text-white/50">{{ $lignes->count() }} ligne(s)</div>
            </div>
            <div class="text-sm font-extrabold text-white/80">Total {{ number_format((float)$commande->montant_total, 2, '.', ' ') }}</div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-white/60">
                    <tr>
                        <th class="text-left py-3 px-4 font-semibold">Produit</th>
                        <th class="text-right py-3 px-4 font-semibold">Qté</th>
                        <th class="text-right py-3 px-4 font-semibold">Prix unit</th>
                        <th class="text-right py-3 px-4 font-semibold">Sous-total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @foreach($lignes as $l)
                        <tr class="hover:bg-white/5">
                            <td class="py-3 px-4">
                                <div class="font-semibold break-words">{{ $l->produit_designation ?? 'Produit' }}</div>
                                <div class="text-xs text-white/60 break-words">{{ $l->produit_reference }}</div>
                            </td>
                            <td class="py-3 px-4 text-right font-extrabold whitespace-nowrap">{{ (int)$l->quantite }}</td>
                            <td class="py-3 px-4 text-right font-extrabold whitespace-nowrap">{{ number_format((float)$l->prix_unitaire, 2, '.', ' ') }}</td>
                            <td class="py-3 px-4 text-right font-extrabold whitespace-nowrap">{{ number_format((float)$l->sous_total, 2, '.', ' ') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t border-white/10">
                        <td colspan="3" class="py-4 px-4 text-right font-extrabold">Total</td>
                        <td class="py-4 px-4 text-right font-extrabold whitespace-nowrap">{{ number_format((float)$commande->montant_total, 2, '.', ' ') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
