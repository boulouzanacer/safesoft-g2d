@extends('layouts.admin')

@section('content')
@php($isUnlocked = (bool) ($is_unlocked ?? false))
@php($tables = $tables ?? [])

<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-5 py-4 text-sm text-emerald-100">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-2xl border border-red-400/20 bg-red-500/10 px-5 py-4 text-sm text-red-100">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[380px_minmax(0,1fr)]">
        <div class="rounded-2xl border border-white/10 bg-[var(--admin-card)] p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="text-2xl font-extrabold tracking-wide">{{ __('Base de données') }}</div>
                    <div class="mt-2 text-sm text-white/70">
                        {{ __('Déverrouillez cette rubrique avec le mot de passe pour rendre les tables sélectionnables avant réinitialisation.') }}
                    </div>
                </div>
                <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-bold {{ $isUnlocked ? 'border-emerald-400/20 bg-emerald-500/10 text-emerald-200' : 'border-amber-400/20 bg-amber-500/10 text-amber-100' }}">
                    {{ $isUnlocked ? __('Déverrouillé') : __('Verrouillé') }}
                </span>
            </div>

            <form method="POST" action="{{ url('/admin/base-de-donnees/unlock') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label for="databasePassword" class="mb-2 block text-sm font-semibold text-white/80">{{ __('Mot de passe') }}</label>
                    <input id="databasePassword"
                           type="password"
                           name="password"
                           autocomplete="off"
                           class="block w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3 text-sm text-white/90 outline-none focus:border-[var(--admin-primary)]"
                           placeholder="{{ __('Entrez le mot de passe') }}">
                    @error('password')
                        <div class="mt-2 text-sm text-red-300">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-2xl bg-[var(--admin-primary)] px-5 py-3 text-sm font-bold text-white shadow-lg hover:opacity-95">
                    <i class="fa-solid fa-unlock-keyhole"></i>
                    <span>{{ __('Déverrouiller les tables') }}</span>
                </button>
            </form>

            <div class="mt-6 rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-4 text-sm text-red-100">
                <div class="font-bold">{{ __('Zone sensible') }}</div>
                <div class="mt-2">
                    {{ __('Si vous videz une table, toutes ses lignes seront supprimées définitivement. Utilisez cette action seulement si vous êtes certain.') }}
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-white/10 bg-[var(--admin-card)] p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div class="text-xl font-extrabold tracking-wide">{{ __('Tables sélectionnables') }}</div>
                    <div class="mt-2 text-sm text-white/70">
                        {{ __('Cochez une ou plusieurs tables, puis cliquez sur Réinitialiser pour les vider complètement.') }}
                    </div>
                </div>
                <span class="inline-flex items-center rounded-full border border-sky-400/20 bg-sky-500/10 px-3 py-1 text-xs font-bold text-sky-200">
                    {{ count($tables) }} {{ __('table(s)') }}
                </span>
            </div>

            @if(! $isUnlocked)
                <div class="mt-8 rounded-2xl border border-white/10 bg-black/20 px-5 py-8 text-center text-sm text-white/60">
                    {{ __('Les tables apparaîtront ici dès que le mot de passe sera correct.') }}
                </div>
            @else
                <form method="POST"
                      action="{{ url('/admin/base-de-donnees/reset') }}"
                      class="mt-6 space-y-6"
                      data-confirm="{{ __('Confirmer la réinitialisation complète des tables sélectionnées ? Cette action est irréversible.') }}"
                      onsubmit="return confirm(this.dataset.confirm || '')">
                    @csrf

                    @error('tables')
                        <div class="rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-red-200">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach($tables as $table)
                            <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/20 px-4 py-3 text-sm text-white/85">
                                <input type="checkbox"
                                       name="tables[]"
                                       value="{{ $table }}"
                                       class="h-4 w-4 rounded border-white/20 bg-transparent text-[var(--admin-primary)] focus:ring-[var(--admin-primary)]">
                                <span class="keep-ltr-inline font-semibold">{{ $table }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-2xl bg-red-600 px-5 py-3 text-sm font-bold text-white shadow-lg hover:bg-red-500">
                            <i class="fa-solid fa-trash-can"></i>
                            <span>{{ __('Réinitialiser') }}</span>
                        </button>
                        <span class="text-xs text-white/50">
                            {{ __('Les tables cochées seront vidées entièrement, y compris leurs identifiants auto-incrémentés selon le moteur de base.') }}
                        </span>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
