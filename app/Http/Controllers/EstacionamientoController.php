<?php

namespace App\Http\Controllers;

use App\Models\Estacionamiento;
use App\Models\Cliente;
use App\Models\Persona;
use App\Models\Documento;
use App\Exports\EstacionamientoExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class EstacionamientoController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-estacionamiento|crear-estacionamiento|editar-estacionamiento|eliminar-estacionamiento', ['only' => ['index']]);
        $this->middleware('permission:crear-estacionamiento', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-estacionamiento', ['only' => ['edit', 'update']]);
        $this->middleware('permission:eliminar-estacionamiento', ['only' => ['destroy']]);
        $this->middleware('permission:historial-estacionamiento', ['only' => ['historial']]);
        $this->middleware('permission:reporte-diario-estacionamiento', ['only' => ['reporteDiario', 'exportDiario']]);
        $this->middleware('permission:reporte-semanal-estacionamiento', ['only' => ['reporteSemanal', 'exportSemanal']]);
        $this->middleware('permission:reporte-mensual-estacionamiento', ['only' => ['reporteMensual', 'exportMensual']]);
        $this->middleware('permission:reporte-personalizado-estacionamiento', ['only' => ['reportePersonalizado', 'exportPersonalizado']]);
    }

    public function index()
    {
        $estacionamientos = Estacionamiento::with('cliente.persona')
            ->where('estado', 'ocupado')
            ->latest()
            ->get();

        return view('estacionamiento.index', compact('estacionamientos'));
    }

    public function create()
    {
        $clientes = Cliente::whereHas('persona', function($query) {
            $query->where('estado', 1);
        })->get();
        $documentos = Documento::all();
        
        return view('estacionamiento.create', compact('clientes', 'documentos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required_without:nuevo_cliente|exists:clientes,id',
            'placa' => 'required|string|max:10',
            'marca' => 'required|string|max:50',
            'modelo' => 'required|string|max:50',
            'telefono' => 'required|string|max:20',
            'tarifa_hora' => 'required|numeric|min:0',
            // Campos para nuevo cliente
            'razon_social' => 'required_with:nuevo_cliente|string|max:80',
            'documento_id' => 'required_with:nuevo_cliente|exists:documentos,id',
        ]);

        try {
            DB::beginTransaction();

            $cliente_id = $request->cliente_id;

            // Si es un cliente nuevo
            if (!$cliente_id) {
                $persona = Persona::create([
                    'razon_social' => $request->razon_social,
                    'direccion' => '',
                    'tipo_persona' => 'cliente',
                    'telefono' => $request->telefono,
                    'documento_id' => $request->documento_id,
                ]);

                $cliente = Cliente::create(['persona_id' => $persona->id]);
                $cliente_id = $cliente->id;
            }

            Estacionamiento::create([
                'cliente_id' => $cliente_id,
                'placa' => $request->placa,
                'marca' => $request->marca,
                'modelo' => $request->modelo,
                'telefono' => $request->telefono,
                'tarifa_hora' => $request->tarifa_hora,
                'hora_entrada' => now(),
                'estado' => 'ocupado'
            ]);

            DB::commit();
            return redirect()->route('estacionamiento.index')->with('success', 'Vehículo registrado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al registrar el vehículo: ' . $e->getMessage());
        }
    }

    public function show(Estacionamiento $estacionamiento)
    {
        // Using index view with single item
        return view('estacionamiento.index', [
            'estacionamientos' => collect([$estacionamiento])
        ]);
        
        // Alternatively, you can create the show.blade.php file in resources/views/estacionamiento/ directory
    }

    public function registrarSalida(Estacionamiento $estacionamiento)
    {
        $estacionamiento->hora_salida = now();

        // Calcular el tiempo total en minutos
        $tiempoTotal = $estacionamiento->hora_entrada->diffInMinutes($estacionamiento->hora_salida);

        // Calcular el monto total basado en la tarifa por hora
        $montoTotal = ($tiempoTotal < 60) 
            ? $estacionamiento->tarifa_hora // Cobrar tarifa mínima si es menos de una hora
            : ($estacionamiento->tarifa_hora * ceil($tiempoTotal / 60));

        $estacionamiento->monto_total = $montoTotal;
        $estacionamiento->estado = 'finalizado';
        $estacionamiento->save();

        return redirect()->route('estacionamiento.index')
            ->with('success', 'Salida registrada correctamente. Monto total: S/.' . number_format($montoTotal, 2));
    }

    public function historial()
    {
        $estacionamientos = Estacionamiento::with('cliente.persona')
            ->where('estado', 'finalizado')
            ->latest()
            ->get();

        return view('estacionamiento.historial', compact('estacionamientos'));
    }

    public function buscarCliente(Request $request)
    {
        $query = $request->input('q');
        
        $clientes = Cliente::whereHas('persona', function($q) use ($query) {
            $q->where('razon_social', 'LIKE', "%{$query}%")
              ->orWhere('numero_documento', 'LIKE', "%{$query}%")
              ->orWhere('telefono', 'LIKE', "%{$query}%");
        })
        ->with(['persona' => function($q) {
            $q->with('documento');
        }])
        ->get()
        ->map(function($cliente) {
            return [
                'id' => $cliente->id,
                'text' => $cliente->persona->razon_social . ' - ' . 
                         $cliente->persona->documento->tipo_documento . ': ' . 
                         $cliente->persona->numero_documento . ' - Tel: ' . 
                         $cliente->persona->telefono
            ];
        });

        return response()->json(['results' => $clientes]);
    }

    public function destroy(Estacionamiento $estacionamiento)
    {
        $estacionamiento->delete();
        return redirect()->route('estacionamiento.index')->with('success', 'Registro eliminado correctamente');
    }

    public function reporteDiario()
    {
        $estacionamientos = Estacionamiento::whereDate('hora_entrada', now()->toDateString())
            ->with(['cliente.persona'])
            ->get();

        return view('estacionamiento.reporte', compact('estacionamientos'))
            ->with('reporte', 'diario');
    }

    public function reporteSemanal()
    {
        $estacionamientos = Estacionamiento::whereBetween('hora_entrada', [now()->startOfWeek(), now()->endOfWeek()])
            ->with(['cliente.persona'])
            ->get();

        return view('estacionamiento.reporte', compact('estacionamientos'))
            ->with('reporte', 'semanal');
    }

    public function reporteMensual()
    {
        $estacionamientos = Estacionamiento::whereMonth('hora_entrada', now()->month)
            ->with(['cliente.persona'])
            ->get();

        return view('estacionamiento.reporte', compact('estacionamientos'))
            ->with('reporte', 'mensual');
    }

    public function reportePersonalizado(Request $request)
    {
        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;

        $estacionamientos = Estacionamiento::whereBetween('hora_entrada', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->with(['cliente.persona'])
            ->get();

        return view('estacionamiento.reporte', compact('estacionamientos', 'fechaInicio', 'fechaFin'))
            ->with('reporte', 'personalizado');
    }

    public function exportDiario()
    {   
        $estacionamientos = Estacionamiento::whereDate('hora_entrada', now()->toDateString())
            ->with(['cliente.persona'])
            ->get();

        return Excel::download(new EstacionamientoExport($estacionamientos), 'estacionamiento_diario.xlsx');
    }

    public function exportSemanal()
    {
        $estacionamientos = Estacionamiento::whereBetween('hora_entrada', [now()->startOfWeek(), now()->endOfWeek()])
            ->with(['cliente.persona'])
            ->get();

        return Excel::download(new EstacionamientoExport($estacionamientos), 'estacionamiento_semanal.xlsx');
    }

    public function exportMensual()
    {
        $estacionamientos = Estacionamiento::whereMonth('hora_entrada', now()->month)
            ->with(['cliente.persona'])
            ->get();

        return Excel::download(new EstacionamientoExport($estacionamientos), 'estacionamiento_mensual.xlsx');
    }

    public function exportPersonalizado(Request $request)
    {
        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;

        $estacionamientos = Estacionamiento::whereBetween('hora_entrada', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->with(['cliente.persona'])
            ->get();

        return Excel::download(new EstacionamientoExport($estacionamientos), "estacionamiento_{$fechaInicio}_a_{$fechaFin}.xlsx");
    }
}
