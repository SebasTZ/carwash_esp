<?php

namespace App\Observers;

use App\Models\Venta;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class VentaObserver
{
    /**
     * Handle the Venta "created" event.
     */
    public function created(Venta $venta): void
    {
        // Limpiar caché de reportes
        $this->limpiarCacheReportes();

        // Loguear la creación
        Log::info('Nueva venta creada', [
            'venta_id' => $venta->id,
            'numero_comprobante' => $venta->numero_comprobante,
            'cliente_id' => $venta->cliente_id,
            'total' => $venta->total,
            'medio_pago' => $venta->medio_pago,
            'usuario_id' => auth()->id(),
        ]);

        // Si es servicio de lavado y no existe control_lavado, crearlo automáticamente
        if ($venta->servicio_lavado && !$venta->controlLavado) {
            $this->crearControlLavadoAutomatico($venta);
        }
    }

    /**
     * Handle the Venta "updated" event.
     */
    public function updated(Venta $venta): void
    {
        // Limpiar caché de reportes
        $this->limpiarCacheReportes();

        // Loguear cambios importantes
        if ($venta->wasChanged('estado')) {
            Log::warning('Estado de venta modificado', [
                'venta_id' => $venta->id,
                'numero_comprobante' => $venta->numero_comprobante,
                'estado_anterior' => $venta->getOriginal('estado'),
                'estado_nuevo' => $venta->estado,
                'usuario_id' => auth()->id(),
            ]);
        }

        if ($venta->wasChanged('total')) {
            Log::warning('Total de venta modificado', [
                'venta_id' => $venta->id,
                'total_anterior' => $venta->getOriginal('total'),
                'total_nuevo' => $venta->total,
                'usuario_id' => auth()->id(),
            ]);
        }
    }

    /**
     * Handle the Venta "deleted" event.
     */
    public function deleted(Venta $venta): void
    {
        // Limpiar caché de reportes
        $this->limpiarCacheReportes();

        // Loguear la eliminación
        Log::warning('Venta eliminada', [
            'venta_id' => $venta->id,
            'numero_comprobante' => $venta->numero_comprobante,
            'cliente_id' => $venta->cliente_id,
            'total' => $venta->total,
            'usuario_id' => auth()->id(),
        ]);
    }

    /**
     * Limpia el caché de reportes cuando hay cambios en ventas
     */
    private function limpiarCacheReportes(): void
    {
        try {
            // Limpiar caché de reportes diarios, semanales y mensuales
            Cache::forget('reporte_ventas_diario');
            Cache::forget('reporte_ventas_semanal');
            Cache::forget('reporte_ventas_mensual');
            Cache::forget('dashboard_ventas_hoy');
            Cache::forget('dashboard_ventas_mes');
            
            // Si el driver soporta tags, limpiar por tag
            Cache::tags(['reportes', 'ventas'])->flush();
        } catch (\BadMethodCallException $e) {
            // El driver de caché no soporta tags (file driver)
            // Ya limpiamos las claves individuales arriba
        }

        Log::debug('Caché de reportes de ventas limpiado');
    }

    /**
     * Crea automáticamente el control de lavado si no existe
     */
    private function crearControlLavadoAutomatico(Venta $venta): void
    {
        try {
            if ($venta->horario_lavado) {
                \App\Models\ControlLavado::create([
                    'venta_id' => $venta->id,
                    'cliente_id' => $venta->cliente_id,
                    'lavador_id' => null,
                    'hora_llegada' => now(),
                    'horario_estimado' => $venta->horario_lavado,
                    'inicio_lavado' => null,
                    'fin_lavado' => null,
                    'inicio_interior' => null,
                    'fin_interior' => null,
                    'hora_final' => null,
                    'tiempo_total' => null,
                    'estado' => 'En espera',
                ]);

                Log::info('Control de lavado creado automáticamente', [
                    'venta_id' => $venta->id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error al crear control de lavado automático', [
                'venta_id' => $venta->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
