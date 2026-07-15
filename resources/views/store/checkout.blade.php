@extends('store.layout')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="text-2xl font-extrabold tracking-wide">Finaliser la commande</div>
            <div class="store-muted mt-1 text-sm">
                @if($boutique)
                    Boutique: <span class="font-semibold" style="color: var(--store-text);">{{ $boutique->nom_frs }}</span>
                @endif
            </div>
        </div>
        <a href="{{ url('/panier') }}" class="store-muted text-sm hover:opacity-90">
            <i class="fa-solid fa-arrow-left-long mr-2"></i>
            Retour panier
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="store-panel lg:col-span-2 p-6">
            <div class="text-lg font-extrabold tracking-wide">Adresse de livraison</div>
            <form method="POST" action="{{ url('/checkout') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold mb-1" style="color: var(--store-text);">Adresse</label>
                    <input name="adresse_livraison"
                           value="{{ old('adresse_livraison', $client->adresse ?? '') }}"
                           class="store-input w-full px-4 py-3"
                           required>
                    @error('adresse_livraison')
                        <div class="mt-1 text-xs text-red-700">{{ $message }}</div>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1" style="color: var(--store-text);">Wilaya</label>
                        <select id="wilayaSelect"
                                name="id_wilaya"
                                class="store-input w-full px-4 py-3"
                                required>
                            @foreach($wilayas as $w)
                                <option value="{{ $w->ID_WILAYA }}"
                                        @selected((int)old('id_wilaya', $selected_wilaya) === (int)$w->ID_WILAYA)>
                                    {{ $w->ID_WILAYA }} - {{ $w->WILAYA }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_wilaya')
                            <div class="mt-1 text-xs text-red-700">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1" style="color: var(--store-text);">Commune</label>
                        <select id="communeSelect"
                                name="id_commune"
                                class="store-input w-full px-4 py-3"
                                required>
                            @foreach($communes as $c)
                                <option value="{{ $c->ID_COMMUNE }}"
                                        @selected((int)old('id_commune', $client->id_commune ?? 0) === (int)$c->ID_COMMUNE)>
                                    {{ $c->COMMUNE }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_commune')
                            <div class="mt-1 text-xs text-red-700">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1" style="color: var(--store-text);">Notes (optionnel)</label>
                    <textarea name="notes"
                              rows="4"
                              class="store-input w-full px-4 py-3">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="mt-1 text-xs text-red-700">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit"
                        class="store-button-primary w-full inline-flex items-center justify-center gap-2 rounded-2xl px-4 py-3 text-sm font-extrabold">
                    <i class="fa-solid fa-cart-shopping"></i>
                    Confirmer la commande
                </button>
            </form>
        </div>

        <div class="store-panel p-6 h-fit">
            <div class="text-lg font-extrabold tracking-wide">Récapitulatif</div>
            <div class="mt-4 space-y-3">
                @foreach($items as $it)
                    @php($p = $it['produit'])
                    <div class="flex items-start justify-between gap-3 text-sm">
                        <div class="min-w-0">
                            <div class="font-bold truncate">{{ $p->designation }}</div>
                            <div class="store-muted">x{{ (int)$it['qty'] }}</div>
                        </div>
                        <div class="font-extrabold">{{ number_format((float)$it['line_total'], 2, '.', ' ') }} DA</div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 pt-4 border-t flex items-center justify-between" style="border-color: var(--store-border);">
                <span class="store-muted">Total</span>
                <span class="font-extrabold" style="color: var(--store-text);">{{ number_format((float)$total, 2, '.', ' ') }} DA</span>
            </div>

            <div class="store-muted mt-3 text-xs">
                Paiement à la livraison.
            </div>
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
