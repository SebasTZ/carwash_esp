<?php

namespace App\Http\Controllers;


use App\Models\PagoComision;
use App\Models\Lavador;
use App\Models\ControlLavado;
use App\Models\TipoVehiculo;
use App\Services\ComisionService;
use App\Exports\ComisionesLavadorExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PagoComisionController extends Controller
{
    protected ComisionService $comisionService;

    public function __construct(ComisionService $comisionService)
    {
        $this->comisionService = $comisionService;
    }

    public function index()
    {
        $this->authorizePermission('ver-pago-comision');

        $pagos = PagoComision::with('lavador')->paginate(15);
        return view('pagos_comisiones.index', compact('pagos'));
    }

    public function create()
    {
        $this->authorizePermission('crear-pago-comision');

        $lavadores = Lavador::where('estado', 'activo')->get();
        return view('pagos_comisiones.create', compact('lavadores'));
    }

    public function store(Request $request)
    {
        $this->authorizePermission('crear-pago-comision');

        $request->validate([
            'lavador_id' => 'required|exists:lavadores,id',
            'monto_pagado' => 'required|numeric',
            'desde' => 'required|date',
            'hasta' => 'required|date',
            'fecha_pago' => 'required|date',
            'observacion' => 'nullable',
        ]);

        // Validar si existen lavados no liquidados en el rango
        $lavadosNoLiquidados = ControlLavado::where('lavador_id', $request->lavador_id)
            ->whereBetween('hora_llegada', [$request->desde . ' 00:00:00', $request->hasta . ' 23:59:59'])
            ->whereDoesntHave('pagosComisiones', function($q) use ($request) {
                $q->where(function($q2) use ($request) {
                    $q2->whereBetween('desde', [$request->desde, $request->hasta])
                       ->orWhereBetween('hasta', [$request->desde, $request->hasta]);
                });
            })
            ->exists();

        if (!$lavadosNoLiquidados) {
            return redirect()->back()->with('warning', 'No hay lavados pendientes de liquidar para este lavador en el rango seleccionado.');
        }

        PagoComision::create($request->only(['lavador_id', 'monto_pagado', 'desde', 'hasta', 'fecha_pago', 'observacion']));
        return redirect()->route('pagos_comisiones.index')->with('success', 'Pago registrado correctamente.');
    }


    public function show(Lavador $lavador, Request $request)
    {
        $this->authorizeAnyPermission(['ver-historial-pago-comision', 'ver-pago-comision']);

        // Si no hay fechas en la URL, usar primer y último día del mes actual
        $fechaInicio = $request->input('fecha_inicio', now()->startOfMonth()->toDateString());
        $fechaFin = $request->input('fecha_fin', now()->endOfMonth()->toDateString());

        $pagosQuery = PagoComision::where('lavador_id', $lavador->id)
            ->where('desde', '<=', $fechaFin)
            ->where('hasta', '>=', $fechaInicio);

        $pagos = $pagosQuery->orderBy('fecha_pago', 'desc')->get();

        // Ruta al reporte de este lavador con el mismo rango de fechas
        $reporteUrl = route('reporte.comisiones', [
            'lavador_id' => $lavador->id,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin
        ]);

        return view('pagos_comisiones.show', compact('lavador', 'pagos', 'reporteUrl', 'fechaInicio', 'fechaFin'));
    }


    public function reporteComisiones(Request $request)
    {
        $this->authorizePermission('ver-pago-comision');

        $fechaInicio = $request->input('fecha_inicio', now()->startOfMonth()->toDateString());
        $fechaFin = $request->input('fecha_fin', now()->endOfMonth()->toDateString());

        // Usar el servicio para generar el reporte (lógica centralizada)
        $data = $this->comisionService->generarReporteComisiones($fechaInicio, $fechaFin);

        return view('pagos_comisiones.reporte', compact('data', 'fechaInicio', 'fechaFin'));
    }


    public function exportarComisiones(Request $request)
    {
        $this->authorizePermission('ver-pago-comision');

        $fechaInicio = $request->input('fecha_inicio', now()->startOfMonth()->toDateString());
        $fechaFin = $request->input('fecha_fin', now()->endOfMonth()->toDateString());
        
        // Usar el servicio para generar el reporte (lógica centralizada)
        $data = $this->comisionService->generarReporteComisiones($fechaInicio, $fechaFin);

        Log::info('Comisiones exportadas:', [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'cantidad_lavadores' => count($data),
        ]);
        
        return Excel::download(new ComisionesLavadorExport($data, $fechaInicio, $fechaFin), 'comisiones_lavadores.xlsx');
    }
}
