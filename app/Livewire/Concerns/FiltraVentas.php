<?php

namespace App\Livewire\Concerns;

trait FiltraVentas
{
    /**
     * Medios de pago excluidos del cálculo de totales.
     */
    protected array $mediosPagoExcluidos = ['tarjeta_regalo', 'lavado_gratis', 'lavado_gratis_(fidelidad)'];

    /**
     * Filtra un array/Collection de ventas (transformadas) por término de búsqueda.
     * Busca en cliente razon_social, comprobante numero_comprobante y vendedor name.
     */
    protected function filtrarVentasPorBusqueda(iterable $ventas, string $search): \Illuminate\Support\Collection
    {
        if ($search === '') {
            return collect($ventas);
        }
        $term = mb_strtolower(trim($search));
        return collect($ventas)->filter(function (array $venta) use ($term) {
            $cliente     = mb_strtolower((string) data_get($venta, 'cliente.persona.razon_social', ''));
            $comprobante = mb_strtolower((string) data_get($venta, 'comprobante.numero_comprobante', ''));
            $vendedor    = mb_strtolower((string) data_get($venta, 'vendedor.name', ''));
            return str_contains($cliente, $term)
                || str_contains($comprobante, $term)
                || str_contains($vendedor, $term);
        })->values();
    }

    /**
     * Calcula el monto total excluyendo medios no monetarios.
     */
    protected function calcularMontoTotal(iterable $ventas): float
    {
        return collect($ventas)->reduce(function (float $carry, array $venta) {
            $medio = str_replace([' ', '-'], '_', mb_strtolower((string) ($venta['medio_pago'] ?? '')));
            if (in_array($medio, $this->mediosPagoExcluidos, true)) {
                return $carry;
            }
            return $carry + (float) ($venta['total_raw'] ?? $venta['total'] ?? 0);
        }, 0.0);
    }
}
