@extends('layouts.admin')

@section('content')
<div class="hidden js-admin-fournisseur-edit-config"
     data-created-token-open="{{ session('created_token') ? '1' : '0' }}"
     data-created-token="{{ e(session('created_token', '')) }}"></div>

<div x-data="adminFournisseurEditPage()" class="max-w-3xl">
    <div class="flex items-center justify-between mb-4">
        <div>
            <div class="text-2xl font-extrabold tracking-wide">Éditer boutique</div>
            <div class="text-sm text-white/60">{{ $frs->nom_frs }}</div>
        </div>
        <a href="{{ url('/admin/fournisseurs') }}"
           class="rounded-2xl px-4 py-3 font-bold border border-white/10 hover:bg-white/10">
            Retour
        </a>
    </div>

    <div class="rounded-2xl border border-white/10 bg-[var(--admin-card)] p-6">
        <form method="POST" action="{{ url('/admin/fournisseurs/'.$frs->id) }}" enctype="multipart/form-data">
            @method('PUT')
            @include('admin.fournisseurs._form', ['isEdit' => true, 'frs' => $frs])

            <div class="mt-6 flex justify-end">
                <button type="submit"
                        class="rounded-2xl px-6 py-3 font-extrabold text-white"
                        style="background: linear-gradient(135deg, var(--admin-primary), #0A3D7A);">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>

    <div x-show="createdTokenOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-black/60" @click="createdTokenOpen=false"></div>
        <div class="relative w-full max-w-lg rounded-2xl border border-white/10 bg-[var(--admin-card)] p-6">
            <div class="flex items-center justify-between">
                <div class="font-extrabold tracking-wide">Token généré</div>
                <button type="button" class="text-white/60 hover:text-white" @click="createdTokenOpen=false">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="mt-3 text-sm text-white/70">
                Copie ce token maintenant. Il ne sera affiché qu'une seule fois.
            </div>
            <div class="mt-4 rounded-xl border border-white/10 bg-black/20 p-4 font-mono text-sm break-all" x-text="createdToken"></div>

            <div class="mt-4 flex justify-end gap-2">
                <button type="button"
                        class="rounded-xl px-4 py-2 font-bold border border-white/10 hover:bg-white/10"
                        @click="navigator.clipboard.writeText(createdToken)">
                    Copier
                </button>
                <button type="button"
                        class="rounded-xl px-4 py-2 font-bold text-white"
                        style="background: linear-gradient(135deg, var(--admin-primary), #0A3D7A);"
                        @click="createdTokenOpen=false">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    window.adminFournisseurEditPage = window.adminFournisseurEditPage || function () {
        const configElement = document.querySelector('.js-admin-fournisseur-edit-config');

        return {
            createdTokenOpen: configElement ? (configElement.dataset.createdTokenOpen || '0') === '1' : false,
            createdToken: configElement ? (configElement.dataset.createdToken || '') : '',
        };
    };
</script>
@endsection
