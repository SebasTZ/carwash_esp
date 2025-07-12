<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompraRequest;
use App\Models\Compra;
use App\Models\Comprobante;
use App\Models\Producto;
use App\Models\Proveedore;
use Exception;
use App\Exports\ComprasExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class compraController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-compra|crear-compra|mostrar-compra|eliminar-compra', ['only' => ['index']]);
        $this->middleware('permission:crear-compra', ['only' => ['create', 'store']]);
        $this->middleware('permission:mostrar-compra', ['only' => ['show']]);
        $this->middleware('permission:eliminar-compra', ['only' => ['destroy']]);
        $this->middleware('permission:reporte-diario-compra', ['only' => ['reporteDiario']]);
        $this->middleware('permission:reporte-semanal-compra', ['only' => ['reporteSemanal']]);
        $this->middleware('permission:reporte-mensual-compra', ['only' => ['reporteMensual']]);
        $this->middleware('permission:reporte-personalizado-compra', ['only' => ['reportePersonalizado']]);
        $this->middleware('permission:exportar-reporte-compra', ['only' => ['exportDiario', 'exportSemanal', 'exportMensual', 'exportPersonalizado']]);
    }

    public function index()
    {
        $compras = Compra::with('comprobante','proveedore.persona')
        ->where('estado',1)
        ->latest()
        ->get(); 
        return view('compra.index',compact('compras'));
    }

    public function create()
    {
        $proveedores = Proveedore::whereHas('persona',function($query){
            $query->where('estado',1);
        })->get();
        $comprobantes = Comprobante::all();
        $productos = Producto::where('estado',1)->where('es_servicio_lavado', false)->limit(10)->get();
        return view('compra.create',compact('proveedores','comprobantes','productos'));
    }

    public function store(StoreCompraRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Validar comprobante duplicado por proveedor
            $existeComprobante = Compra::where('numero_comprobante', $data['numero_comprobante'])
                                     ->where('proveedore_id', $data['proveedore_id'])
                                     ->exists();
            
            if ($existeComprobante) {
                return redirect()->back()->with('error', 'Ya existe una compra con este número de comprobante para este proveedor.');
            }

            // Llenar tabla compras
            $compra = Compra::create(array_merge(
                $data,
                ['created_at' => now()]
            ));

            // Llenar tabla compra_producto
            $arrayProducto_id = $request['arrayidproducto'] ?? [];
            $arrayCantidad = $request['arraycantidad'] ?? [];
            $arrayPrecioCompra = $request['arraypreciocompra'] ?? [];
            $arrayPrecioVenta = $request['arrayprecioventa'] ?? [];

            $siseArray = count($arrayProducto_id);
            $cont = 0;

            while ($cont < $siseArray) {
                $compra->productos()->syncWithoutDetaching([
                    $arrayProducto_id[$cont] => [
                        'cantidad' => $arrayCantidad[$cont],
                        'precio_compra' => $arrayPrecioCompra[$cont],
                        'precio_venta' => $arrayPrecioVenta[$cont]
                    ]
                ]);

                // Actualizar el stock
                $producto = Producto::find($arrayProducto_id[$cont]);
                $stockActual = $producto->stock;
                $stockNuevo = intval($arrayCantidad[$cont]);

                DB::table('productos')
                    ->where('id', $producto->id)
                    ->update([
                        'stock' => $stockActual + $stockNuevo
                    ]);

                $cont++;
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al realizar la compra: ' . $e->getMessage());
        }

        return redirect()->route('compras.index')->with('success', 'Compra exitosa');
    }

    public function show(Compra $compra)
    {
        return view('compra.show',compact('compra'));
    }

    public function reporteDiario()
    {
        $compras = Compra::whereDate('fecha_hora', now()->toDateString())
            ->with(['comprobante', 'proveedore.persona'])
            ->get();

        return view('compra.reporte', compact('compras'))->with('reporte', 'diario');
    }

    public function reporteSemanal()
    {
        $compras = Compra::whereBetween('fecha_hora', [now()->startOfWeek(), now()->endOfWeek()])
            ->with(['comprobante', 'proveedore.persona'])
            ->get();

        return view('compra.reporte', compact('compras'))->with('reporte', 'semanal');
    }

    public function reporteMensual()
    {
        $compras = Compra::whereMonth('fecha_hora', now()->month)
            ->with(['comprobante', 'proveedore.persona'])
            ->get();

        return view('compra.reporte', compact('compras'))->with('reporte', 'mensual');
    }

    public function reportePersonalizado(Request $request)
    {
        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;

        $compras = Compra::whereBetween('fecha_hora', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->with(['comprobante', 'proveedore.persona'])
            ->get();

        return view('compra.reporte', compact('compras', 'fechaInicio', 'fechaFin'))->with('reporte', 'personalizado');
    }

    public function exportDiario()
    {   
        $compras = Compra::whereDate('fecha_hora', now()->toDateString())
            ->with(['comprobante', 'proveedore.persona'])
            ->get();

        return Excel::download(new ComprasExport($compras), 'compras_diarias.xlsx');
    }

    public function exportSemanal()
    {
        $compras = Compra::whereBetween('fecha_hora', [now()->startOfWeek(), now()->endOfWeek()])
            ->with(['comprobante', 'proveedore.persona'])
            ->get();

        return Excel::download(new ComprasExport($compras), 'compras_semanales.xlsx');
    }

    public function exportMensual()
    {
        $compras = Compra::whereMonth('fecha_hora', now()->month)
            ->with(['comprobante', 'proveedore.persona'])
            ->get();

        return Excel::download(new ComprasExport($compras), 'compras_mensuales.xlsx');
    }

    public function exportPersonalizado(Request $request)
    {
        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;

        $compras = Compra::whereBetween('fecha_hora', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->with(['comprobante', 'proveedore.persona'])
            ->get();

        return Excel::download(new ComprasExport($compras), "compras_{$fechaInicio}_a_{$fechaFin}.xlsx");
    }

    public function destroy(string $id)
    {
        Compra::where('id',$id)
        ->update([
            'estado' => 0
        ]);

        return redirect()->route('compras.index')->with('success','Compra eliminada');
    }

    public function buscarProductos(Request $request)
    {
        $query = $request->input('query', '');

        $productos = Producto::where('estado', 1)
            ->where('es_servicio_lavado', false)
            ->where(function ($q) use ($query) {
                $q->where('nombre', 'like', "%$query%")
                  ->orWhere('codigo', 'like', "%$query%");
            })
            ->take(10)
            ->get();

        return response()->json($productos);
    }
}
