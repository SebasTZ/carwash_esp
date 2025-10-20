<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVentaRequest;
use App\Services\VentaService;
use App\Repositories\VentaRepository;
use App\Repositories\ProductoRepository;
use App\Models\Cliente;
use App\Models\Comprobante;
use App\Models\Venta;
use App\Exceptions\VentaException;
use Illuminate\Http\Request;

/**
 * Controlador REFACTORIZADO de Ventas
 * 
 * Este es un ejemplo de cómo debería quedar el controlador después del refactor
 * Se eliminó toda la lógica de negocio y se delegó a servicios y repositorios
 */
class VentaControllerRefactored extends Controller
{
    public function __construct(
        private VentaService $ventaService,
        private VentaRepository $ventaRepository,
        private ProductoRepository $productoRepository
    ) {
        $this->middleware('permission:ver-venta|crear-venta|mostrar-venta|eliminar-venta', ['only' => ['index']]);
        $this->middleware('permission:crear-venta', ['only' => ['create', 'store']]);
        $this->middleware('permission:mostrar-venta', ['only' => ['show']]);
        $this->middleware('permission:eliminar-venta', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $ventas = $this->ventaRepository->obtenerConFiltros([
            'estado' => $request->get('estado', 1),
            'cliente_id' => $request->get('cliente_id'),
            'medio_pago' => $request->get('medio_pago'),
            'fecha_desde' => $request->get('fecha_desde'),
            'fecha_hasta' => $request->get('fecha_hasta'),
            'per_page' => 15,
        ]);

        return view('venta.index', compact('ventas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Usar el repositorio optimizado con caché
        $productos = $this->productoRepository->obtenerParaVenta();

        $clientes = Cliente::whereHas('persona', function ($query) {
            $query->where('estado', 1);
        })->get();

        $comprobantes = Comprobante::all();

        return view('venta.create', compact('productos', 'clientes', 'comprobantes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVentaRequest $request)
    {
        try {
            // Toda la lógica compleja está en el servicio
            $venta = $this->ventaService->procesarVenta($request->validated());

            return redirect()
                ->route('ventas.show', $venta)
                ->with('success', 'Venta registrada exitosamente');

        } catch (VentaException $e) {
            return redirect()
                ->route('ventas.create')
                ->with('error', $e->getMessage())
                ->withInput();

        } catch (\Exception $e) {
            \Log::error('Error al procesar venta', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('ventas.create')
                ->with('error', 'Ocurrió un error al procesar la venta. Por favor intente nuevamente.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Venta $venta)
    {
        $venta->load([
            'cliente.persona',
            'productos',
            'comprobante',
            'user',
        ]);

        return view('venta.show', compact('venta'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Venta $venta, Request $request)
    {
        $request->validate([
            'motivo' => 'required|string|max:500',
        ]);

        try {
            $this->ventaService->anularVenta($venta, $request->motivo);

            return redirect()
                ->route('ventas.index')
                ->with('success', 'Venta anulada exitosamente');

        } catch (\Exception $e) {
            return redirect()
                ->route('ventas.show', $venta)
                ->with('error', 'No se pudo anular la venta: ' . $e->getMessage());
        }
    }

    /**
     * Reportes simplificados usando el repositorio
     */
    public function reporteDiario()
    {
        $ventas = $this->ventaRepository->obtenerDelDia();
        $totales = $this->ventaRepository->obtenerTotalesPorPeriodo(today(), today());

        return view('venta.reporte', compact('ventas', 'totales'))
            ->with('reporte', 'diario');
    }

    public function reporteSemanal()
    {
        $ventas = $this->ventaRepository->obtenerDeLaSemana();
        $totales = $this->ventaRepository->obtenerTotalesPorPeriodo(
            now()->startOfWeek(),
            now()->endOfWeek()
        );

        return view('venta.reporte', compact('ventas', 'totales'))
            ->with('reporte', 'semanal');
    }

    public function reporteMensual()
    {
        $ventas = $this->ventaRepository->obtenerDelMes();
        $totales = $this->ventaRepository->obtenerTotalesPorPeriodo(
            now()->startOfMonth(),
            now()->endOfMonth()
        );

        return view('venta.reporte', compact('ventas', 'totales'))
            ->with('reporte', 'mensual');
    }

    public function reportePersonalizado(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $fechaInicio = \Carbon\Carbon::parse($request->fecha_inicio);
        $fechaFin = \Carbon\Carbon::parse($request->fecha_fin);

        $ventas = $this->ventaRepository->obtenerPorRango($fechaInicio, $fechaFin);
        $totales = $this->ventaRepository->obtenerTotalesPorPeriodo($fechaInicio, $fechaFin);

        return view('venta.reporte', compact('ventas', 'totales'))
            ->with('reporte', 'personalizado')
            ->with('fecha_inicio', $fechaInicio)
            ->with('fecha_fin', $fechaFin);
    }
}
