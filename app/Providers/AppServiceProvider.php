<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Registrar servicios como singletons
        $this->app->singleton(\App\Services\VentaService::class);
        $this->app->singleton(\App\Services\StockService::class);
        $this->app->singleton(\App\Services\FidelizacionService::class);
        $this->app->singleton(\App\Services\TarjetaRegaloService::class);
        $this->app->singleton(\App\Services\ComprobanteService::class);
        $this->app->singleton(\App\Services\ControlLavadoService::class);
        $this->app->singleton(\App\Services\AuditoriaService::class);
        $this->app->singleton(\App\Services\ComisionService::class);

        // Registrar repositorios como singletons
        $this->app->singleton(\App\Repositories\VentaRepository::class);
        $this->app->singleton(\App\Repositories\ProductoRepository::class);
        $this->app->singleton(\App\Repositories\CaracteristicaRepository::class);
        $this->app->singleton(\App\Repositories\ControlLavadoRepository::class);
        $this->app->singleton(\App\Repositories\AuditoriaLavadorRepository::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Registrar Observers
        \App\Models\Producto::observe(\App\Observers\ProductoObserver::class);
        \App\Models\Venta::observe(\App\Observers\VentaObserver::class);
        \App\Models\ControlLavado::observe(\App\Observers\ControlLavadoObserver::class);
    }
}
