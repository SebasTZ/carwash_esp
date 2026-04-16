<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVentaRequest;
use App\Models\Cliente;
use App\Models\Comprobante;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\Fidelizacion;
use App\Models\ConfiguracionNegocio;
use App\Repositories\ProductoRepository;
use App\Repositories\VentaRepository;
use App\Services\VentaService;
use App\Support\VentaTransformer;
use Carbon\Carbon;
use App\Exceptions\VentaException;
use App\Exceptions\StockInsuficienteException;
use App\Exceptions\TarjetaRegaloException;
use Exception;
use App\Exports\VentasExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ventaController extends Controller
{
    private ProductoRepository $productoRepo;
    private VentaRepository $ventaRepo;
    private VentaService $ventaService;
    private VentaTransformer $ventaTransformer;

    public function __construct(
        ProductoRepository $productoRepo,
        VentaRepository $ventaRepo,
        VentaService $ventaService,
        VentaTransformer $ventaTransformer
    ) {
        $this->productoRepo = $productoRepo;
        $this->ventaService = $ventaService;
        $this->ventaRepo = $ventaRepo;
        $this->ventaTransformer = $ventaTransformer;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Venta::class);

        $ventas = Venta::with(['comprobante', 'cliente.persona', 'user'])
            ->where('estado', 1)
            ->when(
                !$this->canManageAllVentas(),
                fn ($query) => $query->where('user_id', auth()->id())
            )
            ->latest()
            ->paginate(15);

        // Transformar solo los elementos, manteniendo la paginación
        /** @var \Illuminate\Pagination\LengthAwarePaginator $ventas */
        $ventas->getCollection()->transform(function($venta) {
            return [
                'id' => $venta->id,
                'comprobante' => [
                    'tipo_comprobante' => $venta->comprobante?->tipo_comprobante,
                    'numero_comprobante' => $venta->numero_comprobante
                ],
                'cliente' => $venta->cliente,
                'fecha_hora' => $venta->fecha_hora,
                'vendedor' => $venta->user,
                'total' => number_format($venta->total, 2),
                'medio_pago' => $venta->medio_pago,
                'servicio_lavado' => $venta->servicio_lavado ?? false,
            ];
        });

        return view('venta.index',compact('ventas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Venta::class);

        // Usar el repository optimizado con caché
        $productos = $this->productoRepo->obtenerParaVenta();

        $clientes = Cliente::activos()->get();
        $comprobantes = Comprobante::all();
        $tarjetas_regalo = \App\Models\TarjetaRegalo::activas()->get();
        $fidelidades = Fidelizacion::activas()->get();

        return view('venta.create', compact('productos', 'clientes', 'comprobantes', 'tarjetas_regalo', 'fidelidades'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVentaRequest $request)
    {
        $this->authorize('create', Venta::class);

        try {
            // Procesar la venta usando el servicio
            $venta = $this->ventaService->procesarVenta($request->validated());

            return redirect()
                ->route('ventas.index')
                ->with('success', "Venta #{$venta->numero_comprobante} realizada exitosamente");

        } catch (VentaException $e) {
            return redirect()
                ->route('ventas.create')
                ->with('error', $e->getMessage())
                ->withInput();

        } catch (StockInsuficienteException $e) {
            return redirect()
                ->route('ventas.create')
                ->with('error', "Stock insuficiente: {$e->getMessage()}")
                ->withInput();

        } catch (TarjetaRegaloException $e) {
            return redirect()
                ->route('ventas.create')
                ->with('error', "Error con tarjeta de regalo: {$e->getMessage()}")
                ->withInput();

        } catch (Exception $e) {
            Log::error('Error al procesar venta', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('ventas.create')
                ->with('error', 'Error inesperado al realizar la venta. Por favor, intente nuevamente.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Venta $venta)
    {
        $this->authorize('view', $venta);

        $venta->load(['productos', 'cliente.persona', 'comprobante', 'user']);
        return view('venta.show', compact('venta'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Transforma una colección de ventas para mostrar en tabla
     */
    private function transformarVentasParaTabla($ventasCollection)
    {
        return $this->ventaTransformer->transformCollection($ventasCollection);
    }

    public function reporteDiario()
    {
        $this->authorize('viewReports', Venta::class);

        $ventas = $this->transformarVentasParaTabla(
            $this->filtrarVentasPorPropietario($this->ventaRepo->obtenerDelDia())
        );

        return view('venta.reporte', compact('ventas'))->with('reporte', 'diario');
    }

    public function reporteSemanal()
    {
        $this->authorize('viewReports', Venta::class);

        $ventas = $this->transformarVentasParaTabla(
            $this->filtrarVentasPorPropietario($this->ventaRepo->obtenerDeLaSemana())
        );

        return view('venta.reporte', compact('ventas'))->with('reporte', 'semanal');
    }

    public function reporteMensual()
    {
        $this->authorize('viewReports', Venta::class);

        $ventas = $this->transformarVentasParaTabla(
            $this->filtrarVentasPorPropietario($this->ventaRepo->obtenerDelMes())
        );

        return view('venta.reporte', compact('ventas'))->with('reporte', 'mensual');
    }

    public function exportDiario()
    {
        $this->authorize('export', Venta::class);

        $ventas = $this->filtrarVentasPorPropietario($this->ventaRepo->obtenerDelDia());

        return Excel::download(new VentasExport($ventas), 'ventas_diarias.xlsx');
    }

    public function exportSemanal()
    {
        $this->authorize('export', Venta::class);

        $ventas = Venta::whereBetween('fecha_hora', [now()->startOfWeek(), now()->endOfWeek()])
            ->whereNotIn('medio_pago', ['tarjeta_regalo', 'lavado_gratis'])
            ->when(
                !$this->canManageAllVentas(),
                fn ($query) => $query->where('user_id', auth()->id())
            )
            ->with(['comprobante', 'cliente.persona', 'user'])
            ->get();

        return Excel::download(new VentasExport($ventas), 'ventas_semanales.xlsx');
    }

    public function exportMensual()
    {
        $this->authorize('export', Venta::class);

        $ventas = Venta::whereMonth('fecha_hora', now()->month)
            ->whereNotIn('medio_pago', ['tarjeta_regalo', 'lavado_gratis'])
            ->when(
                !$this->canManageAllVentas(),
                fn ($query) => $query->where('user_id', auth()->id())
            )
            ->with(['comprobante', 'cliente.persona', 'user'])
            ->get();

        return Excel::download(new VentasExport($ventas), 'ventas_mensuales.xlsx');
    }

    public function reportePersonalizado(Request $request)
    {
        $this->authorize('viewReports', Venta::class);

        $fechaInicio = $request->query('fecha_inicio');
        $fechaFin = $request->query('fecha_fin');

        $ventas = collect();

        if (($fechaInicio !== null && $fechaInicio !== '') || ($fechaFin !== null && $fechaFin !== '')) {
            $request->validate([
                'fecha_inicio' => 'required|date|before_or_equal:fecha_fin',
                'fecha_fin'    => 'required|date',
            ]);

            $ventasRaw = $this->ventaRepo->obtenerPorRango(Carbon::parse($fechaInicio), Carbon::parse($fechaFin))
                ->reject(fn($v) => in_array($v->medio_pago, ['tarjeta_regalo', 'lavado_gratis'], true));

            $ventasRaw = $this->filtrarVentasPorPropietario($ventasRaw);

            $ventas = $this->transformarVentasParaTabla($ventasRaw);
        }

        return view('venta.reporte', compact('ventas', 'fechaInicio', 'fechaFin'))->with('reporte', 'personalizado');
    }

    public function exportPersonalizado(Request $request)
    {
        $this->authorize('export', Venta::class);

        $request->validate([
            'fecha_inicio' => 'required|date|before_or_equal:fecha_fin',
            'fecha_fin'    => 'required|date',
        ]);

        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;

        $ventas = $this->ventaRepo->obtenerPorRango(Carbon::parse($fechaInicio), Carbon::parse($fechaFin))
            ->reject(fn($v) => in_array($v->medio_pago, ['tarjeta_regalo', 'lavado_gratis']));

        $ventas = $this->filtrarVentasPorPropietario($ventas);

        return Excel::download(new VentasExport($ventas), "ventas_{$fechaInicio}_a_{$fechaFin}.xlsx");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Venta $venta)
    {
        $this->authorize('delete', $venta);

        try {
            $this->ventaService->anularVenta($venta, 'Anulada por usuario ' . auth()->user()->name);
            return redirect()->route('ventas.index')->with('success', 'Venta anulada correctamente. Stock y fidelización revertidos.');
        } catch (Exception $e) {
            Log::error('Error al anular venta', ['venta_id' => $venta->id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Error al anular la venta: ' . $e->getMessage());
        }
    }

    public function ticket(Venta $venta)
    {
        $this->authorize('view', $venta);

        $configuracion = ConfiguracionNegocio::first();
        return view('venta.ticket', compact('venta', 'configuracion'));
    }

    public function printTicket(Venta $venta)
    {
        $this->authorize('view', $venta);

        try {
            $configuracion = ConfiguracionNegocio::first();
            $pdf = Pdf::loadView('venta.ticket_pdf', compact('venta', 'configuracion'));
            $fileName = 'ticket_' . $venta->id . '.pdf';
            Storage::put('public/' . $fileName, $pdf->output());

            return Storage::download('public/' . $fileName);
        } catch (Exception $e) {
            return redirect()->route('ventas.show', $venta)->with('error', 'Error al imprimir el ticket: ' . $e->getMessage());
        }
    }

    public function buscarProductos(Request $request)
{
    $this->authorize('searchProducts', Venta::class);

    $query = $request->input('query', '');

    // Productos normales con stock
    $productosNormales = Producto::leftJoin('compra_producto as cpr', function ($join) {
            $join->on('productos.id', '=', 'cpr.producto_id')
                ->whereRaw('cpr.created_at = (
                    SELECT MAX(created_at) 
                    FROM compra_producto 
                    WHERE producto_id = productos.id
                )');
        })
        ->where('productos.estado', 1)
        ->where(function ($q) use ($query) {
            $q->where('productos.nombre', 'like', "%$query%")
              ->orWhere('productos.codigo', 'like', "%$query%");
        })
        ->where('productos.stock', '>', 0)
        ->where('productos.es_servicio_lavado', false)
        ->select(
            'productos.id',
            'productos.nombre', 
            'productos.codigo', 
            'productos.stock', 
            'cpr.precio_venta',
            'productos.es_servicio_lavado'
        )
        ->take(10)
        ->get();

    // Servicios de lavado con su precio real
    $serviciosLavado = Producto::where('estado', 1)
        ->where(function ($q) use ($query) {
            $q->where('nombre', 'like', "%$query%")
              ->orWhere('codigo', 'like', "%$query%");
        })
        ->where('es_servicio_lavado', true)
        ->select(
            'id',
            'nombre',
            'codigo',
            'stock',
            'precio_venta',
            'es_servicio_lavado'
        )
        ->take(10)
        ->get();

    // Combinar los resultados
    $productos = $productosNormales->concat($serviciosLavado);

    return response()->json($productos);
}

    /**
     * Valida si un cliente puede usar lavado gratis (tiene suficientes lavados acumulados)
     * GET /validar-fidelizacion-lavado/{cliente_id}
     */
    public function validarFidelizacionLavado($cliente_id)
    {
        $this->authorize('validateFidelizacion', Venta::class);

        try {
            $cliente = Cliente::findOrFail($cliente_id);
            
            // Necesita 10 lavados acumulados para 1 lavado gratis
            $lavadosNecesarios = 10;
            $lavadosActuales = $cliente->lavados_acumulados ?? 0;
            
            // Si tiene menos de 10 lavados acumulados, no puede usar lavado gratis
            if ($lavadosActuales < $lavadosNecesarios) {
                $lavadosFaltantes = $lavadosNecesarios - $lavadosActuales;
                return response()->json([
                    'valido' => false,
                    'lavados_actuales' => $lavadosActuales,
                    'lavados_necesarios' => $lavadosNecesarios,
                    'lavados_faltantes' => $lavadosFaltantes,
                    'mensaje' => "El cliente tiene {$lavadosActuales} lavado(s) acumulado(s). Le faltan {$lavadosFaltantes} para obtener 1 lavado gratis."
                ], 200);
            }

            // ✅ Tiene suficientes lavados acumulados
            $lavadosDisponibles = intdiv($lavadosActuales, $lavadosNecesarios);
            
            return response()->json([
                'valido' => true,
                'lavados_actuales' => $lavadosActuales,
                'lavados_necesarios' => $lavadosNecesarios,
                'lavados_disponibles' => $lavadosDisponibles,
                'mensaje' => "¡Excelente! El cliente tiene {$lavadosActuales} lavado(s) acumulado(s). Puede obtener {$lavadosDisponibles} lavado(s) gratis."
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'valido' => false,
                'mensaje' => 'Error al validar: ' . $e->getMessage()
            ], 500);
        }
    }

    private function canManageAllVentas(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'superadmin', 'administrador']) ?? false;
    }

    private function filtrarVentasPorPropietario(iterable $ventas): \Illuminate\Support\Collection
    {
        $collection = collect($ventas);

        if ($this->canManageAllVentas()) {
            return $collection->values();
        }

        return $collection
            ->filter(fn ($venta) => (int) ($venta->user_id ?? 0) === (int) auth()->id())
            ->values();
    }
}
