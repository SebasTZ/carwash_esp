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
    function __construct()
    {
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
        ->paginate(15);

        return view('venta.index',compact('ventas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
{
    // Primero obtenemos los productos normales con su último precio de compra
    $subquery = DB::table('compra_producto')
        ->select('producto_id', DB::raw('MAX(created_at) as max_created_at'))
        ->groupBy('producto_id');

    $productosNormales = Producto::join('compra_producto as cpr', function ($join) use ($subquery) {
        $join->on('cpr.producto_id', '=', 'productos.id')
            ->whereIn('cpr.created_at', function ($query) use ($subquery) {
                $query->select('max_created_at')
                    ->fromSub($subquery, 'subquery')
                    ->whereRaw('subquery.producto_id = cpr.producto_id');
            });
    })
        ->select('productos.nombre', 'productos.id', 'productos.stock', 'productos.codigo', 'cpr.precio_venta', 'productos.es_servicio_lavado')
        ->where('productos.estado', 1)
        ->where('productos.stock', '>', 0)
        ->where('productos.es_servicio_lavado', false)
        ->get();

    // Luego obtenemos los servicios de lavado
    $serviciosLavado = Producto::select(
            'nombre',
            'id',
            'stock',
            'codigo',
            'precio_venta', // Ya no usamos un valor predeterminado
            'es_servicio_lavado'
        )
        ->where('estado', 1)
        ->where('es_servicio_lavado', true)
        ->get();

    // Combinamos ambas colecciones
    $productos = $productosNormales->concat($serviciosLavado);

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
        try{
            DB::beginTransaction();

            $medioPago = $request['medio_pago'];
            $cliente = Cliente::find($request['cliente_id']);
            $totalVenta = $request['total'];
            $lavadoGratis = false;
            $tarjetaRegaloId = null;

            // Validación y lógica de métodos de pago
            if ($medioPago === 'tarjeta_regalo') {
                $codigo = $request['tarjeta_regalo_codigo'];
                $tarjeta = \App\Models\TarjetaRegalo::where('codigo', $codigo)->where('estado', 'activa')->first();
                if (!$tarjeta) {
                    return redirect()->route('ventas.create')->with('error', 'Tarjeta de regalo no válida o no activa.');
                }
                if ($tarjeta->saldo_actual < $totalVenta) {
                    return redirect()->route('ventas.create')->with('error', 'Saldo insuficiente en la tarjeta de regalo.');
                }
                // Descontar saldo y actualizar estado si corresponde
                $tarjeta->saldo_actual -= $totalVenta;
                if ($tarjeta->saldo_actual <= 0) {
                    $tarjeta->saldo_actual = 0;
                    $tarjeta->estado = 'usada';
                }
                $tarjeta->save();
                $tarjetaRegaloId = $tarjeta->id;
            } elseif ($medioPago === 'lavado_gratis') {
                if ($cliente->lavados_acumulados < 10) {
                    return redirect()->route('ventas.create')->with('error', 'El cliente no tiene suficientes lavados acumulados para un lavado gratuito.');
                }
                $lavadoGratis = true;
                $cliente->lavados_acumulados = 0;
                $cliente->save();
            } else {
                // Efectivo o tarjeta de crédito: sumar lavado acumulado si es servicio de lavado
                if ($request['servicio_lavado'] ?? 0) {
                    $cliente->lavados_acumulados += 1;
                    $cliente->save();
                }
            }

            //Obtener el tipo de comprobante
            $comprobante = Comprobante::find($request['comprobante_id']);
            $numero_comprobante = Venta::generarNumeroComprobante($request['comprobante_id']);
            $horarioLavado = isset($request['horario_lavado']) && $request['horario_lavado']
                ? now()->format('Y-m-d') . ' ' . $request['horario_lavado'] . ':00'
                : null;

            // Llenar mi tabla venta
            $ventaData = array_merge($request->validated(), [
                'numero_comprobante' => $numero_comprobante,
                'servicio_lavado' => $request['servicio_lavado'] ?? 0,
                'horario_lavado' => $horarioLavado,
                'lavado_gratis' => $lavadoGratis,
                'tarjeta_regalo_id' => $tarjetaRegaloId,
                'medio_pago' => $medioPago,
            ]);

            // Verificar si el servicio de lavado está habilitado y si el horario de lavado está presente
            if (($request['servicio_lavado'] ?? 0) == 1 && empty($request['horario_lavado'])) {
                return redirect()->route('ventas.create')->with('error', 'Debe proporcionar un horario de culminación del lavado.');
            }

            $venta = Venta::create($ventaData);

            //Llenar mi tabla venta_producto
            //1. Recuperar los arrays
            $arrayProducto_id = $request['arrayidproducto'] ?? [];
            $arrayCantidad = $request['arraycantidad'] ?? [];
            $arrayPrecioVenta = $request['arrayprecioventa'] ?? [];
            $arrayDescuento = $request['arraydescuento'] ?? [];

            //2.Realizar el llenado
            $siseArray = count($arrayProducto_id);
            $cont = 0;

            while($cont < $siseArray){
                $venta->productos()->syncWithoutDetaching([
                    $arrayProducto_id[$cont] => [
                        'cantidad' => $arrayCantidad[$cont],
                        'precio_venta' => $arrayPrecioVenta[$cont],
                        'descuento' => $arrayDescuento[$cont]
                    ]
                ]);

                //Actualizar stock
                $producto = Producto::find($arrayProducto_id[$cont]);
                $stockActual = $producto->stock;
                $cantidad = intval($arrayCantidad[$cont]);

                // Solo actualizar stock si NO es un servicio de lavado
                if (!$producto->es_servicio_lavado) {
                    DB::table('productos')
                    ->where('id', $producto->id)
                    ->update([
                        'stock' => $stockActual - $cantidad
                    ]);
                }

                $cont++;
            }

            // Agregar puntos de fidelización
            $cliente = $venta->cliente;
            $puntos = $venta->total * 0.1; // Ejemplo: 10% del total de la venta en puntos

            if ($cliente->fidelizacion) {
                $cliente->fidelizacion->increment('puntos', $puntos);
            } else {
                Fidelizacion::create([
                    'cliente_id' => $cliente->id,
                    'puntos' => $puntos,
                ]);
            }

            // Crear registro en control_lavados si corresponde
            if (($request['servicio_lavado'] ?? 0) == 1) {
                ControlLavado::create([
                    'venta_id' => $venta->id,
                    'cliente_id' => $venta->cliente_id,
                    'lavador_id' => null,
                    'hora_llegada' => now(),
                    'horario_estimado' => $venta->horario_lavado,
                    'inicio_lavado' => null,
                    'fin_lavado' => null,
                    'inicio_interior' => null,
                    'fin_interior' => null,
                    'hora_final' => null,
                    'tiempo_total' => null,
                    'estado' => 'En espera',
                ]);
            }

            DB::commit();
        }catch(Exception $e){
            DB::rollBack();
            return redirect()->route('ventas.create')->with('error', 'Error al realizar la venta: ' . $e->getMessage());
        }

        return redirect()->route('ventas.index')->with('success','Venta exitosa');
    }

    /**
     * Display the specified resource.
     */
    public function show(Venta $venta)
    {
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

    public function reporteDiario()
    {
    $ventas = Venta::whereDate('fecha_hora', now()->toDateString())
        ->with(['comprobante', 'cliente.persona', 'user'])
        ->get();

    return view('venta.reporte', compact('ventas'))->with('reporte', 'diario');
    }

    public function reporteSemanal()
    {
    $ventas = Venta::whereBetween('fecha_hora', [now()->startOfWeek(), now()->endOfWeek()])
        ->with(['comprobante', 'cliente.persona', 'user'])
        ->get();

    return view('venta.reporte', compact('ventas'))->with('reporte', 'semanal');
    }

    public function reporteMensual()
    {
    $ventas = Venta::whereMonth('fecha_hora', now()->month)
        ->with(['comprobante', 'cliente.persona', 'user'])
        ->get();

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

        $ventas = Venta::whereBetween('fecha_hora', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->with(['comprobante', 'cliente.persona', 'user'])
            ->get()
            ->filter(function($venta) {
                return $venta->medio_pago !== 'tarjeta_regalo' && $venta->medio_pago !== 'lavado_gratis';
            });

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
}
