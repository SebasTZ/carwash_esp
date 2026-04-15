<?php

namespace App\Livewire\Ventas;

use Livewire\Component;

class ReportePeriodo extends Component
{
    public string $reporte = 'diario';
    public string $search = '';

    /**
     * @var array<int, array<string, mixed>>
     */
    public $ventas = [];

    public function mount(string $reporte = 'diario', $ventas = []): void
    {
        $this->reporte = trim((string) $reporte) !== '' ? (string) $reporte : 'diario';
        $this->ventas = collect($ventas)->values()->all();
    }

    public function render()
    {
        $ventasFiltradas = collect($this->ventas);
        $search = mb_strtolower(trim($this->search));

        if ($search !== '') {
            $ventasFiltradas = $ventasFiltradas->filter(function (array $venta) use ($search) {
                $cliente = mb_strtolower((string) data_get($venta, 'cliente.persona.razon_social', ''));
                $comprobante = mb_strtolower((string) data_get($venta, 'comprobante.numero_comprobante', ''));
                $vendedor = mb_strtolower((string) data_get($venta, 'vendedor.name', ''));

                return str_contains($cliente, $search)
                    || str_contains($comprobante, $search)
                    || str_contains($vendedor, $search);
            })->values();
        }

        $montoTotal = $ventasFiltradas->reduce(function (float $carry, array $venta) {
            $medioPago = (string) ($venta['medio_pago'] ?? '');
            $normalized = str_replace([' ', '-'], '_', mb_strtolower($medioPago));

            if (in_array($normalized, ['tarjeta_regalo', 'lavado_gratis', 'lavado_gratis_(fidelidad)'], true)) {
                return $carry;
            }

            return $carry + (float) str_replace(',', '', (string) ($venta['total'] ?? '0'));
        }, 0.0);

        return view('livewire.ventas.reporte-periodo', [
            'ventasFiltradas' => $ventasFiltradas,
            'totalVentas' => $ventasFiltradas->count(),
            'montoTotal' => $montoTotal,
        ]);
    }
}
