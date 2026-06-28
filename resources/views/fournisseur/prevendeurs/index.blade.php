@extends('layouts.fournisseur')

@section('content')
@php
    $editing = $editing_prevendeur;
@endphp

<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="rounded-2xl p-5 border border-white/10 bg-[var(--frs-card)]">
            <div class="text-sm text-white/60">Total prevendeurs</div>
            <div class="mt-2 text-3xl font-extrabold">{{ $prevendeurs_count }}</div>
        </div>
        <div class="rounded-2xl p-5 border border-white/10 bg-[var(--frs-card)]">
            <div class="text-sm text-white/60">Prevendeurs actifs</div>
            <div class="mt-2 text-3xl font-extrabold">{{ $prevendeurs_actifs_count }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 rounded-3xl border border-white/10 bg-[var(--frs-card)] p-5 md:p-6">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <div class="font-extrabold tracking-wide">{{ $editing ? 'Modifier un prevendeur' : 'Nouveau prevendeur' }}</div>
                    <div class="text-sm text-white/60">Chaque client pourra ensuite etre affecte a un seul prevendeur.</div>
                </div>

                @if($editing)
                    <a href="{{ url('/fournisseur/prevendeurs') }}"
                       class="rounded-2xl px-4 py-3 text-sm font-bold border border-white/10 hover:bg-white/10">
                        Annuler l'edition
                    </a>
                @endif
            </div>

            <form method="POST" action="{{ $editing ? url('/fournisseur/prevendeurs/'.$editing->id) : url('/fournisseur/prevendeurs') }}" class="mt-6 space-y-5">
                @csrf
                @if($editing)
                    @method('PUT')
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-2">Nom</label>
                        <input type="text" name="nom" value="{{ old('nom', $editing?->nom) }}"
                               class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3"
                               placeholder="Ex: Ahmed Benali">
                        @error('nom') <div class="mt-1 text-sm text-red-300">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Telephone</label>
                        <input type="text" name="telephone" value="{{ old('telephone', $editing?->telephone) }}"
                               class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3"
                               placeholder="Numero de telephone">
                        @error('telephone') <div class="mt-1 text-sm text-red-300">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email', $editing?->email) }}"
                               class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3"
                               placeholder="email@example.com">
                        @error('email') <div class="mt-1 text-sm text-red-300">{{ $message }}</div> @enderror
                    </div>

                    <div class="flex items-center gap-3 pt-8">
                        <input id="actif" type="checkbox" name="actif" value="1"
                               @checked((int) old('actif', $editing?->actif ?? 1) === 1)
                               class="h-4 w-4 rounded border-white/10 bg-black/20">
                        <label for="actif" class="text-sm font-semibold">Actif</label>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold mb-2">Notes</label>
                        <textarea name="notes" rows="4"
                                  class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3"
                                  placeholder="Informations complementaires...">{{ old('notes', $editing?->notes) }}</textarea>
                        @error('notes') <div class="mt-1 text-sm text-red-300">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button type="submit"
                            class="rounded-2xl px-5 py-3 font-bold text-white"
                            style="background: linear-gradient(135deg, var(--frs-primary), #0A3D7A);">
                        {{ $editing ? 'Mettre a jour' : 'Creer le prevendeur' }}
                    </button>
                </div>
            </form>
        </div>

        <div class="rounded-3xl border border-white/10 bg-[var(--frs-card)] p-5 md:p-6">
            <form method="GET" action="{{ url('/fournisseur/prevendeurs') }}" class="space-y-3">
                <div class="font-extrabold tracking-wide">Recherche</div>
                <input name="q"
                       value="{{ $q }}"
                       placeholder="Nom, telephone ou email..."
                       class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                <button class="w-full rounded-2xl px-4 py-3 font-bold text-white"
                        style="background: linear-gradient(135deg, var(--frs-primary), #0A3D7A);">
                    Filtrer
                </button>
            </form>
        </div>
    </div>

    <div class="rounded-3xl border border-white/10 bg-[var(--frs-card)] overflow-hidden">
        <div class="px-5 py-4">
            <div class="font-extrabold tracking-wide">Liste des prevendeurs</div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-white/60">
                    <tr>
                        <th class="text-left py-3 px-4 font-semibold">Nom</th>
                        <th class="text-left py-3 px-4 font-semibold">Telephone</th>
                        <th class="text-left py-3 px-4 font-semibold">Email</th>
                        <th class="text-right py-3 px-4 font-semibold">Clients</th>
                        <th class="text-left py-3 px-4 font-semibold">Etat</th>
                        <th class="text-right py-3 px-4 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse($prevendeurs as $prevendeur)
                        <tr class="hover:bg-white/5">
                            <td class="py-3 px-4 font-bold">{{ $prevendeur->nom }}</td>
                            <td class="py-3 px-4 text-white/80">{{ $prevendeur->telephone ?: '-' }}</td>
                            <td class="py-3 px-4 text-white/80">{{ $prevendeur->email ?: '-' }}</td>
                            <td class="py-3 px-4 text-right font-extrabold">{{ (int) $prevendeur->clients_count }}</td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold {{ $prevendeur->actif ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-400/20' : 'bg-red-500/15 text-red-300 border border-red-400/20' }}">
                                    {{ $prevendeur->actif ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ url('/fournisseur/prevendeurs?edit='.$prevendeur->id) }}"
                                       class="rounded-xl px-3 py-2 text-xs font-bold border border-white/10 hover:bg-white/10">
                                        Editer
                                    </a>
                                    <form method="POST" action="{{ url('/fournisseur/prevendeurs/'.$prevendeur->id.'/toggle') }}">
                                        @csrf
                                        <button type="submit"
                                                class="rounded-xl px-3 py-2 text-xs font-bold border border-white/10 hover:bg-white/10">
                                            {{ $prevendeur->actif ? 'Desactiver' : 'Activer' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-white/60">Aucun prevendeur enregistre.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4">
            {{ $prevendeurs->links() }}
        </div>
    </div>
</div>
@endsection
