<?php

namespace App\Livewire\Ventas;

use App\Livewire\Concerns\FiltraVentas;
use Livewire\Component;

class ReportePeriodo extends Component
{
    use FiltraVentas;
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
        $ventasFiltradas = $this->filtrarVentasPorBusqueda($this->ventas, $this->search);
        $montoTotal = $this->calcularMontoTotal($ventasFiltradas);

        return view('livewire.ventas.reporte-periodo', [
            'ventasFiltradas' => $ventasFiltradas,
            'totalVentas' => $ventasFiltradas->count(),
            'montoTotal' => $montoTotal,
        ]);
    }
}
