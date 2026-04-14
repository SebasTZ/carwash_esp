<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompraRequest;
use App\Models\Compra;
use App\Models\Comprobante;
use App\Models\Producto;
use App\Models\Proveedore;
use App\Services\StockService;
use Exception;
use App\Exports\ComprasExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class compraController extends Controller
{
    public function __construct(private StockService $stockService) {}

    public function index()
    {
        $compras = Compra::with('comprobante','proveedore.persona')
        ->where('estado',1)
        ->latest()
        ->paginate(15); 
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
            $arrayProducto_id = $request->input('arrayidproducto', []);
            $arrayCantidad = $request->input('arraycantidad', []);
            $arrayPrecioCompra = $request->input('arraypreciocompra', []);
            $arrayPrecioVenta = $request->input('arrayprecioventa', []);

            // Cargar todos los productos de una vez (evita N+1 queries)
            $productosMap = Producto::whereIn('id', $arrayProducto_id)->get()->keyBy('id');

            foreach ($arrayProducto_id as $index => $productoId) {
                $compra->productos()->syncWithoutDetaching([
                    $productoId => [
                        'cantidad' => $arrayCantidad[$index],
                        'precio_compra' => $arrayPrecioCompra[$index],
                        'precio_venta' => $arrayPrecioVenta[$index]
                    ]
                ]);

                // Actualizar el stock usando StockService (con lock pesimista y auditoría)
                $producto = $productosMap->get($productoId)
                    ?? throw new Exception("Producto con ID {$productoId} no encontrado");
                $this->stockService->incrementarStock(
                    $producto,
                    intval($arrayCantidad[$index]),
                    "Compra #{$compra->id}"
                );
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
        $request->validate([
            'fecha_inicio' => 'required|date|before_or_equal:fecha_fin',
            'fecha_fin'    => 'required|date',
        ]);

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
        $request->validate([
            'fecha_inicio' => 'required|date|before_or_equal:fecha_fin',
            'fecha_fin'    => 'required|date',
        ]);

        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;

        $compras = Compra::whereBetween('fecha_hora', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->with(['comprobante', 'proveedore.persona'])
            ->get();

        return Excel::download(new ComprasExport($compras), "compras_{$fechaInicio}_a_{$fechaFin}.xlsx");
    }

    public function destroy(Compra $compra)
    {
        $compra->update(['estado' => 0]);

        return redirect()->route('compras.index')->with('success', 'Compra eliminada');
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
