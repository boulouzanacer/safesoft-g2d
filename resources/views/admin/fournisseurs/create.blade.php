@extends('layouts.admin')

@section('content')
<div class="max-w-3xl">
    <div class="flex items-center justify-between mb-4">
        <div>
            <div class="text-2xl font-extrabold tracking-wide">Créer un fournisseur</div>
            <div class="text-sm text-white/60">Le token sera généré automatiquement.</div>
        </div>
        <a href="{{ url('/admin/fournisseurs') }}"
           class="rounded-2xl px-4 py-3 font-bold border border-white/10 hover:bg-white/10">
            Retour
        </a>
    </div>

    <div class="rounded-2xl border border-white/10 bg-[var(--admin-card)] p-6">
        <form method="POST" action="{{ url('/admin/fournisseurs') }}" enctype="multipart/form-data">
            @include('admin.fournisseurs._form', ['isEdit' => false])

            <div class="mt-6 flex justify-end">
                <button type="submit"
                        class="rounded-2xl px-6 py-3 font-extrabold text-white"
                        style="background: linear-gradient(135deg, var(--admin-primary), #0A3D7A);">
                    Créer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
