<?php

namespace App\Http\Controllers;

use App\Models\Cochera;
use App\Models\Cliente;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CocheraController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-cochera|crear-cochera|editar-cochera|eliminar-cochera', ['only' => ['index']]);
        $this->middleware('permission:crear-cochera', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-cochera', ['only' => ['edit', 'update']]);
        $this->middleware('permission:eliminar-cochera', ['only' => ['destroy']]);
        $this->middleware('permission:reporte-cochera', ['only' => ['reportes']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Cochera::with('cliente.persona');
        
        // Filtrar por estado si se solicita
        if ($request->has('estado') && $request->estado != 'todos') {
            $query->where('estado', $request->estado);
        }
        
        // Por defecto mostrar solo cocheras activas
        if (!$request->has('estado')) {
            $query->where('estado', 'activo');
        }
        
        $cocheras = $query->latest()->get();
        
        return view('cochera.index', compact('cocheras'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clientes = Cliente::whereHas('persona',function($query){
            $query->where('estado',1);
        })->get();
        
        return view('cochera.create', compact('clientes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'placa' => 'required|string|max:20',
            'modelo' => 'required|string|max:100',
            'color' => 'required|string|max:50',
            'tipo_vehiculo' => 'required|string|max:50',
            'fecha_ingreso' => 'required|date',
            'ubicacion' => 'nullable|string|max:50',
            'tarifa_hora' => 'required|numeric|min:0',
            'tarifa_dia' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();
            
            $cochera = Cochera::create([
                'cliente_id' => $request->cliente_id,
                'placa' => strtoupper($request->placa),
                'modelo' => $request->modelo,
                'color' => $request->color,
                'tipo_vehiculo' => $request->tipo_vehiculo,
                'fecha_ingreso' => $request->fecha_ingreso,
                'ubicacion' => $request->ubicacion,
                'tarifa_hora' => $request->tarifa_hora,
                'tarifa_dia' => $request->tarifa_dia,
                'observaciones' => $request->observaciones,
                'estado' => 'activo',
            ]);
            
            DB::commit();
            
            return redirect()->route('cocheras.index')->with('success', 'Vehículo registrado en cochera correctamente');
            
        } catch (Exception $e) {
            DB::rollBack();
            
            return redirect()->route('cocheras.create')->with('error', 'Error al registrar: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Cochera $cochera)
    {
        // Calcular el monto actualizado si el vehículo sigue en cochera
        if ($cochera->estado == 'activo') {
            $montoActualizado = $cochera->calcularMonto();
        } else {
            $montoActualizado = $cochera->monto_total;
        }
        
        return view('cochera.show', compact('cochera', 'montoActualizado'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cochera $cochera)
    {
        $clientes = Cliente::whereHas('persona',function($query){
            $query->where('estado',1);
        })->get();
        
        return view('cochera.edit', compact('cochera', 'clientes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cochera $cochera)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'placa' => 'required|string|max:20',
            'modelo' => 'required|string|max:100',
            'color' => 'required|string|max:50',
            'tipo_vehiculo' => 'required|string|max:50',
            'fecha_ingreso' => 'required|date',
            'fecha_salida' => 'nullable|date|after_or_equal:fecha_ingreso',
            'ubicacion' => 'nullable|string|max:50',
            'tarifa_hora' => 'required|numeric|min:0',
            'tarifa_dia' => 'nullable|numeric|min:0',
            'monto_total' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string|max:500',
            'estado' => 'required|in:activo,finalizado,cancelado',
        ]);

        try {
            DB::beginTransaction();
            
            $data = $request->all();
            $data['placa'] = strtoupper($data['placa']);
            
            // Si hay fecha de salida y está finalizando, calcular el monto total
            if ($data['estado'] == 'finalizado' && !empty($data['fecha_salida'])) {
                $cochera->fecha_salida = $data['fecha_salida'];
                $data['monto_total'] = $cochera->calcularMonto();
            }
            
            $cochera->update($data);
            
            DB::commit();
            
            return redirect()->route('cocheras.index')->with('success', 'Registro de cochera actualizado correctamente');
            
        } catch (Exception $e) {
            DB::rollBack();
            
            return redirect()->route('cocheras.edit', $cochera->id)->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cochera $cochera)
    {
        try {
            DB::beginTransaction();
            
            $cochera->delete();
            
            DB::commit();
            
            return redirect()->route('cocheras.index')->with('success', 'Registro de cochera eliminado correctamente');
            
        } catch (Exception $e) {
            DB::rollBack();
            
            return redirect()->route('cocheras.index')->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }
    
    /**
     * Finalizar el servicio de cochera
     */
    public function finalizar(Request $request, Cochera $cochera)
    {
        try {
            DB::beginTransaction();
            
            $cochera->fecha_salida = now();
            $cochera->monto_total = $cochera->calcularMonto();
            $cochera->estado = 'finalizado';
            $cochera->save();
            
            DB::commit();
            
            return redirect()->route('cocheras.show', $cochera->id)->with('success', 'Servicio de cochera finalizado correctamente');
            
        } catch (Exception $e) {
            DB::rollBack();
            
            return redirect()->route('cocheras.show', $cochera->id)->with('error', 'Error al finalizar: ' . $e->getMessage());
        }
    }
    
    /**
     * Reportes de cocheras
     */
    public function reportes(Request $request)
    {
        $fechaInicio = $request->fecha_inicio ?? now()->startOfMonth()->format('Y-m-d');
        $fechaFin = $request->fecha_fin ?? now()->format('Y-m-d');
        
        $cocheras = Cochera::with('cliente.persona')
            ->where(function ($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('fecha_ingreso', [$fechaInicio.' 00:00:00', $fechaFin.' 23:59:59'])
                      ->orWhereBetween('fecha_salida', [$fechaInicio.' 00:00:00', $fechaFin.' 23:59:59']);
            })
            ->orderBy('fecha_ingreso', 'desc')
            ->get();
            
        return view('cochera.reportes', compact('cocheras', 'fechaInicio', 'fechaFin'));
    }
}
