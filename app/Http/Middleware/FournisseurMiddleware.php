<?php

namespace App\Http\Middleware;

use App\Models\Fournisseur;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FournisseurMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->get('role') !== 'fournisseur' || ! $request->session()->has('frs_id')) {
            return redirect()->to('/fournisseur/login');
        }

        $frs = Fournisseur::query()->find($request->session()->get('frs_id'));
        if (! $frs) {
            $request->session()->forget(['role', 'frs_id']);

            return redirect()->to('/fournisseur/login');
        }

        $frs->syncExpirationStatus();

        if ($frs->isExpired() || (int) $frs->actif !== 1) {
            $request->session()->forget(['role', 'frs_id']);
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->to('/fournisseur/login')
                ->withErrors(['email' => 'Compte fournisseur expire ou desactive.']);
        }

        return $next($request);
    }
}
