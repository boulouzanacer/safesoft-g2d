@extends('layouts.fournisseur')

@section('content')
<div class="space-y-4">
    <form method="GET" action="{{ url('/fournisseur/clients') }}" class="flex flex-col md:flex-row md:items-center gap-3">
        <div class="relative w-full md:max-w-md">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-white/50"></i>
            <input name="q"
                   value="{{ $q }}"
                   placeholder="Rechercher nom, code, email ou téléphone..."
                   class="w-full rounded-2xl border border-white/10 bg-[var(--frs-card)] pl-11 pr-4 py-3 outline-none focus:border-[var(--frs-primary)]">
        </div>
        <button class="rounded-2xl px-4 py-3 font-bold text-white"
                style="background: linear-gradient(135deg, var(--frs-primary), #0A3D7A);">
            Filtrer
        </button>
        <a href="{{ url('/fournisseur/clients') }}"
           class="rounded-2xl px-4 py-3 font-bold border border-white/10 hover:bg-white/10">
            Reset
        </a>
    </form>

    <div class="rounded-2xl border border-white/10 bg-[var(--frs-card)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-white/60">
                    <tr>
                        <th class="text-left py-3 px-4 font-semibold">Code</th>
                        <th class="text-left py-3 px-4 font-semibold">Nom</th>
                        <th class="text-left py-3 px-4 font-semibold">Email</th>
                        <th class="text-left py-3 px-4 font-semibold">Téléphone</th>
                        <th class="text-left py-3 px-4 font-semibold">Type</th>
                        <th class="text-left py-3 px-4 font-semibold">Commune</th>
                        <th class="text-right py-3 px-4 font-semibold">Nb commandes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse($clients as $c)
                        <tr class="hover:bg-white/5 cursor-pointer"
                            onclick="window.location='{{ url('/fournisseur/clients/'.$c->id) }}'">
                            <td class="py-3 px-4 font-semibold">{{ $c->code_client ?? '-' }}</td>
                            <td class="py-3 px-4 text-white/80">{{ $c->prenom }} {{ $c->nom }}</td>
                            <td class="py-3 px-4 text-white/80">{{ $c->email }}</td>
                            <td class="py-3 px-4 text-white/80">{{ $c->telephone }}</td>
                            <td class="py-3 px-4">
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $c->type_client === 'abonne' ? 'bg-sky-500/15 text-sky-300 border border-sky-400/20' : 'bg-white/10 text-white/70 border border-white/10' }}">
                                    {{ $c->type_client }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-white/80">{{ $c->commune_nom ?? '-' }}</td>
                            <td class="py-3 px-4 text-right font-extrabold">{{ (int)$c->nb_commandes }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-white/60">Aucun client</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $clients->links() }}
    </div>
</div>
@endsection
