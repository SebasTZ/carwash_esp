<?php

namespace App\Support;

use Illuminate\Support\Collection;

class VentaTransformer
{
    public function transformCollection($ventasCollection): Collection
    {
        return collect($ventasCollection)->map(function ($venta) {
            $medioPago = $venta->medio_pago ?? '-';

            return [
                'id' => $venta->id,
                'comprobante' => [
                    'tipo_comprobante' => $venta->comprobante->tipo_comprobante ?? '-',
                    'numero_comprobante' => $venta->numero_comprobante ?? '-',
                ],
                'cliente' => [
                    'persona' => [
                        'razon_social' => $venta->cliente->persona->razon_social ?? '-',
                        'tipo_persona' => $venta->cliente->persona->tipo_persona ?? '-',
                    ],
                ],
                'fecha_hora' => $venta->fecha_hora,
                'vendedor' => [
                    'name' => $venta->user->name ?? '-',
                ],
                'total' => number_format($venta->total, 2),
                'total_raw' => (float) ($venta->total ?? 0),
                'comentarios' => $venta->comentarios ?? '-',
                'medio_pago' => $medioPago,
                'efectivo' => number_format($venta->efectivo ?? 0, 2),
                'tarjeta_credito' => number_format($venta->tarjeta_credito ?? 0, 2),
                'tarjeta_regalo_id' => $venta->tarjeta_regalo_id ?? '-',
                'lavado_gratis' => (bool) $venta->lavado_gratis,
                'servicio_lavado' => (bool) $venta->servicio_lavado,
                'horario_lavado' => $venta->horario_lavado ? $venta->horario_lavado->format('d/m/Y H:i') : '-',
            ];
        })->values();
    }
}
