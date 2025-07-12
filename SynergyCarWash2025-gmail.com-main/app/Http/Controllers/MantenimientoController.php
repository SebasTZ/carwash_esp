<?php

namespace App\Http\Controllers;

use App\Models\Mantenimiento;
use App\Models\Cliente;
use App\Models\Venta;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MantenimientoController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-mantenimiento|crear-mantenimiento|editar-mantenimiento|eliminar-mantenimiento', ['only' => ['index']]);
        $this->middleware('permission:crear-mantenimiento', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-mantenimiento', ['only' => ['edit', 'update']]);
        $this->middleware('permission:eliminar-mantenimiento', ['only' => ['destroy']]);
        $this->middleware('permission:reporte-mantenimiento', ['only' => ['reportes']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Mantenimiento::with(['cliente.persona']);
        
        // Filtrar por estado
        if ($request->has('estado') && $request->estado != 'todos') {
            $query->where('estado', $request->estado);
        }
        
        // Por defecto no mostrar los entregados
        if (!$request->has('estado')) {
            $query->where('estado', '!=', 'entregado');
        }
        
        $mantenimientos = $query->latest()->get();
        
        return view('mantenimiento.index', compact('mantenimientos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clientes = Cliente::whereHas('persona',function($query){
            $query->where('estado', 1);
        })->get();
        
        return view('mantenimiento.create', compact('clientes'));
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
            'tipo_vehiculo' => 'required|string|max:50',
            'fecha_ingreso' => 'required|date',
            'fecha_entrega_estimada' => 'nullable|date|after_or_equal:fecha_ingreso',
            'tipo_servicio' => 'required|string|max:100',
            'descripcion_trabajo' => 'required|string',
            'observaciones' => 'nullable|string',
            'costo_estimado' => 'nullable|numeric|min:0',
            'mecanico_responsable' => 'nullable|string|max:100',
        ]);

        try {
            DB::beginTransaction();
            
            $mantenimiento = Mantenimiento::create([
                'cliente_id' => $request->cliente_id,
                'placa' => strtoupper($request->placa),
                'modelo' => $request->modelo,
                'tipo_vehiculo' => $request->tipo_vehiculo,
                'fecha_ingreso' => $request->fecha_ingreso,
                'fecha_entrega_estimada' => $request->fecha_entrega_estimada,
                'tipo_servicio' => $request->tipo_servicio,
                'descripcion_trabajo' => $request->descripcion_trabajo,
                'observaciones' => $request->observaciones,
                'costo_estimado' => $request->costo_estimado,
                'mecanico_responsable' => $request->mecanico_responsable,
                'estado' => 'recibido',
                'pagado' => false,
            ]);
            
            DB::commit();
            
            return redirect()->route('mantenimientos.index')->with('success', 'Servicio de mantenimiento registrado correctamente');
            
        } catch (Exception $e) {
            DB::rollBack();
            
            return redirect()->route('mantenimientos.create')->with('error', 'Error al registrar: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Mantenimiento $mantenimiento)
    {
        return view('mantenimiento.show', compact('mantenimiento'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Mantenimiento $mantenimiento)
    {
        $clientes = Cliente::whereHas('persona',function($query){
            $query->where('estado', 1);
        })->get();
        
        return view('mantenimiento.edit', compact('mantenimiento', 'clientes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Mantenimiento $mantenimiento)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'placa' => 'required|string|max:20',
            'modelo' => 'required|string|max:100',
            'tipo_vehiculo' => 'required|string|max:50',
            'fecha_ingreso' => 'required|date',
            'fecha_entrega_estimada' => 'nullable|date|after_or_equal:fecha_ingreso',
            'fecha_entrega_real' => 'nullable|date|after_or_equal:fecha_ingreso',
            'tipo_servicio' => 'required|string|max:100',
            'descripcion_trabajo' => 'required|string',
            'observaciones' => 'nullable|string',
            'costo_estimado' => 'nullable|numeric|min:0',
            'costo_final' => 'nullable|numeric|min:0',
            'mecanico_responsable' => 'nullable|string|max:100',
            'estado' => 'required|in:recibido,en_proceso,terminado,entregado',
            'pagado' => 'boolean',
        ]);

        try {
            DB::beginTransaction();
            
            $data = $request->all();
            $data['placa'] = strtoupper($data['placa']);
            $data['pagado'] = $request->has('pagado');
            
            // Si el estado es entregado y no hay fecha de entrega, establecerla
            if ($data['estado'] == 'entregado' && empty($data['fecha_entrega_real'])) {
                $data['fecha_entrega_real'] = now();
            }
            
            $mantenimiento->update($data);
            
            DB::commit();
            
            return redirect()->route('mantenimientos.index')->with('success', 'Servicio de mantenimiento actualizado correctamente');
            
        } catch (Exception $e) {
            DB::rollBack();
            
            return redirect()->route('mantenimientos.edit', $mantenimiento->id)->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Mantenimiento $mantenimiento)
    {
        try {
            DB::beginTransaction();
            
            $mantenimiento->delete();
            
            DB::commit();
            
            return redirect()->route('mantenimientos.index')->with('success', 'Servicio de mantenimiento eliminado correctamente');
            
        } catch (Exception $e) {
            DB::rollBack();
            
            return redirect()->route('mantenimientos.index')->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }
    
    /**
     * Actualizar estado del mantenimiento
     */
    public function cambiarEstado(Request $request, Mantenimiento $mantenimiento)
    {
        $request->validate([
            'estado' => 'required|in:recibido,en_proceso,terminado,entregado',
        ]);
        
        try {
            DB::beginTransaction();
            
            $mantenimiento->estado = $request->estado;
            
            // Si cambia a entregado, registrar fecha de entrega
            if ($request->estado == 'entregado' && !$mantenimiento->fecha_entrega_real) {
                $mantenimiento->fecha_entrega_real = now();
            }
            
            $mantenimiento->save();
            
            DB::commit();
            
            return redirect()->route('mantenimientos.show', $mantenimiento->id)
                ->with('success', 'Estado actualizado a: ' . ucfirst(str_replace('_', ' ', $request->estado)));
            
        } catch (Exception $e) {
            DB::rollBack();
            
            return redirect()->route('mantenimientos.show', $mantenimiento->id)
                ->with('error', 'Error al actualizar estado: ' . $e->getMessage());
        }
    }
    
    /**
     * Vincular mantenimiento con una venta
     */
    public function vincularVenta(Request $request, Mantenimiento $mantenimiento)
    {
        $request->validate([
            'venta_id' => 'required|exists:ventas,id',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Verificar que la venta exista y esté activa
            $venta = Venta::where('id', $request->venta_id)
                ->where('estado', 1)
                ->firstOrFail();
            
            $mantenimiento->venta_id = $venta->id;
            $mantenimiento->pagado = true;
            $mantenimiento->costo_final = $venta->total;
            $mantenimiento->save();
            
            DB::commit();
            
            return redirect()->route('mantenimientos.show', $mantenimiento->id)
                ->with('success', 'Mantenimiento vinculado correctamente con la venta #' . $venta->id);
            
        } catch (Exception $e) {
            DB::rollBack();
            
            return redirect()->route('mantenimientos.show', $mantenimiento->id)
                ->with('error', 'Error al vincular venta: ' . $e->getMessage());
        }
    }
    
    /**
     * Reportes de mantenimientos
     */
    public function reportes(Request $request)
    {
        $fechaInicio = $request->fecha_inicio ?? now()->startOfMonth()->format('Y-m-d');
        $fechaFin = $request->fecha_fin ?? now()->format('Y-m-d');
        
        $mantenimientos = Mantenimiento::with(['cliente.persona', 'venta'])
            ->where(function ($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('fecha_ingreso', [$fechaInicio.' 00:00:00', $fechaFin.' 23:59:59'])
                    ->orWhereBetween('fecha_entrega_real', [$fechaInicio.' 00:00:00', $fechaFin.' 23:59:59']);
            })
            ->orderBy('fecha_ingreso', 'desc')
            ->get();
            
        return view('mantenimiento.reportes', compact('mantenimientos', 'fechaInicio', 'fechaFin'));
    }
}
