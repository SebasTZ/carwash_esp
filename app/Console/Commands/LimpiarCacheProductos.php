<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class LimpiarCacheProductos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:productos:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpia el caché relacionado con productos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Limpiando caché de productos...');

        // Limpiar cachés específicos
        $caches = [
            'productos:para_venta',
            'productos:stock_bajo',
            'marcas:activas',
            'presentaciones:activas',
            'categorias:activas',
        ];

        foreach ($caches as $key) {
            Cache::forget($key);
            $this->line("  - {$key}");
        }
        
        // Tags solo si el driver lo soporta (Redis, Memcached)
        try {
            Cache::tags(['productos'])->flush();
            $this->line("  - Tags: productos");
        } catch (\Exception $e) {
            // Ignorar si el driver no soporta tags
        }

        $this->info('✓ Caché de productos limpiado exitosamente');
        
        return Command::SUCCESS;
    }
}
