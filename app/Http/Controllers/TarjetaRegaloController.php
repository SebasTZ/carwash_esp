<?php

namespace App\Http\Controllers;

use App\Models\TarjetaRegalo;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exports\TarjetasRegaloExport;
use Maatwebsite\Excel\Facades\Excel;

class TarjetaRegaloController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver-tarjeta-regalo', ['only' => ['index', 'show', 'reporte', 'reporteView']]);
        $this->middleware('permission:crear-tarjeta-regalo', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-tarjeta-regalo', ['only' => ['edit', 'update']]);
        $this->middleware('permission:eliminar-tarjeta-regalo', ['only' => ['destroy']]);
        $this->middleware('permission:reporte-tarjeta-regalo', ['only' => ['reporte', 'reporteView']]);
        $this->middleware('permission:historial-tarjeta-regalo', ['only' => ['usos']]);
        $this->middleware('permission:exportar-tarjeta-regalo', ['only' => ['exportExcel']]);
    }

    public function index()
    {
        $tarjetas = TarjetaRegalo::with('cliente')->paginate(15);
        // Si es AJAX o API, responde JSON. Si es web, muestra la vista.
        if (request()->ajax()) {
            return response()->json($tarjetas);
        }
        return view('tarjetas_regalo.reporte', compact('tarjetas'));
    }

    public function store(Request $request)
    {
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
        $tarjeta = TarjetaRegalo::with('cliente')->findOrFail($id);
        return response()->json($tarjeta);
    }

    public function edit($id)
    {
        $tarjeta = \App\Models\TarjetaRegalo::findOrFail($id);
        $clientes = \App\Models\Cliente::with('persona')->get();
        return view('tarjetas_regalo.edit', compact('tarjeta', 'clientes'));
    }

    public function update(Request $request, $id)
    {
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
        $tarjeta = \App\Models\TarjetaRegalo::findOrFail($id);
        $tarjeta->delete();
        return redirect()->route('tarjetas_regalo.reporte.view')->with('success', 'Tarjeta de regalo eliminada correctamente.');
    }

    public function usarTarjeta(Request $request)
    {
        $request->validate([
            'codigo' => 'required|exists:tarjetas_regalo,codigo',
            'monto' => 'required|numeric|min:0.01',
        ]);
        $tarjeta = TarjetaRegalo::where('codigo', $request->codigo)->firstOrFail();
        if ($tarjeta->estado !== 'activa' || $tarjeta->saldo_actual < $request->monto) {
            return response()->json(['error' => 'Tarjeta no válida o saldo insuficiente'], 422);
        }
        $tarjeta->saldo_actual -= $request->monto;
        if ($tarjeta->saldo_actual <= 0) {
            $tarjeta->saldo_actual = 0;
            $tarjeta->estado = 'usada';
        }
        $tarjeta->save();
        return response()->json(['tarjeta' => $tarjeta]);
    }

    public function reporte(Request $request)
    {
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
        $tarjetas = \App\Models\TarjetaRegalo::with('cliente.persona')->paginate(15);
        return view('tarjetas_regalo.reporte', compact('tarjetas'));
    }

    public function create()
    {
        $clientes = \App\Models\Cliente::with('persona')->get();
        return view('tarjetas_regalo.create', compact('clientes'));
    }

    public function check($codigo)
    {
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
        $usos = \App\Models\Venta::whereNotNull('tarjeta_regalo_id')->with('cliente.persona')->get();
        return view('tarjetas_regalo.usos', compact('usos'));
    }

    public function exportExcel()
    {
        return Excel::download(new TarjetasRegaloExport, 'gift_cards.xlsx');
    }
}
