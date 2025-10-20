<?php

namespace App\Listeners;

use App\Events\StockBajoEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class NotificarStockBajo implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(StockBajoEvent $event): void
    {
        $producto = $event->producto;

        // Evitar notificaciones duplicadas (máximo 1 por día por producto)
        $cacheKey = "notificacion_stock_bajo_{$producto->id}";
        
        if (Cache::has($cacheKey)) {
            return; // Ya se notificó hoy
        }

        // Loguear alerta de stock bajo
        Log::warning('ALERTA: Stock bajo detectado', [
            'producto_id' => $producto->id,
            'nombre' => $producto->nombre,
            'stock_actual' => $producto->stock,
            'stock_minimo' => $producto->stock_minimo ?? 10,
            'marca' => $producto->marca->caracteristica->nombre ?? 'N/A',
        ]);

        // Aquí podrías enviar notificaciones por:
        // - Email al administrador
        // - SMS
        // - Slack/Discord webhook
        // - Push notification
        
        // Ejemplo de envío de email (descomentarizar cuando tengas configurado el mail):
        /*
        try {
            Mail::to(config('app.admin_email'))
                ->send(new StockBajoMailable($producto));
        } catch (\Exception $e) {
            Log::error('Error al enviar email de stock bajo', [
                'producto_id' => $producto->id,
                'error' => $e->getMessage(),
            ]);
        }
        */

        // Marcar como notificado por 24 horas
        Cache::put($cacheKey, true, now()->addDay());

        Log::info('Notificación de stock bajo procesada', [
            'producto_id' => $producto->id,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(StockBajoEvent $event, \Throwable $exception): void
    {
        Log::error('Error al procesar notificación de stock bajo', [
            'producto_id' => $event->producto->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
