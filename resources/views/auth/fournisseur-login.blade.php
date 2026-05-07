@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4"
     style="background: linear-gradient(135deg, #1A1A2E 0%, #0A3D7A 100%);">
    <div class="w-full max-w-md">
        <div class="text-center mb-6 text-white">
            <div class="text-3xl font-extrabold tracking-wide">SafeSoft G2D</div>
            <div class="text-sm opacity-90 mt-1">Espace Fournisseur</div>
        </div>

        <div class="bg-white shadow-2xl rounded-2xl p-8">
            @if($errors->any())
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ url('/fournisseur/login') }}" class="space-y-4" x-data="{ show: false }">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Email</label>
                    <input name="email"
                           type="email"
                           value="{{ old('email') }}"
                           required
                           autocomplete="email"
                           class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-[#1E6FD9] focus:ring-2 focus:ring-blue-200" />
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Password</label>
                    <div class="relative">
                        <input name="password"
                               :type="show ? 'text' : 'password'"
                               required
                               autocomplete="current-password"
                               class="w-full rounded-xl border border-slate-200 px-4 py-3 pr-12 outline-none focus:border-[#1E6FD9] focus:ring-2 focus:ring-blue-200" />
                        <button type="button"
                                class="absolute inset-y-0 right-0 px-4 text-slate-500 hover:text-slate-700"
                                @click="show = !show">
                            <i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>

                <button type="submit"
                        class="w-full rounded-xl py-3 font-bold text-white shadow-lg"
                        style="background: linear-gradient(135deg, #1E6FD9 0%, #0A3D7A 100%);">
                    Se connecter
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ url('/admin/login') }}" class="text-sm text-slate-500 hover:text-slate-700">
                    Accès Administration
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
