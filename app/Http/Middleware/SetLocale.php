<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = array_keys((array) config('locales.supported', []));
        $default = (string) config('locales.default', config('app.locale', 'fr'));

        $locale = (string) $request->session()->get('locale', $default);

        if ($request->filled('lang')) {
            $requestedLocale = (string) $request->query('lang');
            if (in_array($requestedLocale, $supported, true)) {
                $locale = $requestedLocale;
                $request->session()->put('locale', $locale);
            }
        }

        if (! in_array($locale, $supported, true)) {
            $locale = $default;
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
