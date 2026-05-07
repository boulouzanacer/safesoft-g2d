<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Fournisseur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class FrsAuthController extends Controller
{
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $frs = Fournisseur::query()->where('email', $credentials['email'])->first();

        if (! $frs || ! Hash::check($credentials['password'], $frs->password)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Identifiants invalides.']);
        }

        if ((int) $frs->actif !== 1) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Compte désactivé']);
        }

        $request->session()->regenerate();

        $request->session()->put([
            'role' => 'fournisseur',
            'frs_id' => $frs->id,
        ]);

        return redirect()->to('/fournisseur/dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['role', 'frs_id']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to('/fournisseur/login');
    }
}
