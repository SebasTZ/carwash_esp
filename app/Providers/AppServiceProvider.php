<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Servicios como singletons
        $this->app->singleton(\App\Services\VentaService::class);
        $this->app->singleton(\App\Services\StockService::class);
        $this->app->singleton(\App\Services\FidelizacionService::class);
        $this->app->singleton(\App\Services\TarjetaRegaloService::class);
        $this->app->singleton(\App\Services\ComprobanteService::class);
        $this->app->singleton(\App\Services\ControlLavadoService::class);
        $this->app->singleton(\App\Services\AuditoriaService::class);
        $this->app->singleton(\App\Services\ComisionService::class);

        // Repositorios como singletons
        $this->app->singleton(\App\Repositories\VentaRepository::class);
        $this->app->singleton(\App\Repositories\ProductoRepository::class);
        $this->app->singleton(\App\Repositories\CaracteristicaRepository::class);
        $this->app->singleton(\App\Repositories\ControlLavadoRepository::class);
        $this->app->singleton(\App\Repositories\AuditoriaLavadorRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Policies y Gates para autorización contextual
        Gate::policy(\App\Models\Venta::class, \App\Policies\VentaPolicy::class);
        Gate::policy(\App\Models\Cita::class, \App\Policies\CitaPolicy::class);
        Gate::policy(\App\Models\User::class, \App\Policies\UserPolicy::class);
        Gate::policy(\Spatie\Permission\Models\Role::class, \App\Policies\RolePolicy::class);

        Gate::define('gestionar-venta-propia', function (\App\Models\User $user, \App\Models\Venta $venta) {
            $esPrivilegiado = $user->hasAnyRole(['admin', 'superadmin', 'administrador']);

            return $user->can('eliminar-venta') && ($esPrivilegiado || $venta->user_id === $user->id);
        });

        Gate::define('gestionar-cita-propia', function (\App\Models\User $user, \App\Models\Cita $cita) {
            $esPrivilegiado = $user->hasAnyRole(['admin', 'superadmin', 'administrador']);

            return $user->can('editar-cita') && ($esPrivilegiado || $cita->user_id === $user->id);
        });

        // Observers
        \App\Models\Producto::observe(\App\Observers\ProductoObserver::class);
        \App\Models\Venta::observe(\App\Observers\VentaObserver::class);
        \App\Models\ControlLavado::observe(\App\Observers\ControlLavadoObserver::class);

        // Event listeners (migrado desde EventServiceProvider)
        Event::listen(
            \Illuminate\Auth\Events\Registered::class,
            \Illuminate\Auth\Listeners\SendEmailVerificationNotification::class,
        );
        Event::listen(
            \App\Events\StockBajoEvent::class,
            \App\Listeners\NotificarStockBajo::class,
        );

        // Invalidar caché de permisos al cambiar roles o permisos
        $permissionEvents = [
            \Spatie\Permission\Events\RoleAssigned::class,
            \Spatie\Permission\Events\RoleRevoked::class,
            \Spatie\Permission\Events\PermissionAssigned::class,
            \Spatie\Permission\Events\PermissionRevoked::class,
            \Spatie\Permission\Events\RoleCreated::class,
            \Spatie\Permission\Events\RoleDeleted::class,
            \Spatie\Permission\Events\PermissionCreated::class,
            \Spatie\Permission\Events\PermissionDeleted::class,
        ];
        foreach ($permissionEvents as $event) {
            Event::listen($event, \App\Listeners\LimpiarCachePermisos::class);
        }

        // Route model bindings (migrado desde RouteServiceProvider)
        Route::model('lavador', \App\Models\Lavador::class);
        Route::model('tipo_vehiculo', \App\Models\TipoVehiculo::class);

        // Rate limiting (migrado desde RouteServiceProvider)
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
