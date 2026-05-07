<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class OptionalSanctumAuth
{
    public function handle(Request $request, Closure $next)
    {
        $authHeader = (string) $request->header('Authorization', '');
        if (! str_starts_with($authHeader, 'Bearer ')) {
            return $next($request);
        }

        $plainTextToken = trim(substr($authHeader, 7));
        if ($plainTextToken === '') {
            return $next($request);
        }

        $accessToken = PersonalAccessToken::findToken($plainTextToken);
        if (! $accessToken) {
            return $next($request);
        }

        $user = $accessToken->tokenable;
        if ($user) {
            $accessToken->forceFill(['last_used_at' => now()])->save();
            Auth::setUser($user);
            $request->setUserResolver(fn () => $user);
        }

        return $next($request);
    }
}

