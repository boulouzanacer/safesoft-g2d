@extends('layouts.fournisseur')

@section('content')
<div class="max-w-4xl">
    <div class="flex items-center justify-between mb-4">
        <div>
            <div class="text-2xl font-extrabold tracking-wide">{{ __('Éditer produit') }}</div>
            <div class="text-sm text-white/60">{{ $produit->designation }} • <span class="keep-ltr-inline">{{ $produit->reference }}</span></div>
        </div>
        <a href="{{ url('/fournisseur/produits') }}"
           class="rounded-2xl px-4 py-3 font-bold border border-white/10 hover:bg-white/10">
            {{ __('Retour') }}
        </a>
    </div>

    <div class="rounded-2xl border border-white/10 bg-[var(--frs-card)] p-6">
        <form method="POST" action="{{ url('/fournisseur/produits/'.$produit->id) }}" enctype="multipart/form-data">
            @method('PUT')
            @include('fournisseur.produits._form', ['produit' => $produit, 'images' => $images])

            <div class="mt-6 flex justify-end">
                <button type="submit"
                        class="rounded-2xl px-6 py-3 font-extrabold text-white"
                        style="background: linear-gradient(135deg, var(--frs-primary), #0A3D7A);">
                    {{ __('Enregistrer') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
