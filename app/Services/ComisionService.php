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
}
