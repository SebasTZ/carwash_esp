<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ControlLavado;
use App\Models\User;
use App\Models\Lavador;
use App\Models\TipoVehiculo;
use App\Exports\ControlLavadoExport;
use Maatwebsite\Excel\Facades\Excel;

class ControlLavadoController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-control-lavado|crear-control-lavado|editar-control-lavado|eliminar-control-lavado', ['only' => ['index']]);
        $this->middleware('permission:crear-control-lavado', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-control-lavado', ['only' => ['edit', 'update', 'asignarLavador', 'inicioLavado', 'finLavado', 'inicioInterior', 'finInterior']]);
        $this->middleware('permission:eliminar-control-lavado', ['only' => ['destroy']]);
        $this->middleware('permission:reporte-diario-lavado', ['only' => ['reporteDiario']]);
        $this->middleware('permission:reporte-semanal-lavado', ['only' => ['reporteSemanal']]);
        $this->middleware('permission:reporte-mensual-lavado', ['only' => ['reporteMensual']]);
        $this->middleware('permission:reporte-personalizado-lavado', ['only' => ['reportePersonalizado']]);
        $this->middleware('permission:exportar-reporte-lavado', ['only' => ['exportDiario', 'exportSemanal', 'exportMensual', 'exportPersonalizado']]);
        $this->middleware('permission:ver-tarjeta-regalo|crear-tarjeta-regalo|editar-tarjeta-regalo|eliminar-tarjeta-regalo|reporte-tarjeta-regalo', ['only' => ['usarTarjetaRegalo', 'consultarTarjetaRegalo', 'reporteTarjetasRegalo']]);
        $this->middleware('permission:ver-fidelidad|gestionar-fidelidad', ['only' => ['usarFidelidad', 'consultarFidelidad', 'reporteFidelidad']]);
    }

    public function index(Request $request)
    {
        $query = ControlLavado::with(['venta', 'cliente', 'lavador', 'tipoVehiculo']);

        if ($request->filled('lavador_id')) {
            $query->where('lavador_id', $request->lavador_id);
        }
        if ($request->filled('tipo_vehiculo_id')) {
            $query->where('tipo_vehiculo_id', $request->tipo_vehiculo_id);
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('fecha')) {
            $query->whereDate('hora_llegada', $request->fecha);
        }

        $lavadores = Lavador::where('estado', 'activo')->get();
        $tiposVehiculo = TipoVehiculo::where('estado', 'activo')->get();
        $lavados = $query->orderBy('hora_llegada', 'desc')->get();

        return view('control.lavados', compact('lavados', 'lavadores', 'tiposVehiculo'));
    }

    public function show($id)
    {
        $lavado = ControlLavado::with(['venta', 'cliente'])->findOrFail($id);
        return view('control.show', compact('lavado'));
    }

    public function destroy($lavado)
    {
        $lavado = ControlLavado::findOrFail($lavado);
        $lavado->delete();

        return redirect()->route('control.lavados')->with('success', 'Registro de lavado eliminado correctamente.');
    }

    public function asignarLavador(Request $request, $lavado)
    {
        $request->validate([
            'lavador_id' => 'required|exists:lavadores,id',
            'tipo_vehiculo_id' => 'required|exists:tipos_vehiculo,id',
        ]);

        $lavado = ControlLavado::findOrFail($lavado);
        $lavado->lavador_id = $request->lavador_id;
        $lavado->tipo_vehiculo_id = $request->tipo_vehiculo_id;
        $lavado->save();

        return redirect()->route('control.lavados')->with('success', 'Lavador y tipo de vehículo asignados correctamente.');
    }

    public function inicioLavado($id)
    {
        $lavado = ControlLavado::findOrFail($id);
        if (!$lavado->inicio_lavado) {
            $lavado->inicio_lavado = now();
            $lavado->estado = 'En proceso';
            $lavado->save();
        }
        return redirect()->route('control.lavados');
    }

    public function finLavado($id)
    {
        $lavado = ControlLavado::findOrFail($id);
        if ($lavado->inicio_lavado && !$lavado->fin_lavado) {
            $lavado->fin_lavado = now();
            $lavado->save();
        }
        return redirect()->route('control.lavados');
    }

    public function inicioInterior($id)
    {
        $lavado = ControlLavado::findOrFail($id);
        if ($lavado->fin_lavado && !$lavado->inicio_interior) {
            $lavado->inicio_interior = now();
            $lavado->save();
        }
        return redirect()->route('control.lavados');
    }

    public function finInterior($id)
    {
        $lavado = ControlLavado::findOrFail($id);
        if ($lavado->inicio_interior && !$lavado->fin_interior) {
            $lavado->fin_interior = now();
            $lavado->hora_final = now();
            $lavado->estado = 'Terminado';
            $lavado->save();
        }
        return redirect()->route('control.lavados');
    }

    public function exportDiario()
    {   
        $lavados = ControlLavado::whereDate('hora_llegada', now()->toDateString())
            ->with(['venta', 'cliente.persona'])
            ->get();

        return Excel::download(new ControlLavadoExport($lavados), 'control_lavado_diario.xlsx');
    }

    public function exportSemanal()
    {
        $lavados = ControlLavado::whereBetween('hora_llegada', [now()->startOfWeek(), now()->endOfWeek()])
            ->with(['venta', 'cliente.persona'])
            ->get();

        return Excel::download(new ControlLavadoExport($lavados), 'control_lavado_semanal.xlsx');
    }

    public function exportMensual()
    {
        $lavados = ControlLavado::whereMonth('hora_llegada', now()->month)
            ->with(['venta', 'cliente.persona'])
            ->get();

        return Excel::download(new ControlLavadoExport($lavados), 'control_lavado_mensual.xlsx');
    }

    public function exportPersonalizado(Request $request)
    {
        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;

        $lavados = ControlLavado::whereBetween('hora_llegada', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->with(['venta', 'cliente.persona'])
            ->get();

        return Excel::download(new ControlLavadoExport($lavados), "control_lavado_{$fechaInicio}_a_{$fechaFin}.xlsx");
    }
}
