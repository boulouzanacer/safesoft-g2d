<?php

namespace App\Http\Controllers\Fournisseur;

use App\Http\Controllers\Controller;
use App\Models\Commune;
use App\Models\Fournisseur;
use App\Models\Wilaya;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $frs = Fournisseur::query()->findOrFail((int) session('frs_id'));

        $wilayas = Wilaya::query()->orderBy('ID_WILAYA')->get();
        $communes = Commune::query()
            ->where('ID_WILAYA', $frs->id_wilaya)
            ->orderBy('COMMUNE')
            ->get();

        return view('fournisseur.profil', [
            'title' => 'Mon Profil',
            'frs' => $frs,
            'wilayas' => $wilayas,
            'communes' => $communes,
            'storefront_theme_options' => Fournisseur::storefrontThemeOptions(),
            'default_storefront_theme' => Fournisseur::DEFAULT_STOREFRONT_THEME,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $frs = Fournisseur::query()->findOrFail((int) session('frs_id'));

        $data = $request->validate([
            'nom_frs' => ['required', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_logo' => ['nullable', 'boolean'],
            'adresse' => ['required', 'string'],
            'id_wilaya' => ['required', 'integer', 'exists:wilaya,ID_WILAYA'],
            'id_commune' => ['required', 'integer', 'exists:commune,ID_COMMUNE'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_visible' => ['nullable', 'boolean'],
            'storefront_theme' => ['nullable', 'string', 'in:'.implode(',', array_keys(Fournisseur::storefrontThemeOptions()))],
        ]);

        $payload = [
            'nom_frs' => $data['nom_frs'],
            'telephone' => $data['telephone'] ?? null,
            'adresse' => $data['adresse'],
            'id_wilaya' => (int) $data['id_wilaya'],
            'id_commune' => (int) $data['id_commune'],
            'latitude' => array_key_exists('latitude', $data) ? (float) $data['latitude'] : null,
            'longitude' => array_key_exists('longitude', $data) ? (float) $data['longitude'] : null,
            'is_visible' => (int) ($data['is_visible'] ?? 0) === 1 ? 1 : 0,
            'storefront_theme' => Fournisseur::normalizeStorefrontTheme($data['storefront_theme'] ?? null),
        ];

        if ((int) ($data['remove_logo'] ?? 0) === 1) {
            if (! empty($frs->logo_path)) {
                Storage::disk('public')->delete($frs->logo_path);
            }
            $payload['logo_path'] = null;
        }

        if ($request->hasFile('logo')) {
            if (! empty($frs->logo_path)) {
                Storage::disk('public')->delete($frs->logo_path);
            }
            $ext = strtolower((string) $request->file('logo')->getClientOriginalExtension());
            if ($ext === '') {
                $ext = 'jpg';
            }
            $path = $request->file('logo')->storeAs(
                "frs/{$frs->id}",
                'logo_'.now()->timestamp.'.'.$ext,
                'public'
            );
            $payload['logo_path'] = $path;
        }

        $frs->update($payload);

        return back()->with('success', 'Profil mis à jour.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $frs = Fournisseur::query()->findOrFail((int) session('frs_id'));

        $data = $request->validate([
            'old_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($data['old_password'], $frs->password)) {
            return back()->withErrors(['old_password' => 'Ancien mot de passe incorrect.']);
        }

        $frs->update(['password' => Hash::make($data['password'])]);

        return back()->with('success', 'Mot de passe mis à jour.');
    }

    public function storeCustomDomain(Request $request): RedirectResponse
    {
        $frs = Fournisseur::query()->findOrFail((int) session('frs_id'));
        $normalizedDomain = $this->normalizeDomain((string) $request->input('domain', ''));

        if (! $this->isValidDomain($normalizedDomain)) {
            return back()->withErrors([
                'domain' => 'Le domaine saisi est invalide. Exemple: www.boutika.com',
            ])->withInput();
        }

        $exists = CustomDomain::query()
            ->where('domain', $normalizedDomain)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'domain' => 'Ce domaine est deja utilise par une autre boutique.',
            ])->withInput();
        }

        $hasPrimary = $frs->customDomains()->where('is_primary', 1)->exists();

        $frs->customDomains()->create([
            'domain' => $normalizedDomain,
            'is_primary' => ! $hasPrimary,
            'is_active' => 1,
        ]);

        return back()->with('success', 'Domaine personnalisé ajouté.');
    }

    public function makeCustomDomainPrimary(int $id): RedirectResponse
    {
        $frsId = (int) session('frs_id');
        $domain = \App\Models\CustomDomain::query()
            ->where('fournisseur_id', $frsId)
            ->findOrFail($id);

        CustomDomain::query()
            ->where('fournisseur_id', $frsId)
            ->update(['is_primary' => 0]);

        $domain->forceFill([
            'is_primary' => 1,
            'is_active' => 1,
        ])->save();

        return back()->with('success', 'Domaine principal mis à jour.');
    }

    public function destroyCustomDomain(int $id): RedirectResponse
    {
        $frsId = (int) session('frs_id');
        $domain = CustomDomain::query()
            ->where('fournisseur_id', $frsId)
            ->findOrFail($id);

        $wasPrimary = (bool) $domain->is_primary;
        $domain->delete();

        if ($wasPrimary) {
            $replacement = CustomDomain::query()
                ->where('fournisseur_id', $frsId)
                ->orderBy('domain')
                ->first();

            if ($replacement) {
                $replacement->forceFill(['is_primary' => 1])->save();
            }
        }

        return back()->with('success', 'Domaine personnalisé supprimé.');
    }

    public function communes(int $idWilaya): JsonResponse
    {
        $rows = DB::table('commune')
            ->where('ID_WILAYA', $idWilaya)
            ->orderBy('COMMUNE')
            ->get(['ID_COMMUNE', 'COMMUNE']);

        return response()->json($rows);
    }

    private function normalizeDomain(string $value): string
    {
        $normalized = mb_strtolower(trim($value));

        if ($normalized === '') {
            return '';
        }

        if (str_starts_with($normalized, 'http://') || str_starts_with($normalized, 'https://')) {
            $normalized = (string) parse_url($normalized, PHP_URL_HOST);
        }

        $normalized = trim($normalized, "/ \t\n\r\0\x0B.");
        $normalized = preg_replace('/\/.*$/', '', $normalized) ?? $normalized;

        return mb_strtolower(trim((string) $normalized));
    }

    private function isValidDomain(string $domain): bool
    {
        if ($domain === '') {
            return false;
        }

        return (bool) preg_match('/^(?=.{4,190}$)(?!-)(?:[a-z0-9-]{1,63}\.)+[a-z]{2,63}$/i', $domain);
    }
}
