@extends('store.layout')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="text-2xl font-extrabold tracking-wide">Mes commandes</div>
            <div class="mt-1 text-sm text-slate-600">Historique des commandes</div>
        </div>
        <a href="{{ url('/') }}" class="text-sm text-slate-500 hover:text-slate-900">
            <i class="fa-solid fa-store mr-2"></i>
            Retour store
        </a>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-[var(--store-card)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-slate-500">
                    <tr>
                        <th class="text-left py-3 px-4 font-semibold">#</th>
                        <th class="text-left py-3 px-4 font-semibold">Date</th>
                        <th class="text-left py-3 px-4 font-semibold">Boutique</th>
                        <th class="text-left py-3 px-4 font-semibold">Statut</th>
                        <th class="text-right py-3 px-4 font-semibold">Total</th>
                        <th class="text-right py-3 px-4 font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($commandes as $c)
                        @php
                            $statut = (string)$c->statut;
                            $badge = match($statut) {
                                'en_attente' => 'bg-amber-50 text-amber-700 border border-amber-200',
                                'confirmee' => 'bg-sky-50 text-sky-700 border border-sky-200',
                                'expediee' => 'bg-indigo-50 text-indigo-700 border border-indigo-200',
                                'livree' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                'annulee' => 'bg-red-50 text-red-700 border border-red-200',
                                default => 'bg-slate-50 text-slate-600 border border-slate-200'
                            };
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-4 font-semibold">#{{ $c->id }}</td>
                            <td class="py-3 px-4 text-slate-700">{{ \Illuminate\Support\Carbon::parse($c->date_cmd)->format('d/m/Y H:i') }}</td>
                            <td class="py-3 px-4 text-slate-700">{{ $c->frs_nom ?? '—' }}</td>
                            <td class="py-3 px-4">
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $badge }}">{{ $statut }}</span>
                            </td>
                            <td class="py-3 px-4 text-right font-bold">{{ number_format((float)$c->montant_total, 2, '.', ' ') }} DA</td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ url('/mes-commandes/'.$c->id) }}"
                                   class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-xs font-extrabold text-white"
                                   style="background: linear-gradient(135deg, var(--store-primary), #0A3D7A);">
                                    Détail
                                    <i class="fa-solid fa-arrow-right-long"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-600">Aucune commande</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $commandes->links() }}
    </div>
</div>
@endsection
