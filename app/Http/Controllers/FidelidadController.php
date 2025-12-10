<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Services\FidelizacionService;
use Illuminate\Http\Request;
use App\Exports\FidelidadExport;

class FidelidadController extends Controller
{
    public function __construct(
        private FidelizacionService $fidelizacionService
    ) {
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

    /**
     * Incrementa un lavado al cliente usando el servicio de fidelización
     * 
     * @param int $clienteId ID del cliente
     * @return \Illuminate\Http\JsonResponse
     */
    public function incrementarLavado($clienteId)
    {
        $cliente = Cliente::findOrFail($clienteId);
        $this->fidelizacionService->acumularLavado($cliente);
        
        // Recargar cliente para obtener datos actualizados
        $cliente->refresh();
        
        return response()->json(['lavados_acumulados' => $cliente->lavados_acumulados]);
    }

    /**
     * Aplica un lavado gratis al cliente si tiene suficientes acumulados
     * 
     * @param int $clienteId ID del cliente
     * @return \Illuminate\Http\JsonResponse
     */
    public function aplicarLavadoGratis($clienteId)
    {
        $cliente = Cliente::findOrFail($clienteId);
        
        if ($this->fidelizacionService->puedeUsarLavadoGratis($cliente)) {
            $this->fidelizacionService->canjearLavadoGratis($cliente);
            return response()->json(['lavado_gratis' => true, 'lavados_acumulados' => 0]);
        }
        
        return response()->json([
            'lavado_gratis' => false,
            'lavados_acumulados' => $cliente->lavados_acumulados,
            'mensaje' => "Le faltan " . (10 - $cliente->lavados_acumulados) . " lavados para obtener uno gratis"
        ]);
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
