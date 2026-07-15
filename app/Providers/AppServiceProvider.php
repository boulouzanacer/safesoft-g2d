<?php

namespace App\Providers;

use App\Models\Admin;
use App\Models\Fournisseur;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('public', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        RateLimiter::for('auth', function (Request $request) {
            $key = $request->user()?->getAuthIdentifier() ?: $request->ip();
            return Limit::perMinute(100)->by((string) $key);
        });

        View::composer('*', function ($view): void {
            static $shared = null;

            if ($shared === null) {
                $shared = [
                    'current_admin' => session()->has('admin_id')
                        ? Admin::query()->find((int) session('admin_id'))
                        : null,
                    'current_fournisseur' => session()->has('frs_id')
                        ? Fournisseur::query()->find((int) session('frs_id'))
                        : null,
                ];
            }

            $view->with($shared);
        });
    }
}
