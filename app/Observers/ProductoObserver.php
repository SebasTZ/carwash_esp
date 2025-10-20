<?php

namespace App\Observers;

use App\Models\Producto;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProductoObserver
{
    /**
     * Handle the Producto "created" event.
     */
    public function created(Producto $producto): void
    {
        Log::info("Producto creado: {$producto->nombre}", ['id' => $producto->id]);
        $this->limpiarCache();
    }

    /**
     * Handle the Producto "updated" event.
     */
    public function updated(Producto $producto): void
    {
        // Loguear cambios importantes
        if ($producto->wasChanged('stock')) {
            Log::info("Stock actualizado: {$producto->nombre}", [
                'producto_id' => $producto->id,
                'stock_anterior' => $producto->getOriginal('stock'),
                'stock_nuevo' => $producto->stock,
            ]);
        }

        if ($producto->wasChanged('precio_venta')) {
            Log::info("Precio actualizado: {$producto->nombre}", [
                'producto_id' => $producto->id,
                'precio_anterior' => $producto->getOriginal('precio_venta'),
                'precio_nuevo' => $producto->precio_venta,
            ]);
        }

        $this->limpiarCache();
    }

    /**
     * Handle the Producto "deleted" event.
     */
    public function deleted(Producto $producto): void
    {
        Log::warning("Producto eliminado: {$producto->nombre}", ['id' => $producto->id]);
        $this->limpiarCache();
    }

    /**
     * Handle the Producto "restored" event.
     */
    public function restored(Producto $producto): void
    {
        Log::info("Producto restaurado: {$producto->nombre}", ['id' => $producto->id]);
        $this->limpiarCache();
    }

    /**
     * Limpia el caché relacionado con productos
     */
    private function limpiarCache(): void
    {
        Cache::forget('productos:para_venta');
        Cache::forget('productos:stock_bajo');
        
        // Tags solo si el driver lo soporta
        try {
            Cache::tags(['productos'])->flush();
        } catch (\Exception $e) {
            // Ignorar si el driver no soporta tags
        }
    }
}
