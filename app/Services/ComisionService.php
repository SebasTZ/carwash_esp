<?php

namespace App\Services;

use App\Models\ControlLavado;
use App\Models\Lavador;
use App\Models\PagoComision;
use Illuminate\Support\Facades\Log;

class ComisionService
{
    /**
     * Registra una comisión para un lavado completado
     */
    public function registrarComisionLavado(ControlLavado $lavado): ?PagoComision
    {
        // Validar que el lavado tenga lavador
        if (!$lavado->lavador_id) {
            throw new \Exception('El lavado no tiene lavador asignado');
        }

        // Validar que el lavado esté completado
        if (!$lavado->fin_interior) {
            throw new \Exception('El lavado no está finalizado');
        }

        $montoComision = $this->calcularComision($lavado);

        if ($montoComision <= 0) {
            Log::channel('lavados')->info('Comisión calculada es 0 o negativa', [
                'lavado_id' => $lavado->id,
                'monto' => $montoComision,
            ]);
            return null;
        }

        $pagoComision = PagoComision::create([
            'lavador_id' => $lavado->lavador_id,
            'monto_pagado' => $montoComision,
            'desde' => $lavado->hora_llegada,
            'hasta' => $lavado->hora_final ?? $lavado->fin_interior ?? now(),
            'observacion' => 'Comisión por lavado ID ' . $lavado->id,
            'fecha_pago' => now(),
        ]);

        Log::channel('lavados')->info('Comisión registrada exitosamente', [
            'pago_comision_id' => $pagoComision->id,
            'lavado_id' => $lavado->id,
            'lavador_id' => $lavado->lavador_id,
            'monto' => $montoComision,
            'timestamp' => now()->toISOString(),
        ]);

        return $pagoComision;
    }

    /**
     * Calcula la comisión basada en el tipo de vehículo
     */
    protected function calcularComision(ControlLavado $lavado): float
    {
        if ($lavado->tipoVehiculo && $lavado->tipoVehiculo->comision > 0) {
            return round($lavado->tipoVehiculo->comision, 2);
        }

        return 10.00; // Comisión base por defecto
    }

    /**
     * Obtiene el total de comisiones de un lavador en un período
     */
    public function obtenerComisionesLavador(int $lavadorId, string $fechaInicio, string $fechaFin): float
    {
        return PagoComision::where('lavador_id', $lavadorId)
            ->whereBetween('fecha_pago', [$fechaInicio, $fechaFin])
            ->sum('monto_pagado');
    }

    /**
     * Genera reporte de comisiones para todos los lavadores en un período.
     *
     * Usa 3 queries en total (antes: 2N+1).
     *
     * @param string $fechaInicio Fecha inicio en formato Y-m-d
     * @param string $fechaFin Fecha fin en formato Y-m-d
     * @return array Array con datos de comisiones por lavador
     */
    public function generarReporteComisiones(string $fechaInicio, string $fechaFin): array
    {
        // Query 1: Lavadores activos con pagos eager-loaded
        $lavadores = Lavador::where('estado', 'activo')
            ->with(['pagosComisiones'])
            ->get();

        // Query 2: Todos los ControlLavado del período agrupados por lavador
        $lavadosPorLavador = ControlLavado::whereIn('lavador_id', $lavadores->pluck('id'))
            ->whereBetween('hora_llegada', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->with('tipoVehiculo')
            ->get()
            ->groupBy('lavador_id');

        $data = $lavadores->map(function (Lavador $lavador) use ($lavadosPorLavador, $fechaInicio, $fechaFin) {
            $lavados = $lavadosPorLavador->get($lavador->id, collect());

            $comisionTotal = $lavados->sum(fn($l) => $l->tipoVehiculo?->comision ?? 0);

            // Filtrar pagos en memoria (ya eager-loaded)
            $pagado = $lavador->pagosComisiones
                ->where('desde', '<=', $fechaFin)
                ->where('hasta', '>=', $fechaInicio)
                ->sum('monto_pagado');

            return [
                'lavador' => $lavador,
                'cantidad' => $lavados->count(),
                'comision_total' => $comisionTotal,
                'pagado' => $pagado,
                'saldo' => $comisionTotal - $pagado,
            ];
        })->sortBy(fn($item) => $item['lavador']->nombre)->values()->toArray();

        Log::channel('comisiones')->info('Reporte de comisiones generado', [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'cantidad_lavadores' => count($data),
            'comision_total' => array_sum(array_column($data, 'comision_total')),
        ]);

        return $data;
    }
}
