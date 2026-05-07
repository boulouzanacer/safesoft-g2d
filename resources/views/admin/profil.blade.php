@extends('layouts.admin')

@section('content')
@php($admin = \App\Models\Admin::find(session('admin_id')))
<div class="rounded-2xl border border-white/10 bg-[var(--admin-card)] p-6">
    <div class="text-2xl font-extrabold tracking-wide">Profil</div>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
            <div class="text-white/60">Nom</div>
            <div class="font-bold mt-1">{{ $admin?->nom }}</div>
        </div>
        <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
            <div class="text-white/60">Prénom</div>
            <div class="font-bold mt-1">{{ $admin?->prenom }}</div>
        </div>
        <div class="rounded-2xl border border-white/10 bg-black/20 p-4 md:col-span-2">
            <div class="text-white/60">Email</div>
            <div class="font-bold mt-1">{{ $admin?->email }}</div>
        </div>
    </div>
</div>
@endsection

