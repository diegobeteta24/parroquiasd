<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use App\Services\RbacHealth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    // Registrar bindings aquí
    // PDF: En Laravel 12, barryvdh/laravel-dompdf ^2 no es compatible. Evaluar alternativa
    // (por ejemplo, spatie/browsershot o wkhtmltopdf) y registrar el servicio cuando se defina.
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $portalConfig = config('portal');
        View::share('portalConfig', $portalConfig);
        View::share('portalParish', $portalConfig['parish'] ?? []);
        View::share('portalHeroConfig', $portalConfig['hero'] ?? []);

        // Auto-heal RBAC once every few minutes to avoid missing roles/users due to cache or migrations
        try {
            Cache::remember('rbac_health_last_run', now()->addMinutes(5), function(){
                app(RbacHealth::class)->ensure();
                return now();
            });
        } catch (\Throwable $e) {
            // ignore boot-time RBAC fixes if cache connection not ready
        }
    }
}
