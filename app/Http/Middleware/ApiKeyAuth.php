<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use App\Traits\ApiResponseTrait;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyAuth
{
    use ApiResponseTrait;

    public function handle(Request $request, Closure $next, string $type): Response
    {
        $value = $this->extractApiKey($request);

        if ($value === '') {
            return $this->unauthorized('Api key requise');
        }

        $apiKey = ApiKey::query()
            ->where('api_key', $value)
            ->where('type', $type)
            ->where('actif', true)
            ->first();

        if (! $apiKey) {
            return $this->unauthorized('Api key invalide');
        }

        $apiKey->forceFill([
            'last_used_at' => now(),
        ])->save();

        $request->attributes->set('api_key', $apiKey);

        return $next($request);
    }

    protected function extractApiKey(Request $request): string
    {
        $headerValue = trim((string) $request->header('X-API-KEY', ''));
        if ($headerValue !== '') {
            return $headerValue;
        }

        $authorization = trim((string) $request->header('Authorization', ''));
        if (str_starts_with($authorization, 'Bearer ')) {
            return trim(substr($authorization, 7));
        }

        return '';
    }
}
