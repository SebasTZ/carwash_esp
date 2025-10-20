<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use App\Exports\FidelidadExport;

class FidelidadController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver-fidelidad', ['only' => ['reporteFidelidad', 'reporteView']]);
        $this->middleware('permission:gestionar-fidelidad', ['only' => ['mostrarLavados', 'incrementarLavado', 'aplicarLavadoGratis']]);
        $this->middleware('permission:reporte-fidelidad', ['only' => ['reporteFidelidad', 'reporteView']]);
        $this->middleware('permission:exportar-fidelidad', ['only' => ['exportExcel']]);
    }

    public function mostrarLavados($clienteId)
    {
        $cliente = Cliente::findOrFail($clienteId);
        return response()->json(['lavados_acumulados' => $cliente->lavados_acumulados]);
    }

    public function incrementarLavado($clienteId)
    {
        $cliente = Cliente::findOrFail($clienteId);
        $cliente->lavados_acumulados += 1;
        $cliente->save();
        return response()->json(['lavados_acumulados' => $cliente->lavados_acumulados]);
    }

    public function aplicarLavadoGratis($clienteId)
    {
        $cliente = Cliente::findOrFail($clienteId);
        if ($cliente->lavados_acumulados >= 10) {
            $cliente->lavados_acumulados = 0;
            $cliente->save();
            // Aquí se debe registrar el lavado gratuito en el sistema de lavados
            return response()->json(['lavado_gratis' => true]);
        }
        return response()->json(['lavado_gratis' => false]);
    }

    public function reporteFidelidad(Request $request)
    {
        $clientes = \App\Models\Cliente::with('persona')
            ->orderByDesc('lavados_acumulados')
            ->get();
        $lavadosGratuitos = \App\Models\Venta::where('lavado_gratis', true)->with('cliente.persona')->get();
        return response()->json([
            'clientes_frecuentes' => $clientes,
            'lavados_gratis' => $lavadosGratuitos
        ]);
    }

    public function reporteView(Request $request)
    {
        $clientes_frecuentes = \App\Models\Cliente::with('persona')->orderByDesc('lavados_acumulados')->paginate(15);
        $lavados_gratis = \App\Models\Venta::where('lavado_gratis', true)->with('cliente.persona')->paginate(15);
        return view('fidelidad.reporte', compact('clientes_frecuentes', 'lavados_gratis'));
    }

    public function exportExcel()
    {
        $clientes = \App\Models\Cliente::with('persona')->orderByDesc('lavados_acumulados')->get();
        $lavadosGratis = \App\Models\Venta::where('lavado_gratis', true)->with('cliente.persona')->get();
        return \Maatwebsite\Excel\Facades\Excel::download(new FidelidadExport($clientes, $lavadosGratis), 'reporte_fidelidad.xlsx');
    }
}
