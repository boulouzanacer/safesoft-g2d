<?php

namespace App\Http\Middleware;

use App\Models\CustomDomain;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ResolveStorefrontDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = mb_strtolower(trim((string) $request->getHost()));

        // #region debug-point storefront-domain-loop:resolve
        $debug = $this->debugContext($host);
        if ($debug['enabled']) {
            $this->debugWrite([
                'ts' => now()->toIso8601String(),
                'session' => 'storefront-domain-loop',
                'req_id' => $debug['req_id'],
                'event' => 'resolve.enter',
                'host' => $host,
                'method' => $request->getMethod(),
                'uri' => $request->getRequestUri(),
                'is_secure' => $request->isSecure(),
                'x_forwarded_proto' => (string) $request->header('x-forwarded-proto', ''),
                'cf_visitor' => (string) $request->header('cf-visitor', ''),
                'app_url' => (string) config('app.url'),
                'url_root' => (string) url('/'),
            ]);
        }
        // #endregion debug-point storefront-domain-loop:resolve

        if ($host === '' || $this->isPlatformHost($host)) {
            $response = $next($request);

            // #region debug-point storefront-domain-loop:resolve
            if ($debug['enabled']) {
                $this->debugWrite([
                    'ts' => now()->toIso8601String(),
                    'session' => 'storefront-domain-loop',
                    'req_id' => $debug['req_id'],
                    'event' => 'resolve.exit',
                    'path' => 'platform_or_empty_host',
                    'host' => $host,
                    'status' => (int) $response->getStatusCode(),
                    'location' => (string) ($response->headers->get('Location') ?? ''),
                ]);
            }
            // #endregion debug-point storefront-domain-loop:resolve

            return $response;
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

        // #region debug-point storefront-domain-loop:resolve
        if ($debug['enabled']) {
            $this->debugWrite([
                'ts' => now()->toIso8601String(),
                'session' => 'storefront-domain-loop',
                'req_id' => $debug['req_id'],
                'event' => 'resolve.lookup',
                'host' => $host,
                'matched' => (bool) $domain,
                'domain_id' => $domain?->id,
                'domain_value' => (string) ($domain?->domain ?? ''),
                'is_active' => (int) ($domain?->is_active ?? 0),
                'is_primary' => (int) ($domain?->is_primary ?? 0),
                'verified_at' => $domain?->verified_at ? $domain->verified_at->toIso8601String() : null,
                'fournisseur_id' => $domain?->fournisseur_id,
                'has_fournisseur' => (bool) ($domain?->fournisseur),
            ]);
        }
        // #endregion debug-point storefront-domain-loop:resolve

        if (! $domain || ! $domain->fournisseur) {
            $response = $next($request);

            // #region debug-point storefront-domain-loop:resolve
            if ($debug['enabled']) {
                $this->debugWrite([
                    'ts' => now()->toIso8601String(),
                    'session' => 'storefront-domain-loop',
                    'req_id' => $debug['req_id'],
                    'event' => 'resolve.exit',
                    'path' => 'no_active_match',
                    'host' => $host,
                    'status' => (int) $response->getStatusCode(),
                    'location' => (string) ($response->headers->get('Location') ?? ''),
                ]);
            }
            // #endregion debug-point storefront-domain-loop:resolve

            return $response;
        }

        if (! $domain->verified_at) {
            $domain->forceFill(['verified_at' => now()])->save();
        }

        $request->attributes->set('custom_storefront_domain', $domain);
        $request->attributes->set('custom_storefront_boutique', $domain->fournisseur);

        $response = $next($request);

        // #region debug-point storefront-domain-loop:resolve
        if ($debug['enabled']) {
            $this->debugWrite([
                'ts' => now()->toIso8601String(),
                'session' => 'storefront-domain-loop',
                'req_id' => $debug['req_id'],
                'event' => 'resolve.exit',
                'path' => 'matched_and_attached',
                'host' => $host,
                'boutique_id' => (int) $domain->fournisseur->id,
                'status' => (int) $response->getStatusCode(),
                'location' => (string) ($response->headers->get('Location') ?? ''),
            ]);
        }
        // #endregion debug-point storefront-domain-loop:resolve

        return $response;
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

    private function debugContext(string $host): array
    {
        $enabled = (string) env('TRAE_DEBUG_SESSION', '') === 'storefront-domain-loop';
        if (! $enabled) {
            return ['enabled' => false, 'req_id' => ''];
        }

        $targetHost = mb_strtolower(trim((string) env('TRAE_DEBUG_HOST', '')));
        if ($targetHost !== '' && $targetHost !== $host) {
            return ['enabled' => false, 'req_id' => ''];
        }

        return ['enabled' => true, 'req_id' => uniqid('sdl_', true)];
    }

    private function debugWrite(array $payload): void
    {
        try {
            $line = json_encode($payload, JSON_UNESCAPED_SLASHES);
            if (! is_string($line) || $line === '') {
                return;
            }

            @file_put_contents(
                storage_path('logs/trae-debug-log-storefront-domain-loop.ndjson'),
                $line.PHP_EOL,
                FILE_APPEND
            );
        } catch (Throwable) {
        }
    }
}
