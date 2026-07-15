<?php

namespace App\Http\Middleware;

use App\Models\CustomDomain;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveStorefrontDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = mb_strtolower(trim((string) $request->getHost()));

        if ($host === '' || $this->isPlatformHost($host)) {
            return $next($request);
        }

        $domain = CustomDomain::query()
            ->with(['fournisseur' => function ($query) {
                $query->where('actif', 1)
                    ->whereNull('deleted_at')
                    ->with('boutiqueCategory:id,name');
            }])
            ->where('domain', $host)
            ->where('is_active', 1)
            ->first();

        if (! $domain || ! $domain->fournisseur) {
            return $next($request);
        }

        if (! $domain->verified_at) {
            $domain->forceFill(['verified_at' => now()])->save();
        }

        $request->attributes->set('custom_storefront_domain', $domain);
        $request->attributes->set('custom_storefront_boutique', $domain->fournisseur);

        return $next($request);
    }

    private function isPlatformHost(string $host): bool
    {
        $platformHosts = array_filter([
            mb_strtolower(trim((string) parse_url((string) config('app.url'), PHP_URL_HOST))),
            'g2d-dz.com',
            'www.g2d-dz.com',
            'localhost',
            '127.0.0.1',
        ]);

        return in_array($host, array_values(array_unique($platformHosts)), true);
    }
}
