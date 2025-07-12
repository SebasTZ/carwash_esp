<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Documento;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\CitasExport;
use Maatwebsite\Excel\Facades\Excel;

class CitaController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-cita|crear-cita|editar-cita|eliminar-cita|calendario-cita|confirmar-cita', ['only' => ['index']]);
        $this->middleware('permission:crear-cita', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-cita', ['only' => ['edit', 'update']]);
        $this->middleware('permission:eliminar-cita', ['only' => ['destroy']]);
        $this->middleware('permission:calendario-cita', ['only' => ['dashboard']]);
        $this->middleware('permission:confirmar-cita', ['only' => ['iniciarCita', 'completarCita', 'cancelarCita']]);
        $this->middleware('permission:reporte-diario-cita', ['only' => ['reporteDiario']]);
        $this->middleware('permission:reporte-semanal-cita', ['only' => ['reporteSemanal']]);
        $this->middleware('permission:reporte-mensual-cita', ['only' => ['reporteMensual']]);
        $this->middleware('permission:reporte-personalizado-cita', ['only' => ['reportePersonalizado']]);
        $this->middleware('permission:exportar-reporte-cita', ['only' => ['exportDiario', 'exportSemanal', 'exportMensual', 'exportPersonalizado']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Cita::with('cliente.persona');

        // Filter by status if provided
        if ($request->has('estado') && $request->estado != '') {
            $query->where('estado', $request->estado);
        }

        // Filter by date
        if ($request->has('fecha') && $request->fecha != '') {
            $query->whereDate('fecha', $request->fecha);
        } else {
            // Default to today's date if no date is provided
            $query->whereDate('fecha', now()->toDateString());
        }

        $citas = $query->orderBy('fecha')->orderBy('posicion_cola')->get();

        return view('citas.index', compact('citas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get clients for the dropdown
        $clientes = Cliente::whereHas('persona', function ($query) {
            $query->where('estado', 1);
        })->get();
        
        // Get document types for new client registration
        $documentos = Documento::all();

        return view('citas.create', compact('clientes', 'documentos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate appointment data
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'fecha' => 'required|date|date_format:Y-m-d|after_or_equal:today',
            'hora' => 'required',
            'notas' => 'nullable|string|max:500',
        ]);
        
        // Get next position in the queue for the selected date
        $posicionCola = Cita::getNextQueuePosition($request->fecha);

        // Create the appointment
        Cita::create([
            'cliente_id' => $request->cliente_id,
            'fecha' => $request->fecha,
            'hora' => $request->hora,
            'posicion_cola' => $posicionCola,
            'estado' => 'pendiente',
            'notas' => $request->notas,
        ]);

        return redirect()->route('citas.index')
            ->with('success', 'Cita registrada exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Cita $cita)
    {
        return view('citas.show', compact('cita'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cita $cita)
    {
        // Ya no necesitamos cargar todos los clientes, ya que el cliente no será modificable
        return view('citas.edit', compact('cita'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cita $cita)
    {
        $request->validate([
            'fecha' => 'required|date|date_format:Y-m-d',
            'hora' => 'required',
            'notas' => 'nullable|string|max:500',
        ]);

        // Verificar si cambió la fecha para actualizar la posición en cola si es necesario
        $actualizarPosicion = $request->fecha != $cita->fecha->format('Y-m-d');
        $posicionCola = $actualizarPosicion ? Cita::getNextQueuePosition($request->fecha) : $cita->posicion_cola;

        $cita->update([
            'cliente_id' => $cita->cliente_id, // Incluimos explícitamente el cliente_id original
            'fecha' => $request->fecha,
            'hora' => $request->hora,
            'posicion_cola' => $posicionCola,
            'notas' => $request->notas,
        ]);

        return redirect()->route('citas.index')
            ->with('success', 'Cita actualizada exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cita $cita)
    {
        $cita->delete();

        return redirect()->route('citas.index')
            ->with('success', 'Cita eliminada exitosamente');
    }

    /**
     * Change the appointment status to 'en_proceso'
     */
    public function iniciarCita(Cita $cita)
    {
        $cita->update(['estado' => 'en_proceso']);
        return redirect()->route('citas.dashboard')
            ->with('success', 'Cita iniciada exitosamente');
    }

    /**
     * Change the appointment status to 'completada'
     */
    public function completarCita(Cita $cita)
    {
        $cita->update(['estado' => 'completada']);
        return redirect()->route('citas.dashboard')
            ->with('success', 'Cita completada exitosamente');
    }

    /**
     * Change the appointment status to 'cancelada'
     */
    public function cancelarCita(Cita $cita)
    {
        $cita->update(['estado' => 'cancelada']);
        return redirect()->route('citas.dashboard')
            ->with('success', 'Cita cancelada exitosamente');
    }

    /**
     * Save a new client from the appointment form without creating an appointment.
     */
    public function saveClient(Request $request)
    {
        // Validar datos del nuevo cliente
        $validatedData = $request->validate([
            'razon_social' => 'required|string|max:80',
            'direccion' => 'nullable|string|max:80',
            'telefono' => 'nullable|string|max:15',
            'documento_id' => 'required|exists:documentos,id',
            'numero_documento' => [
                'required',
                'max:20',
                'unique:personas,numero_documento',
                function ($attribute, $value, $fail) use ($request) {
                    $tipoDocumento = Documento::find($request->documento_id)->tipo_documento;
                    if ($tipoDocumento === 'DNI' && !preg_match('/^\d{8}$/', $value)) {
                        $fail('El DNI debe tener 8 dígitos numéricos.');
                    } elseif ($tipoDocumento === 'RUC' && !preg_match('/^\d{11}$/', $value)) {
                        $fail('El RUC debe tener 11 dígitos numéricos.');
                    } elseif ($tipoDocumento === 'Pasaporte' && !preg_match('/^[a-zA-Z0-9]{12}$/', $value)) {
                        $fail('El Pasaporte debe tener 12 caracteres alfanuméricos.');
                    } elseif ($tipoDocumento === 'Carné de Extranjería' && !preg_match('/^\d{12}$/', $value)) {
                        $fail('El Carné de Extranjería debe tener 12 dígitos numéricos.');
                    }
                }
            ]
        ]);
        
        try {
            DB::beginTransaction();
            
            // Crear nueva persona
            $persona = Persona::create([
                'razon_social' => $request->razon_social,
                'direccion' => $request->direccion ?? '',
                'tipo_persona' => 'cliente',
                'telefono' => $request->telefono ?? '',
                'documento_id' => $request->documento_id,
                'numero_documento' => $request->numero_documento,
            ]);
            
            // Crear nuevo cliente
            $cliente = Cliente::create([
                'persona_id' => $persona->id
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Cliente guardado exitosamente',
                'cliente' => [
                    'id' => $cliente->id,
                    'persona' => [
                        'razon_social' => $persona->razon_social,
                        'numero_documento' => $persona->numero_documento
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the real-time dashboard of appointments for the current day
     */
    public function dashboard()
    {
        $citas = Cita::with('cliente.persona')
            ->whereDate('fecha', now()->toDateString())
            ->orderBy('posicion_cola')
            ->get();

        return view('citas.dashboard', compact('citas'));
    }

    public function exportDiario()
    {   
        $citas = Cita::whereDate('fecha', now()->toDateString())
            ->with(['cliente.persona'])
            ->get();

        return Excel::download(new CitasExport($citas), 'citas_diarias.xlsx');
    }

    public function exportSemanal()
    {
        $citas = Cita::whereBetween('fecha', [now()->startOfWeek(), now()->endOfWeek()])
            ->with(['cliente.persona'])
            ->get();

        return Excel::download(new CitasExport($citas), 'citas_semanales.xlsx');
    }

    public function exportMensual()
    {
        $citas = Cita::whereMonth('fecha', now()->month)
            ->with(['cliente.persona'])
            ->get();

        return Excel::download(new CitasExport($citas), 'citas_mensuales.xlsx');
    }

    public function exportPersonalizado(Request $request)
    {
        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;

        $citas = Cita::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->with(['cliente.persona'])
            ->get();

        return Excel::download(new CitasExport($citas), "citas_{$fechaInicio}_a_{$fechaFin}.xlsx");
    }
}
