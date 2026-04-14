<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
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

        // Route model bindings (migrado desde RouteServiceProvider)
        Route::model('lavador', \App\Models\Lavador::class);
        Route::model('tipo_vehiculo', \App\Models\TipoVehiculo::class);

        // Rate limiting (migrado desde RouteServiceProvider)
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
