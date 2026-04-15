<?php

namespace App\Livewire\Ventas;

use App\Repositories\VentaRepository;
use App\Support\VentaTransformer;
use Carbon\Carbon;
use Livewire\Component;

class ReportePersonalizado extends Component
{
    public string $fechaInicio = '';
    public string $fechaFin = '';
    public string $search = '';

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $ventas = [];

    public function mount(?string $fechaInicio = null, ?string $fechaFin = null): void
    {
        $this->fechaInicio = trim((string) ($fechaInicio ?? ''));
        $this->fechaFin = trim((string) ($fechaFin ?? ''));

        if ($this->fechaInicio !== '' && $this->fechaFin !== '') {
            $this->loadVentas();
        }
    }

    protected function rules(): array
    {
        return [
            'fechaInicio' => ['required', 'date', 'before_or_equal:fechaFin'],
            'fechaFin' => ['required', 'date', 'after_or_equal:fechaInicio'],
        ];
    }

    public function filtrar(): void
    {
        $this->validate();
        $this->loadVentas();
    }

    public function resetFiltros(): void
    {
        $this->fechaInicio = '';
        $this->fechaFin = '';
        $this->search = '';
        $this->ventas = [];
    }

    protected function loadVentas(): void
    {
        $repo = app(VentaRepository::class);
        $transformer = app(VentaTransformer::class);

        $ventasRaw = $repo->obtenerPorRango(
            Carbon::parse($this->fechaInicio),
            Carbon::parse($this->fechaFin)
        )->reject(fn ($venta) => in_array($venta->medio_pago, ['tarjeta_regalo', 'lavado_gratis'], true));

        $this->ventas = $transformer->transformCollection($ventasRaw)->all();
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

        $total = $ventasFiltradas->reduce(function (float $carry, array $venta) {
            return $carry + (float) str_replace(',', '', (string) ($venta['total'] ?? '0'));
        }, 0.0);

        return view('livewire.ventas.reporte-personalizado', [
            'ventasFiltradas' => $ventasFiltradas,
            'totalVentas' => $ventasFiltradas->count(),
            'montoTotal' => $total,
        ]);
    }
}
