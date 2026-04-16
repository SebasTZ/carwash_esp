<?php

namespace App\Http\Controllers;

use App\Models\Estacionamiento;
use App\Models\Cliente;
use App\Models\Persona;
use App\Models\Documento;
use App\Services\EstacionamientoService;
use App\Exports\EstacionamientoExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class EstacionamientoController extends Controller
{
    protected EstacionamientoService $estacionamientoService;

    function __construct(EstacionamientoService $estacionamientoService)
    {
        $this->estacionamientoService = $estacionamientoService;
    }

    public function index()
    {
        $this->authorizeAnyPermission(['ver-estacionamiento', 'crear-estacionamiento', 'editar-estacionamiento', 'eliminar-estacionamiento']);

        $estacionamientos = Estacionamiento::with('cliente.persona')
            ->where('estado', 'ocupado')
            ->latest()
            ->paginate(15);

        return view('estacionamiento.index', compact('estacionamientos'));
    }

    public function create()
    {
        $this->authorizePermission('crear-estacionamiento');

        $clientes = Cliente::whereHas('persona', function($query) {
            $query->where('estado', 1);
        })->get();
        $documentos = Documento::all();
        
        return view('estacionamiento.create', compact('clientes', 'documentos'));
    }

    public function store(Request $request)
    {
        $this->authorizePermission('crear-estacionamiento');

        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'placa' => 'required|string|max:10',
            'marca' => 'required|string|max:50',
            'modelo' => 'required|string|max:50',
            'telefono' => 'required|string|max:20',
            'tarifa_hora' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // ✅ CORRECCIÓN BUG #3: Validar capacidad máxima del estacionamiento
            $capacidadMaxima = config('estacionamiento.capacidad_maxima', 20);
            $espaciosOcupados = Estacionamiento::where('estado', 'ocupado')->count();
            
            if ($espaciosOcupados >= $capacidadMaxima) {
                DB::rollBack();
                return redirect()->back()->with('error', 
                    "Estacionamiento lleno. Capacidad máxima: {$capacidadMaxima} vehículos. " .
                    "Espacios ocupados: {$espaciosOcupados}"
                );
            }

            // ✅ CORRECCIÓN BUG #4: Validar que la placa no esté duplicada (case-insensitive)
            $placaExistente = Estacionamiento::whereRaw('UPPER(placa) = ?', [strtoupper($request->placa)])
                ->where('estado', 'ocupado')
                ->exists();
                
            if ($placaExistente) {
                DB::rollBack();
                return redirect()->back()->with('error', 
                    "El vehículo con placa {$request->placa} ya está estacionado actualmente. " .
                    "Por favor, verifique la placa o registre la salida del vehículo anterior."
                );
            }

            // Normalizar placa a mayúsculas
            $placa = strtoupper($request->placa);

            Estacionamiento::create([
                'cliente_id' => $request->cliente_id,
                'placa' => $placa,
                'marca' => $request->marca,
                'modelo' => $request->modelo,
                'telefono' => $request->telefono,
                'tarifa_hora' => $request->tarifa_hora,
                'hora_entrada' => now(),
                'estado' => 'ocupado',
                'pagado_adelantado' => $request->has('pagado_adelantado'),
                'monto_pagado_adelantado' => $request->pagado_adelantado ? $request->monto_pagado_adelantado : null
            ]);

            DB::commit();
            
            $espaciosDisponibles = $capacidadMaxima - ($espaciosOcupados + 1);
            return redirect()->route('estacionamiento.index')->with('success', 
                "Vehículo registrado correctamente. Espacios disponibles: {$espaciosDisponibles}"
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al registrar el vehículo: ' . $e->getMessage());
        }
    }

    public function show(Estacionamiento $estacionamiento)
    {
        $this->authorizePermission('ver-estacionamiento');

        // Using index view with single item
        return view('estacionamiento.index', [
            'estacionamientos' => collect([$estacionamiento])
        ]);
        
        // Alternatively, you can create the show.blade.php file in resources/views/estacionamiento/ directory
    }

    public function registrarSalida(Estacionamiento $estacionamiento)
    {
        $this->authorizePermission('editar-estacionamiento');

        try {
            $estacionamiento->hora_salida = now();

            // Usar el servicio para calcular el monto (lógica centralizada)
            $montoTotal = $this->estacionamientoService->registrarSalida($estacionamiento);

            $estacionamiento->estado = 'finalizado';
            $estacionamiento->save();

            return redirect()->route('estacionamiento.index')
                ->with('success', 'Salida registrada correctamente. Monto total: S/.' . number_format($montoTotal, 2));

        } catch (\Exception $e) {
            Log::error('Error al registrar salida de estacionamiento', [
                'estacionamiento_id' => $estacionamiento->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('estacionamiento.index')
                ->with('error', 'Error al registrar la salida: ' . $e->getMessage());
        }
    }

    public function historial()
    {
        $this->authorizePermission('historial-estacionamiento');

        $estacionamientos = Estacionamiento::with('cliente.persona')
            ->where('estado', 'finalizado')
            ->latest()
            ->get();

        return view('estacionamiento.historial', compact('estacionamientos'));
    }

    public function buscarCliente(Request $request)
    {
        $this->authorizeAnyPermission(['ver-estacionamiento', 'crear-estacionamiento']);

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
        $this->authorizePermission('eliminar-estacionamiento');

        $estacionamiento->delete();
        return redirect()->route('estacionamiento.index')->with('success', 'Registro eliminado correctamente');
    }

    public function reporteDiario()
    {
        $this->authorizePermission('reporte-diario-estacionamiento');

        $estacionamientos = Estacionamiento::whereDate('hora_entrada', now()->toDateString())
            ->with(['cliente.persona'])
            ->get();

        return view('estacionamiento.reporte', compact('estacionamientos'))
            ->with('reporte', 'diario');
    }

    public function reporteSemanal()
    {
        $this->authorizePermission('reporte-semanal-estacionamiento');

        $estacionamientos = Estacionamiento::whereBetween('hora_entrada', [now()->startOfWeek(), now()->endOfWeek()])
            ->with(['cliente.persona'])
            ->get();

        return view('estacionamiento.reporte', compact('estacionamientos'))
            ->with('reporte', 'semanal');
    }

    public function reporteMensual()
    {
        $this->authorizePermission('reporte-mensual-estacionamiento');

        $estacionamientos = Estacionamiento::whereMonth('hora_entrada', now()->month)
            ->with(['cliente.persona'])
            ->get();

        return view('estacionamiento.reporte', compact('estacionamientos'))
            ->with('reporte', 'mensual');
    }

    public function reportePersonalizado(Request $request)
    {
        $this->authorizePermission('reporte-personalizado-estacionamiento');

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
        $this->authorizePermission('reporte-diario-estacionamiento');

        $estacionamientos = Estacionamiento::whereDate('hora_entrada', now()->toDateString())
            ->with(['cliente.persona'])
            ->get();

        return Excel::download(new EstacionamientoExport($estacionamientos), 'estacionamiento_diario.xlsx');
    }

    public function exportSemanal()
    {
        $this->authorizePermission('reporte-semanal-estacionamiento');

        $estacionamientos = Estacionamiento::whereBetween('hora_entrada', [now()->startOfWeek(), now()->endOfWeek()])
            ->with(['cliente.persona'])
            ->get();

        return Excel::download(new EstacionamientoExport($estacionamientos), 'estacionamiento_semanal.xlsx');
    }

    public function exportMensual()
    {
        $this->authorizePermission('reporte-mensual-estacionamiento');

        $estacionamientos = Estacionamiento::whereMonth('hora_entrada', now()->month)
            ->with(['cliente.persona'])
            ->get();

        return Excel::download(new EstacionamientoExport($estacionamientos), 'estacionamiento_mensual.xlsx');
    }

    public function exportPersonalizado(Request $request)
    {
        $this->authorizePermission('reporte-personalizado-estacionamiento');

        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;

        $estacionamientos = Estacionamiento::whereBetween('hora_entrada', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->with(['cliente.persona'])
            ->get();

        return Excel::download(new EstacionamientoExport($estacionamientos), "estacionamiento_{$fechaInicio}_a_{$fechaFin}.xlsx");
    }
}
