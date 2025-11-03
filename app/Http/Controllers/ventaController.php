<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVentaRequest;
use App\Models\Cliente;
use App\Models\Comprobante;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\Fidelizacion;
use App\Models\ControlLavado;
use App\Models\ConfiguracionNegocio;
use App\Repositories\ProductoRepository;
use App\Services\VentaService;
use App\Exceptions\VentaException;
use App\Exceptions\StockInsuficienteException;
use App\Exceptions\TarjetaRegaloException;
use Exception;
use App\Exports\VentasExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ventaController extends Controller
{
    public function __construct(
        private ProductoRepository $productoRepo,
        private VentaService $ventaService
    ) {
        $this->middleware('permission:ver-venta|crear-venta|mostrar-venta|eliminar-venta', ['only' => ['index']]);
        $this->middleware('permission:crear-venta', ['only' => ['create', 'store']]);
        $this->middleware('permission:mostrar-venta', ['only' => ['show']]);
        $this->middleware('permission:eliminar-venta', ['only' => ['destroy']]);
        $this->middleware('permission:reporte-diario-venta', ['only' => ['reporteDiario']]);
        $this->middleware('permission:reporte-semanal-venta', ['only' => ['reporteSemanal']]);
        $this->middleware('permission:reporte-mensual-venta', ['only' => ['reporteMensual']]);
        $this->middleware('permission:reporte-personalizado-venta', ['only' => ['reportePersonalizado']]);
        $this->middleware('permission:exportar-reporte-venta', ['only' => ['exportDiario', 'exportSemanal', 'exportMensual', 'exportPersonalizado']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ventas = Venta::with(['comprobante','cliente.persona','user'])
            ->where('estado',1)
            ->latest()
            ->paginate(15)
            ->map(function($venta) {
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
            })
            ->values();

        return view('venta.index',compact('ventas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
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
            \Log::error('Error al procesar venta', [
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
        // Eager load productos para evitar N+1 queries
        $venta->load(['productos', 'cliente.persona', 'comprobante', 'user']);
        
        \Log::channel('ventas')->debug('Show Venta - Eager Loading', [
            'venta_id' => $venta->id,
            'productos_count' => $venta->productos->count(),
            'productos_keys' => $venta->productos->pluck('id')->toArray(),
        ]);
        
        return view('venta.show',compact('venta'));
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
        return $ventasCollection->map(function($venta) {
            // Mapear medio_pago a etiquetas más legibles
            $medioPagoMap = [
                'efectivo' => 'efectivo',
                'tarjeta_credito' => 'tarjeta_credito',
                'tarjeta_regalo' => 'tarjeta_regalo',
                'lavado_gratis' => 'lavado_gratis'
            ];
            
            $medioPago = $medioPagoMap[$venta->medio_pago] ?? $venta->medio_pago ?? '-';
            
            return [
                'id' => $venta->id,
                'comprobante' => [
                    'tipo_comprobante' => $venta->comprobante->tipo_comprobante ?? '-',
                    'numero_comprobante' => $venta->numero_comprobante ?? '-'
                ],
                'cliente' => [
                    'persona' => [
                        'razon_social' => $venta->cliente->persona->razon_social ?? '-',
                        'tipo_persona' => $venta->cliente->persona->tipo_persona ?? '-'
                    ]
                ],
                'fecha_hora' => $venta->fecha_hora,
                'vendedor' => [
                    'name' => $venta->user->name ?? '-'
                ],
                'total' => number_format($venta->total, 2),
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

    public function reporteDiario()
    {
        $ventasRaw = Venta::whereDate('fecha_hora', now()->toDateString())
            ->with(['comprobante', 'cliente.persona', 'user'])
            ->get();

        $ventas = $this->transformarVentasParaTabla($ventasRaw);

        return view('venta.reporte', compact('ventas'))->with('reporte', 'diario');
    }

    public function reporteSemanal()
    {
        $ventasRaw = Venta::whereBetween('fecha_hora', [now()->startOfWeek(), now()->endOfWeek()])
            ->with(['comprobante', 'cliente.persona', 'user'])
            ->get();

        $ventas = $this->transformarVentasParaTabla($ventasRaw);

        return view('venta.reporte', compact('ventas'))->with('reporte', 'semanal');
    }

    public function reporteMensual()
    {
        $ventasRaw = Venta::whereMonth('fecha_hora', now()->month)
            ->with(['comprobante', 'cliente.persona', 'user'])
            ->get();

        $ventas = $this->transformarVentasParaTabla($ventasRaw);

        return view('venta.reporte', compact('ventas'))->with('reporte', 'mensual');
    }

    public function exportDiario()
    {   
    $ventas = Venta::whereDate('fecha_hora', now()->toDateString())
        ->with(['comprobante', 'cliente.persona', 'user'])
        ->get();

    return Excel::download(new VentasExport($ventas), 'ventas_diarias.xlsx');
    }

    public function exportSemanal()
    {
    $ventas = Venta::whereBetween('fecha_hora', [now()->startOfWeek(), now()->endOfWeek()])
        ->with(['comprobante', 'cliente.persona', 'user'])
        ->get()
        ->filter(function($venta) {
            // Excluir ventas con medio_pago 'tarjeta_regalo' o 'lavado_gratis'
            return $venta->medio_pago !== 'tarjeta_regalo' && $venta->medio_pago !== 'lavado_gratis';
        });

    return Excel::download(new VentasExport($ventas), 'ventas_semanales.xlsx');
    }

    public function exportMensual()
    {
    $ventas = Venta::whereMonth('fecha_hora', now()->month)
        ->with(['comprobante', 'cliente.persona', 'user'])
        ->get()
        ->filter(function($venta) {
            return $venta->medio_pago !== 'tarjeta_regalo' && $venta->medio_pago !== 'lavado_gratis';
        });

    return Excel::download(new VentasExport($ventas), 'ventas_mensuales.xlsx');
    }

    public function reportePersonalizado(Request $request)
    {
        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;

        $ventasRaw = Venta::whereBetween('fecha_hora', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->with(['comprobante', 'cliente.persona', 'user'])
            ->get()
            ->filter(function($venta) {
                return $venta->medio_pago !== 'tarjeta_regalo' && $venta->medio_pago !== 'lavado_gratis';
            });

        $ventas = $this->transformarVentasParaTabla($ventasRaw);

        return view('venta.reporte', compact('ventas', 'fechaInicio', 'fechaFin'))->with('reporte', 'personalizado');
    }

    public function exportPersonalizado(Request $request)
    {
        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;

        $ventas = Venta::whereBetween('fecha_hora', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->with(['comprobante', 'cliente.persona', 'user'])
            ->get()
            ->filter(function($venta) {
                return $venta->medio_pago !== 'tarjeta_regalo' && $venta->medio_pago !== 'lavado_gratis';
            });

        return Excel::download(new VentasExport($ventas), "ventas_{$fechaInicio}_a_{$fechaFin}.xlsx");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Venta::where('id',$id)
        ->update([
            'estado' => 0
        ]);

        return redirect()->route('ventas.index')->with('success','Venta eliminada');
    }

    public function ticket(Venta $venta)
    {
        $configuracion = ConfiguracionNegocio::first();
        return view('venta.ticket', compact('venta', 'configuracion'));
    }

    public function printTicket(Venta $venta)
    {
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
}
