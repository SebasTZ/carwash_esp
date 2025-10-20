<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Fidelizacion;
use Illuminate\Support\Facades\Cache;

class FidelizacionService
{
    /**
     * Número de lavados necesarios para obtener uno gratis
     */
    private const LAVADOS_PARA_GRATIS = 10;

    /**
     * Acumula un lavado al cliente
     *
     * @param Cliente $cliente
     * @return void
     */
    public function acumularLavado(Cliente $cliente): void
    {
        $cliente->increment('lavados_acumulados');
        
        // Limpiar caché
        Cache::forget("cliente_{$cliente->id}_fidelizacion");

        // Notificar al cliente si completó los lavados
        if ($cliente->lavados_acumulados >= self::LAVADOS_PARA_GRATIS) {
            // Aquí podrías disparar una notificación
            \Log::info("Cliente {$cliente->id} completó {$cliente->lavados_acumulados} lavados");
        }
    }

    /**
     * Acumula puntos de fidelización al cliente (10% del total de la venta)
     *
     * @param Cliente $cliente
     * @param float $totalVenta
     * @return void
     */
    public function acumularPuntos(Cliente $cliente, float $totalVenta): void
    {
        $puntos = $totalVenta * 0.1; // 10% del total en puntos

        // Si el cliente ya tiene registro de fidelización, incrementar puntos
        if ($cliente->fidelizacion) {
            $cliente->fidelizacion->increment('puntos', $puntos);
        } else {
            // Crear nuevo registro de fidelización
            Fidelizacion::create([
                'cliente_id' => $cliente->id,
                'puntos' => $puntos,
            ]);
        }

        Cache::forget("cliente_{$cliente->id}_fidelizacion");
        
        \Log::info("Puntos acumulados para cliente {$cliente->id}: {$puntos} puntos");
    }

    /**
     * Verifica si el cliente puede usar un lavado gratis
     *
     * @param Cliente $cliente
     * @return bool
     */
    public function puedeUsarLavadoGratis(Cliente $cliente): bool
    {
        return $cliente->lavados_acumulados >= self::LAVADOS_PARA_GRATIS;
    }

    /**
     * Canjea el lavado gratis del cliente
     *
     * @param Cliente $cliente
     * @return void
     */
    public function canjearLavadoGratis(Cliente $cliente): void
    {
        $cliente->lavados_acumulados = 0;
        $cliente->save();

        // Registrar en tabla de fidelización si existe
        try {
            Fidelizacion::create([
                'cliente_id' => $cliente->id,
                'lavados_acumulados' => self::LAVADOS_PARA_GRATIS,
                'fecha_canje' => now(),
                'tipo' => 'lavado_gratis',
            ]);
        } catch (\Exception $e) {
            \Log::info("Lavado gratis canjeado para cliente {$cliente->id}");
        }

        Cache::forget("cliente_{$cliente->id}_fidelizacion");
    }

    /**
     * Revierte un lavado acumulado (usado en anulaciones)
     *
     * @param Cliente $cliente
     * @return void
     */
    public function revertirLavado(Cliente $cliente): void
    {
        if ($cliente->lavados_acumulados > 0) {
            $cliente->decrement('lavados_acumulados');
            Cache::forget("cliente_{$cliente->id}_fidelizacion");
        }
    }

    /**
     * Obtiene el progreso de fidelización del cliente
     *
     * @param Cliente $cliente
     * @return array
     */
    public function obtenerProgreso(Cliente $cliente): array
    {
        return Cache::remember("cliente_{$cliente->id}_fidelizacion", 3600, function () use ($cliente) {
            return [
                'lavados_acumulados' => $cliente->lavados_acumulados,
                'lavados_faltantes' => max(0, self::LAVADOS_PARA_GRATIS - $cliente->lavados_acumulados),
                'progreso_porcentaje' => min(100, ($cliente->lavados_acumulados / self::LAVADOS_PARA_GRATIS) * 100),
                'puede_canjear' => $this->puedeUsarLavadoGratis($cliente),
            ];
        });
    }
}
