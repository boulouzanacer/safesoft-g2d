<?php

namespace App\Http\Middleware;

use App\Models\Fournisseur;
use App\Traits\ApiResponseTrait;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PmeTokenAuth
{
    use ApiResponseTrait;

    public function handle(Request $request, Closure $next): Response
    {
        $auth = (string) $request->header('Authorization', '');

        if (! str_starts_with($auth, 'Bearer ')) {
            return $this->unauthorized('Non autorisé');
        }

        $token = trim(substr($auth, 7));
        if ($token === '') {
            return $this->unauthorized('Non autorisé');
        }

        $frs = Fournisseur::query()
            ->where('token', $token)
            ->where('actif', 1)
            ->first();

        if (! $frs) {
            return $this->unauthorized('Non autorisé');
        }

        $request->attributes->set('fournisseur', $frs);

        return $next($request);
    }
}

