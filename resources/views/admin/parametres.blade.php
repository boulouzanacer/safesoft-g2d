@extends('layouts.admin')

@section('content')
@php($platformBranding = $platform_branding ?? [])
@php($platformLogoUrl = trim((string) ($platformBranding['logo_url'] ?? '')))
@php($platformName = trim((string) ($platformBranding['name'] ?? config('branding.platform_name'))))
@php($platformInitials = trim((string) ($platformBranding['initials'] ?? config('branding.platform_initials'))))

<div class="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_380px]">
    <div class="rounded-2xl border border-white/10 bg-[var(--admin-card)] p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="text-2xl font-extrabold tracking-wide">{{ __('Paramètres') }}</div>
                <div class="mt-2 max-w-2xl text-white/70">
                    {{ __('Gérez ici l\'identité visuelle globale de la plateforme. Ce logo s\'affiche dans l\'administration, l\'espace Boutique et les écrans de connexion quand aucune identité boutique spécifique ne doit prendre le dessus.') }}
                </div>
            </div>
            <span class="inline-flex items-center rounded-full border border-sky-400/20 bg-sky-500/10 px-3 py-1 text-xs font-bold text-sky-200">
                {{ __('Branding plateforme') }}
            </span>
        </div>

        <form method="POST"
              action="{{ url('/admin/parametres/logo') }}"
              enctype="multipart/form-data"
              class="mt-8 space-y-6">
            @csrf

            <div>
                <label class="mb-2 block text-sm font-semibold text-white/80">{{ __('Logo plateforme') }}</label>
                <input type="file"
                       name="logo"
                       accept=".jpg,.jpeg,.png,.webp,.svg"
                       class="block w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3 text-sm text-white/80 file:mr-4 file:rounded-xl file:border-0 file:bg-[var(--admin-primary)] file:px-4 file:py-2 file:font-semibold file:text-white hover:file:opacity-95">
                <div class="mt-2 text-xs text-white/50">
                    {{ __('Formats acceptés: JPG, PNG, WEBP, SVG. Taille maximale: 4 Mo.') }}
                </div>
                @error('logo')
                    <div class="mt-2 text-sm text-red-300">{{ $message }}</div>
                @enderror
            </div>

            <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/20 px-4 py-3 text-sm text-white/80">
                <input type="checkbox"
                       name="remove_logo"
                       value="1"
                       class="h-4 w-4 rounded border-white/20 bg-transparent text-[var(--admin-primary)] focus:ring-[var(--admin-primary)]">
                <span>{{ __('Supprimer le logo actuel et revenir aux initiales') }}</span>
            </label>

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-2xl bg-[var(--admin-primary)] px-5 py-3 text-sm font-bold text-white shadow-lg hover:opacity-95">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>{{ __('Enregistrer le logo') }}</span>
                </button>
                <span class="text-xs text-white/50">
                    {{ __('Si vous chargez un nouveau logo, il remplace automatiquement l\'ancien.') }}
                </span>
            </div>
        </form>
    </div>

    <div class="rounded-2xl border border-white/10 bg-[var(--admin-card)] p-6">
        <div class="text-sm font-bold uppercase tracking-[0.25em] text-white/50">{{ __('Aperçu') }}</div>
        <div class="mt-5 rounded-3xl border border-white/10 bg-black/20 p-5">
            <div class="flex items-center gap-4">
                @if($platformLogoUrl !== '')
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl border border-white/10 bg-white p-2">
                        <img src="{{ $platformLogoUrl }}"
                             alt="{{ $platformName }}"
                             class="max-h-full max-w-full object-contain">
                    </div>
                @else
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-[var(--admin-primary)] to-[#0A3D7A] text-xl font-extrabold text-white">
                        {{ $platformInitials }}
                    </div>
                @endif

                <div>
                    <div class="text-lg font-extrabold tracking-wide">{{ $platformName }}</div>
                    <div class="mt-1 text-sm text-white/60">{{ __('Administration / Espace Boutique') }}</div>
                </div>
            </div>
        </div>

        <div class="mt-6 rounded-2xl border border-amber-400/20 bg-amber-500/10 px-4 py-4 text-sm text-amber-100">
            <div class="font-bold">{{ __('Important') }}</div>
            <div class="mt-2">
                {{ __('Le logo plateforme sert uniquement d\'identité globale. Les logos propres aux boutiques continuent de s\'afficher dans les storefronts et les fiches boutique.') }}
            </div>
        </div>
    </div>
</div>
@endsection
