@extends('store.layout')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="rounded-2xl border border-slate-200 bg-[var(--store-card)] p-6">
        <div class="text-2xl font-extrabold tracking-wide">Créer un compte</div>
        <div class="mt-1 text-sm text-slate-600">Compte client simple (abonnement géré par l'administration).</div>

        @if(session('pending_client_id'))
            <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                Un code de vérification a été envoyé à <span class="font-bold">{{ session('pending_client_email') }}</span>.
            </div>

            <form method="POST" action="{{ url('/register/verify-email') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Code (6 chiffres)</label>
                    <input name="code"
                           inputmode="numeric"
                           autocomplete="one-time-code"
                           value="{{ old('code') }}"
                           class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-[var(--store-primary)]"
                           required>
                    @error('code')
                        <div class="mt-1 text-xs text-red-700">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-2xl px-4 py-3 text-sm font-extrabold text-white"
                        style="background: linear-gradient(135deg, var(--store-primary), #0A3D7A);">
                    <i class="fa-solid fa-shield-halved"></i>
                    Vérifier mon email
                </button>
            </form>

            <form method="POST" action="{{ url('/register/resend-email-code') }}" class="mt-3">
                @csrf
                <button type="submit"
                        class="w-full rounded-2xl px-4 py-3 font-bold border border-slate-200 hover:bg-slate-50">
                    Renvoyer le code
                </button>
            </form>
        @else
            <form method="POST" action="{{ url('/register') }}" class="mt-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Nom</label>
                        <input name="nom"
                               value="{{ old('nom') }}"
                               class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-[var(--store-primary)]"
                               required>
                        @error('nom')
                            <div class="mt-1 text-xs text-red-700">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Prénom</label>
                        <input name="prenom"
                               value="{{ old('prenom') }}"
                               class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-[var(--store-primary)]"
                               required>
                        @error('prenom')
                            <div class="mt-1 text-xs text-red-700">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Email</label>
                        <input name="email"
                               type="email"
                               value="{{ old('email') }}"
                               class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-[var(--store-primary)]"
                               required>
                        @error('email')
                            <div class="mt-1 text-xs text-red-700">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Téléphone</label>
                        <input name="telephone"
                               value="{{ old('telephone') }}"
                               class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-[var(--store-primary)]"
                               required>
                        @error('telephone')
                            <div class="mt-1 text-xs text-red-700">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Adresse (optionnel)</label>
                    <input name="adresse"
                           value="{{ old('adresse') }}"
                           class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-[var(--store-primary)]">
                    @error('adresse')
                        <div class="mt-1 text-xs text-red-700">{{ $message }}</div>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Wilaya</label>
                        <select id="wilayaSelect"
                                name="id_wilaya"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-[var(--store-primary)]">
                            @foreach($wilayas as $w)
                                <option value="{{ $w->ID_WILAYA }}"
                                        @selected((int)old('id_wilaya', $default_wilaya) === (int)$w->ID_WILAYA)>
                                    {{ $w->ID_WILAYA }} - {{ $w->WILAYA }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_wilaya')
                            <div class="mt-1 text-xs text-red-700">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Commune</label>
                        <select id="communeSelect"
                                name="id_commune"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-[var(--store-primary)]">
                            @foreach($communes as $c)
                                <option value="{{ $c->ID_COMMUNE }}"
                                        @selected((int)old('id_commune') === (int)$c->ID_COMMUNE)>
                                    {{ $c->COMMUNE }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_commune')
                            <div class="mt-1 text-xs text-red-700">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Mot de passe</label>
                        <input name="password"
                               type="password"
                               class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-[var(--store-primary)]"
                               required>
                        @error('password')
                            <div class="mt-1 text-xs text-red-700">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Confirmer</label>
                        <input name="password_confirmation"
                               type="password"
                               class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-[var(--store-primary)]"
                               required>
                    </div>
                </div>

                <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-2xl px-4 py-3 text-sm font-extrabold text-white"
                        style="background: linear-gradient(135deg, var(--store-primary), #0A3D7A);">
                    <i class="fa-solid fa-user-plus"></i>
                    Créer mon compte
                </button>
            </form>
        @endif

        <div class="mt-4 text-sm text-slate-600">
            Déjà un compte ?
            <a href="{{ url('/login') }}" class="text-[var(--store-primary)] font-bold hover:underline">Se connecter</a>
        </div>
    </div>
</div>

<script>
(() => {
    const wilayaSelect = document.getElementById('wilayaSelect');
    const communeSelect = document.getElementById('communeSelect');
    if (!wilayaSelect || !communeSelect) return;

    const setLoading = (loading) => {
        communeSelect.disabled = loading;
        if (loading) {
            communeSelect.innerHTML = '<option value=\"\">Chargement...</option>';
        }
    };

    const loadCommunes = async (wilayaId) => {
        setLoading(true);
        try {
            const res = await fetch(`/api/v1/communes/${wilayaId}`, { headers: { 'Accept': 'application/json' } });
            const json = await res.json();
            const rows = (json && json.success && Array.isArray(json.data)) ? json.data : [];
            communeSelect.innerHTML = rows.map(c => `<option value=\"${c.ID_COMMUNE}\">${c.COMMUNE}</option>`).join('');
            setLoading(false);
        } catch (_) {
            communeSelect.innerHTML = '<option value=\"\">Erreur</option>';
            setLoading(false);
        }
    };

    wilayaSelect.addEventListener('change', () => {
        const id = wilayaSelect.value;
        if (!id) return;
        loadCommunes(id);
    });
})();
</script>
@endsection
