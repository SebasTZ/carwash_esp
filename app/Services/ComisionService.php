<?php

namespace App\Services;

use App\Models\ControlLavado;
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
     * Calcula la comisión basada en el tipo de vehículo y otros factores
     */
    protected function calcularComision(ControlLavado $lavado): float
    {
        // Comisión base
        $comisionBase = 10.00;

        // Factor por tipo de vehículo (si existe configuración)
        $factorTipoVehiculo = 1.0;
        if ($lavado->tipoVehiculo && $lavado->tipoVehiculo->comision > 0) {
            // Usar directamente el monto de comisión del tipo de vehículo
            return round($lavado->tipoVehiculo->comision, 2);
        }

        // Si no hay comisión específica, usar comisión base
        $comisionTotal = $comisionBase * $factorTipoVehiculo;

        Log::channel('lavados')->debug('Comisión calculada', [
            'lavado_id' => $lavado->id,
            'comision_base' => $comisionBase,
            'factor_tipo_vehiculo' => $factorTipoVehiculo,
            'comision_total' => $comisionTotal,
        ]);

        return round($comisionTotal, 2);
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
     * Genera reporte de comisiones para todos los lavadores en un período
     * 
     * Este método centraliza la lógica de cálculo que antes estaba duplicada
     * en reporteComisiones() y exportarComisiones() del controller
     * 
     * @param string $fechaInicio Fecha inicio en formato Y-m-d
     * @param string $fechaFin Fecha fin en formato Y-m-d
     * @return array Array con datos de comisiones por lavador
     */
    public function generarReporteComisiones(string $fechaInicio, string $fechaFin): array
    {
        $lavadores = \App\Models\Lavador::where('estado', 'activo')->get();
        $data = [];

        foreach ($lavadores as $lavador) {
            // Lavados realizados en el rango
            $lavados = ControlLavado::where('lavador_id', $lavador->id)
                ->whereBetween('hora_llegada', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
                ->with('tipoVehiculo')
                ->get();

            $cantidad = $lavados->count();
            $comisionTotal = $lavados->sum(function($lavado) {
                return $lavado->tipoVehiculo ? $lavado->tipoVehiculo->comision : 0;
            });

            // Total pagado en el rango (pagos que se solapan con el rango)
            $pagado = $lavador->pagosComisiones()
                ->where('desde', '<=', $fechaFin)
                ->where('hasta', '>=', $fechaInicio)
                ->sum('monto_pagado');

            $saldo = $comisionTotal - $pagado;

            $data[] = [
                'lavador' => $lavador,
                'cantidad' => $cantidad,
                'comision_total' => $comisionTotal,
                'pagado' => $pagado,
                'saldo' => $saldo,
            ];
        }

        // Ordenar por nombre del lavador para consistencia
        usort($data, function($a, $b) {
            return strcmp($a['lavador']->nombre, $b['lavador']->nombre);
        });

        Log::channel('comisiones')->info('Reporte de comisiones generado', [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'cantidad_lavadores' => count($data),
            'comision_total' => array_sum(array_column($data, 'comision_total')),
        ]);

        return $data;
    }
}
