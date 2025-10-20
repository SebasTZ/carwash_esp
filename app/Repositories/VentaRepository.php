<?php

namespace App\Repositories;

use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class VentaRepository
{
    /**
     * Relaciones comunes que se cargan frecuentemente
     */
    private const RELACIONES_COMUNES = [
        'comprobante',
        'cliente.persona',
        'user',
        'productos'
    ];

    /**
     * Obtiene ventas con filtros y paginación
     *
     * @param array $filtros
     * @return LengthAwarePaginator
     */
    public function obtenerConFiltros(array $filtros = []): LengthAwarePaginator
    {
        return Venta::query()
            ->with(self::RELACIONES_COMUNES)
            ->when($filtros['estado'] ?? null, fn($q, $estado) => $q->where('estado', $estado))
            ->when($filtros['cliente_id'] ?? null, fn($q, $clienteId) => $q->where('cliente_id', $clienteId))
            ->when($filtros['medio_pago'] ?? null, fn($q, $medio) => $q->where('medio_pago', $medio))
            ->when($filtros['fecha_desde'] ?? null, fn($q, $fecha) => 
                $q->where('fecha_hora', '>=', $fecha)
            )
            ->when($filtros['fecha_hasta'] ?? null, fn($q, $fecha) => 
                $q->where('fecha_hora', '<=', $fecha . ' 23:59:59')
            )
            ->where('estado', 1)
            ->latest()
            ->paginate($filtros['per_page'] ?? 15);
    }

    /**
     * Obtiene ventas del día
     *
     * @param Carbon|null $fecha
     * @return Collection
     */
    public function obtenerDelDia(?Carbon $fecha = null): Collection
    {
        $fecha = $fecha ?? today();

        return Venta::with(self::RELACIONES_COMUNES)
            ->whereDate('fecha_hora', $fecha)
            ->where('estado', 1)
            ->get();
    }

    /**
     * Obtiene ventas de la semana
     *
     * @return Collection
     */
    public function obtenerDeLaSemana(): Collection
    {
        return Venta::with(self::RELACIONES_COMUNES)
            ->whereBetween('fecha_hora', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])
            ->where('estado', 1)
            ->get();
    }

    /**
     * Obtiene ventas del mes
     *
     * @param int|null $mes
     * @param int|null $anio
     * @return Collection
     */
    public function obtenerDelMes(?int $mes = null, ?int $anio = null): Collection
    {
        $mes = $mes ?? now()->month;
        $anio = $anio ?? now()->year;

        return Venta::with(self::RELACIONES_COMUNES)
            ->whereMonth('fecha_hora', $mes)
            ->whereYear('fecha_hora', $anio)
            ->where('estado', 1)
            ->get();
    }

    /**
     * Obtiene ventas por rango de fechas
     *
     * @param Carbon $fechaInicio
     * @param Carbon $fechaFin
     * @return Collection
     */
    public function obtenerPorRango(Carbon $fechaInicio, Carbon $fechaFin): Collection
    {
        return Venta::with(self::RELACIONES_COMUNES)
            ->whereBetween('fecha_hora', [
                $fechaInicio->startOfDay(),
                $fechaFin->endOfDay()
            ])
            ->where('estado', 1)
            ->get();
    }

    /**
     * Obtiene totales de ventas por período
     *
     * @param Carbon $fechaInicio
     * @param Carbon $fechaFin
     * @return array
     */
    public function obtenerTotalesPorPeriodo(Carbon $fechaInicio, Carbon $fechaFin): array
    {
        $resultado = Venta::whereBetween('fecha_hora', [
                $fechaInicio->startOfDay(),
                $fechaFin->endOfDay()
            ])
            ->where('estado', 1)
            ->selectRaw('
                COUNT(*) as total_ventas,
                SUM(total) as monto_total,
                AVG(total) as ticket_promedio,
                SUM(CASE WHEN medio_pago = "efectivo" THEN total ELSE 0 END) as total_efectivo,
                SUM(CASE WHEN medio_pago = "tarjeta_credito" THEN total ELSE 0 END) as total_tarjeta,
                SUM(CASE WHEN medio_pago = "tarjeta_regalo" THEN total ELSE 0 END) as total_tarjeta_regalo
            ')
            ->first();

        return [
            'total_ventas' => $resultado->total_ventas ?? 0,
            'monto_total' => $resultado->monto_total ?? 0,
            'ticket_promedio' => $resultado->ticket_promedio ?? 0,
            'total_efectivo' => $resultado->total_efectivo ?? 0,
            'total_tarjeta' => $resultado->total_tarjeta ?? 0,
            'total_tarjeta_regalo' => $resultado->total_tarjeta_regalo ?? 0,
        ];
    }

    /**
     * Obtiene ventas por medio de pago
     *
     * @param string $medioPago
     * @param Carbon|null $fecha
     * @return Collection
     */
    public function obtenerPorMedioPago(string $medioPago, ?Carbon $fecha = null): Collection
    {
        $query = Venta::with(self::RELACIONES_COMUNES)
            ->where('medio_pago', $medioPago)
            ->where('estado', 1);

        if ($fecha) {
            $query->whereDate('fecha_hora', $fecha);
        }

        return $query->get();
    }

    /**
     * Obtiene las ventas de un cliente
     *
     * @param int $clienteId
     * @param int $limite
     * @return Collection
     */
    public function obtenerPorCliente(int $clienteId, int $limite = 20): Collection
    {
        return Venta::with(['productos', 'comprobante'])
            ->where('cliente_id', $clienteId)
            ->where('estado', 1)
            ->latest()
            ->limit($limite)
            ->get();
    }
}
