<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Fidelizacion;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FidelizacionService
{
    private const LAVADOS_PARA_GRATIS = 10;

    public function acumularLavado(Cliente $cliente): void
    {
        $cliente->increment('lavados_acumulados');
        Cache::forget("cliente_{$cliente->id}_fidelizacion");

        // Leer valor actualizado de BD para la comparación (increment() no refresca el modelo)
        if ($cliente->fresh()->lavados_acumulados >= self::LAVADOS_PARA_GRATIS) {
            Log::channel('fidelizacion')->info('Cliente alcanzó lavados para canje gratis', [
                'cliente_id' => $cliente->id,
                'lavados_acumulados' => $cliente->fresh()->lavados_acumulados,
            ]);
        }
    }

    public function acumularPuntos(Cliente $cliente, float $totalVenta): void
    {
        $puntos = $totalVenta * 0.1;

        if ($cliente->fidelizacion) {
            $cliente->fidelizacion->increment('puntos', $puntos);
        } else {
            Fidelizacion::create([
                'cliente_id' => $cliente->id,
                'puntos' => $puntos,
            ]);
        }

        Cache::forget("cliente_{$cliente->id}_fidelizacion");

        Log::channel('fidelizacion')->info('Puntos acumulados', [
            'cliente_id' => $cliente->id,
            'puntos' => $puntos,
        ]);
    }

    public function puedeUsarLavadoGratis(Cliente $cliente): bool
    {
        return $cliente->lavados_acumulados >= self::LAVADOS_PARA_GRATIS;
    }

    public function canjearLavadoGratis(Cliente $cliente): void
    {
        $cliente->lavados_acumulados = 0;
        $cliente->save();

        // Sin try/catch: si la tabla tiene columnas incorrectas, debe fallar visiblemente
        Fidelizacion::create([
            'cliente_id' => $cliente->id,
            'lavados_acumulados' => self::LAVADOS_PARA_GRATIS,
            'fecha_canje' => now(),
            'tipo' => 'lavado_gratis',
        ]);

        Cache::forget("cliente_{$cliente->id}_fidelizacion");

        Log::channel('fidelizacion')->info('Lavado gratis canjeado', ['cliente_id' => $cliente->id]);
    }

    public function revertirLavado(Cliente $cliente): void
    {
        if ($cliente->lavados_acumulados > 0) {
            $cliente->decrement('lavados_acumulados');
            Cache::forget("cliente_{$cliente->id}_fidelizacion");
        }
    }

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
