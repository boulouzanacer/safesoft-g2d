<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Commune;
use App\Models\Wilaya;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ClientAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.client-login', ['title' => 'Connexion']);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $client = Client::query()->where('email', $credentials['email'])->first();

        if (! $client || ! Hash::check($credentials['password'], $client->password)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Identifiants invalides.']);
        }

        if ((int) $client->actif !== 1) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Compte désactivé.']);
        }

        $request->session()->regenerate();
        $request->session()->put([
            'role' => 'client',
            'client_id' => $client->id,
        ]);

        return redirect()->intended('/');
    }

    public function showRegister(): View
    {
        $wilayas = Wilaya::query()->orderBy('ID_WILAYA')->get(['ID_WILAYA', 'WILAYA']);
        $defaultWilaya = (int) ($wilayas->first()?->ID_WILAYA ?? 1);
        $communes = Commune::query()
            ->where('ID_WILAYA', $defaultWilaya)
            ->orderBy('COMMUNE')
            ->get(['ID_COMMUNE', 'COMMUNE', 'ID_WILAYA']);

        return view('auth.client-register', [
            'title' => 'Créer un compte',
            'wilayas' => $wilayas,
            'communes' => $communes,
            'default_wilaya' => $defaultWilaya,
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:client,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'telephone' => ['nullable', 'string', 'max:255'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'id_wilaya' => ['nullable', 'integer', 'exists:wilaya,ID_WILAYA'],
            'id_commune' => ['nullable', 'integer', 'exists:commune,ID_COMMUNE'],
        ]);

        $idWilaya = isset($data['id_wilaya'])
            ? (int) $data['id_wilaya']
            : (int) (DB::table('wilaya')->min('ID_WILAYA') ?? 1);

        $idCommune = isset($data['id_commune'])
            ? (int) $data['id_commune']
            : (int) (DB::table('commune')->where('ID_WILAYA', $idWilaya)->min('ID_COMMUNE') ?? 1);

        $client = Client::create([
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'telephone' => $data['telephone'] ?? null,
            'adresse' => $data['adresse'] ?? '',
            'id_wilaya' => $idWilaya,
            'id_commune' => $idCommune,
            'type_client' => 'simple',
            'id_frs' => null,
            'actif' => 1,
        ]);

        $request->session()->regenerate();
        $request->session()->put([
            'role' => 'client',
            'client_id' => $client->id,
        ]);

        return redirect()->to('/')->with('success', 'Compte créé.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['role', 'client_id']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to('/');
    }
}

