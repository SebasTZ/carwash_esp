<?php

namespace App\Livewire\Ventas;

use App\Livewire\Concerns\AuthorizesLivewirePermissions;
use App\Livewire\Concerns\FiltraVentas;
use App\Repositories\VentaRepository;
use App\Support\VentaTransformer;
use Carbon\Carbon;
use Livewire\Component;

class ReportePersonalizado extends Component
{
    use AuthorizesLivewirePermissions;
    use FiltraVentas;

    public string $fechaInicio = '';
    public string $fechaFin = '';
    public string $search = '';

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $ventas = [];

    public function mount(?string $fechaInicio = null, ?string $fechaFin = null): void
    {
        $this->ensurePermission('reporte-personalizado-venta');

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
        $this->ensurePermission('reporte-personalizado-venta');
        $this->validate();
        $this->loadVentas();
    }

    public function resetFiltros(): void
    {
        $this->ensurePermission('reporte-personalizado-venta');

        $this->fechaInicio = '';
        $this->fechaFin = '';
        $this->search = '';
        $this->ventas = [];
    }

    protected function loadVentas(): void
    {
        $repo = app(VentaRepository::class);
        $transformer = app(VentaTransformer::class);

        try {
            $fechaInicio = Carbon::createFromFormat('Y-m-d', $this->fechaInicio);
            $fechaFin = Carbon::createFromFormat('Y-m-d', $this->fechaFin);
        } catch (\Throwable $e) {
            $this->ventas = [];
            return;
        }

        $ventasRaw = $repo->obtenerPorRango(
            $fechaInicio,
            $fechaFin
        )->reject(fn ($venta) => in_array($venta->medio_pago, ['tarjeta_regalo', 'lavado_gratis'], true));

        if (!$this->isPrivilegedUser()) {
            $ventasRaw = $ventasRaw->filter(fn ($venta) => $venta->user_id === auth()->id());
        }

        $this->ventas = $transformer->transformCollection($ventasRaw)->all();
    }

    public function render()
    {
        $ventasFiltradas = $this->filtrarVentasPorBusqueda($this->ventas, $this->search);
        $montoTotal = $this->calcularMontoTotal($ventasFiltradas);

        return view('livewire.ventas.reporte-personalizado', [
            'ventasFiltradas' => $ventasFiltradas,
            'totalVentas' => $ventasFiltradas->count(),
            'montoTotal' => $montoTotal,
        ]);
    }
}
