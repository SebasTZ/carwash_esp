<?php

namespace App\Services;

use App\Models\Estacionamiento;
use Illuminate\Support\Facades\Log;

class EstacionamientoService
{
    /**
     * Calcula el monto total a pagar por estacionamiento
     *
     * @param Estacionamiento $estacionamiento Registro del estacionamiento
     * @return float Monto calculado
     */
    public function calcularMonto(Estacionamiento $estacionamiento): float
    {
        // Validar que existan las horas
        if (!$estacionamiento->hora_entrada || !$estacionamiento->hora_salida) {
            throw new \Exception('No se puede calcular sin hora de entrada y salida');
        }

        // Calcular el tiempo total en minutos
        $tiempoTotal = $estacionamiento->hora_entrada->diffInMinutes($estacionamiento->hora_salida);

        // Calcular la tarifa por minuto (tarifa_hora / 60)
        $tarifaPorMinuto = $estacionamiento->tarifa_hora / 60;

        // Calcular el monto total basado en la tarifa por hora (proporcional a minutos)
        $montoTotal = $tarifaPorMinuto * $tiempoTotal;

        // Si hay pago adelantado, descontar del monto total
        if ($estacionamiento->monto_pagado_adelantado > 0) {
            $montoTotal = max(0, $montoTotal - $estacionamiento->monto_pagado_adelantado);
        }

        Log::channel('estacionamiento')->debug('Monto calculado', [
            'estacionamiento_id' => $estacionamiento->id,
            'tiempo_minutos' => $tiempoTotal,
            'tarifa_hora' => $estacionamiento->tarifa_hora,
            'tarifa_minuto' => $tarifaPorMinuto,
            'monto_bruto' => ($tarifaPorMinuto * $tiempoTotal),
            'pagado_adelantado' => $estacionamiento->monto_pagado_adelantado,
            'monto_total' => $montoTotal,
        ]);

        return round($montoTotal, 2);
    }

    /**
     * Registra la salida de un vehículo y calcula el monto a pagar
     *
     * @param Estacionamiento $estacionamiento Registro del estacionamiento
     * @return float Monto total a pagar
     */
    public function registrarSalida(Estacionamiento $estacionamiento): float
    {
        // Calcular el monto usando el servicio
        $montoTotal = $this->calcularMonto($estacionamiento);

        // Actualizar registro
        $estacionamiento->monto_total = $montoTotal;
        $estacionamiento->save();

        Log::channel('estacionamiento')->info('Salida registrada', [
            'estacionamiento_id' => $estacionamiento->id,
            'placa' => $estacionamiento->placa,
            'monto_total' => $montoTotal,
            'timestamp' => now()->toISOString(),
        ]);

        return $montoTotal;
    }

    /**
     * Obtiene el total de ingresos por estacionamiento en un período
     *
     * @param string $fechaInicio Fecha inicio en formato Y-m-d
     * @param string $fechaFin Fecha fin en formato Y-m-d
     * @return float Total de ingresos
     */
    public function obtenerIngresoPeriodo(string $fechaInicio, string $fechaFin): float
    {
        return Estacionamiento::whereBetween('hora_salida', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->whereNotNull('hora_salida')
            ->sum('monto_total');
    }
}
