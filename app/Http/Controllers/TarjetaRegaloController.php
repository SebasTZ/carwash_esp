<?php

namespace App\Http\Controllers;

use App\Models\TarjetaRegalo;
use App\Models\Cliente;
use App\Services\TarjetaRegaloService;
use App\Exceptions\TarjetaRegaloException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exports\TarjetasRegaloExport;
use Maatwebsite\Excel\Facades\Excel;

class TarjetaRegaloController extends Controller
{
    public function __construct(
        private TarjetaRegaloService $tarjetaRegaloService
    ) {}

    public function index()
    {
        $this->authorizePermission('ver-tarjeta-regalo');

        $tarjetas = TarjetaRegalo::with('cliente')->paginate(15);
        // Si es AJAX o API, responde JSON. Si es web, muestra la vista.
        if (request()->ajax()) {
            return response()->json($tarjetas);
        }
        return view('tarjetas_regalo.reporte', compact('tarjetas'));
    }

    public function store(Request $request)
    {
        $this->authorizePermission('crear-tarjeta-regalo');

        $validator = Validator::make($request->all(), [
            'codigo' => 'required|unique:tarjetas_regalo,codigo',
            'valor_inicial' => 'required|numeric|min:1',
            'fecha_venta' => 'required|date',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha_venta',
            'cliente_id' => 'nullable|exists:clientes,id',
        ]);

        if ($validator->fails()) {
            // Si es AJAX, responde JSON, si no, redirige con errores
            if ($request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $tarjeta = TarjetaRegalo::create([
            'codigo' => $request->codigo,
            'valor_inicial' => $request->valor_inicial,
            'saldo_actual' => $request->valor_inicial,
            'estado' => 'activa',
            'fecha_venta' => $request->fecha_venta,
            'fecha_vencimiento' => $request->fecha_vencimiento,
            'cliente_id' => $request->cliente_id,
        ]);

        // Si es AJAX, responde JSON, si no, redirige con mensaje de éxito
        if ($request->ajax()) {
            return response()->json($tarjeta, 201);
        }
        return redirect()->route('tarjetas_regalo.reporte.view')->with('success', '¡Tarjeta de regalo creada correctamente!');
    }

    public function show($id)
    {
        $this->authorizePermission('ver-tarjeta-regalo');

        $tarjeta = TarjetaRegalo::with('cliente')->findOrFail($id);
        return response()->json($tarjeta);
    }

    public function edit($id)
    {
        $this->authorizePermission('editar-tarjeta-regalo');

        $tarjeta = \App\Models\TarjetaRegalo::findOrFail($id);
        $clientes = \App\Models\Cliente::with('persona')->get();
        return view('tarjetas_regalo.edit', compact('tarjeta', 'clientes'));
    }

    public function update(Request $request, $id)
    {
        $this->authorizePermission('editar-tarjeta-regalo');

        $tarjeta = \App\Models\TarjetaRegalo::findOrFail($id);
        $request->validate([
            'valor_inicial' => 'required|numeric|min:1',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha_venta',
            'cliente_id' => 'nullable|exists:clientes,id',
            'estado' => 'required|in:activa,usada,vencida',
        ]);
        $tarjeta->update($request->only(['valor_inicial', 'fecha_vencimiento', 'cliente_id', 'estado']));
        return redirect()->route('tarjetas_regalo.reporte.view')->with('success', 'Tarjeta de regalo actualizada correctamente.');
    }

    public function destroy($id)
    {
        $this->authorizePermission('eliminar-tarjeta-regalo');

        $tarjeta = \App\Models\TarjetaRegalo::findOrFail($id);
        $tarjeta->delete();
        return redirect()->route('tarjetas_regalo.reporte.view')->with('success', 'Tarjeta de regalo eliminada correctamente.');
    }

    /**
     * Usa una tarjeta de regalo para descontar del total
     * Utiliza el servicio para validación y descuento
     */
    public function usarTarjeta(Request $request)
    {
        $this->authorizeAnyPermission(['crear-venta', 'ver-tarjeta-regalo']);

        $request->validate([
            'codigo' => 'required|exists:tarjetas_regalo,codigo',
            'monto' => 'required|numeric|min:0.01',
        ]);

        try {
            $tarjeta = $this->tarjetaRegaloService->validarYDescontar(
                codigo: $request->codigo,
                monto: $request->monto
            );

            return response()->json([
                'success' => true,
                'tarjeta' => $tarjeta,
                'saldo_restante' => $tarjeta->saldo_actual,
                'mensaje' => 'Tarjeta aplicada correctamente'
            ]);

        } catch (TarjetaRegaloException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al procesar la tarjeta de regalo'
            ], 500);
        }
    }

    public function reporte(Request $request)
    {
        $this->authorizePermission('reporte-tarjeta-regalo');

        $query = TarjetaRegalo::with('cliente');
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('vendidas')) {
            $query->whereNotNull('fecha_venta');
        }
        if ($request->filled('saldo')) {
            $query->where('saldo_actual', '>', 0);
        }
        $tarjetas = $query->get();
        return response()->json($tarjetas);
    }

    public function reporteView(Request $request)
    {
        $this->authorizePermission('reporte-tarjeta-regalo');

        $tarjetas = \App\Models\TarjetaRegalo::with('cliente.persona')->paginate(15);
        return view('tarjetas_regalo.reporte', compact('tarjetas'));
    }

    public function create()
    {
        $this->authorizePermission('crear-tarjeta-regalo');

        $clientes = \App\Models\Cliente::with('persona')->get();
        return view('tarjetas_regalo.create', compact('clientes'));
    }

    public function check($codigo)
    {
        $this->authorizeAnyPermission(['ver-tarjeta-regalo', 'crear-venta']);

        $tarjeta = \App\Models\TarjetaRegalo::where('codigo', $codigo)->first();
        if (!$tarjeta) {
            return response()->json(['error' => 'not_found'], 404);
        }
        return response()->json([
            'estado' => $tarjeta->estado,
            'saldo_actual' => $tarjeta->saldo_actual
        ]);
    }

    public function usos()
    {
        $this->authorizePermission('historial-tarjeta-regalo');

        $usos = \App\Models\Venta::whereNotNull('tarjeta_regalo_id')->with('cliente.persona')->get();
        return view('tarjetas_regalo.usos', compact('usos'));
    }

    public function exportExcel()
    {
        $this->authorizePermission('exportar-tarjeta-regalo');

        return Excel::download(new TarjetasRegaloExport, 'gift_cards.xlsx');
    }
}
