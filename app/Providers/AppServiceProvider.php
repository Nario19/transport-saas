<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Empresa;
use App\Observers\EmpresaObserver;
use Illuminate\Pagination\Paginator;

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
        Empresa::observe(EmpresaObserver::class);

        // Usar el partial personalizado para toda la paginación de la app
        Paginator::defaultView('partials.pagination');

        // Garantizar acceso global a Super Admin y administradores de empresa a alertas y ajustes
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            if ($user->hasRole('SUPER_ADMIN')) {
                return true;
            }
            if ($user->roles->contains(fn($r) => str_ends_with($r->name, '_ADMIN') || $r->name === 'ADMIN')) {
                if (in_array($ability, ['gestionar alertas', 'gestionar ajustes de empresa'])) {
                    return true;
                }
            }
            return null;
        });
    }
}
