<?php

namespace App\Http\Controllers;

use App\Models\PagoComision;
use App\Models\Lavador;
use App\Models\ControlLavado;
use App\Models\TipoVehiculo;
use App\Exports\ComisionesLavadorExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;

class PagoComisionController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:ver-pago-comision')->only(['index', 'show']);
        $this->middleware('can:crear-pago-comision')->only(['create', 'store']);
        $this->middleware('can:ver-historial-pago-comision')->only(['show']);
    }

    public function index()
    {
        $pagos = PagoComision::with('lavador')->get();
        return view('pagos_comisiones.index', compact('pagos'));
    }

    public function create()
    {
        $lavadores = Lavador::where('estado', 'activo')->get();
        return view('pagos_comisiones.create', compact('lavadores'));
    }

    public function store(Request $request)
    {
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

        PagoComision::create($request->all());
        return redirect()->route('pagos_comisiones.index')->with('success', 'Pago registrado correctamente.');
    }

    public function show(Lavador $lavador)
    {
        $pagos = PagoComision::where('lavador_id', $lavador->id)->orderBy('fecha_pago', 'desc')->get();
        // Acceso directo al reporte de comisiones de este lavador
        $reporteUrl = route('reporte.comisiones', ['lavador_id' => $lavador->id]);
        return view('pagos_comisiones.show', compact('lavador', 'pagos', 'reporteUrl'));
    }

    public function reporteComisiones(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->startOfMonth()->toDateString());
        $fechaFin = $request->input('fecha_fin', now()->endOfMonth()->toDateString());

        $lavadores = \App\Models\Lavador::where('estado', 'activo')->get();
        $data = [];
        foreach ($lavadores as $lavador) {
            // Lavados realizados en el rango
            $lavados = ControlLavado::where('lavador_id', $lavador->id)
                ->whereBetween('hora_llegada', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
                ->with('tipoVehiculo')
                ->get();
            $cantidad = $lavados->count();
            $comisionTotal = $lavados->sum(function($lavado) {
                return $lavado->tipoVehiculo ? $lavado->tipoVehiculo->comision : 0;
            });
            // Total pagado en el rango
            $pagado = $lavador->pagosComisiones()
                ->where(function($q) use ($fechaInicio, $fechaFin) {
                    $q->whereBetween('desde', [$fechaInicio, $fechaFin])
                      ->orWhereBetween('hasta', [$fechaInicio, $fechaFin]);
                })
                ->sum('monto_pagado');
            $saldo = $comisionTotal - $pagado;
            $data[] = [
                'lavador' => $lavador,
                'cantidad' => $cantidad,
                'comision_total' => $comisionTotal,
                'pagado' => $pagado,
                'saldo' => $saldo,
            ];
        }
        return view('pagos_comisiones.reporte', compact('data', 'fechaInicio', 'fechaFin'));
    }

    public function exportarComisiones(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->startOfMonth()->toDateString());
        $fechaFin = $request->input('fecha_fin', now()->endOfMonth()->toDateString());
        
        $lavadores = Lavador::where('estado', 'activo')->get();
        \Log::info('Lavadores activos exportados:', $lavadores->pluck('nombre')->toArray());
        $data = [];
        
        foreach ($lavadores as $lavador) {
            $lavados = ControlLavado::where('lavador_id', $lavador->id)
                ->whereBetween('hora_llegada', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
                ->with('tipoVehiculo')
                ->get();
            $cantidad = $lavados->count();
            $comisionTotal = $lavados->sum(function($lavado) {
                return $lavado->tipoVehiculo ? $lavado->tipoVehiculo->comision : 0;
            });
            $pagado = $lavador->pagosComisiones()
                ->where(function($q) use ($fechaInicio, $fechaFin) {
                    $q->whereBetween('desde', [$fechaInicio, $fechaFin])
                      ->orWhereBetween('hasta', [$fechaInicio, $fechaFin]);
                })
                ->sum('monto_pagado');
            $saldo = $comisionTotal - $pagado;
            $data[] = [
                'lavador' => $lavador,
                'cantidad' => $cantidad,
                'comision_total' => $comisionTotal,
                'pagado' => $pagado,
                'saldo' => $saldo,
            ];
        }
        
        // Ordenar por nombre del lavador para consistencia
        usort($data, function($a, $b) {
            return strcmp($a['lavador']->nombre, $b['lavador']->nombre);
        });
        
        \Log::info('Data enviada a Excel:', collect($data)->map(function($row) {
            return [
                'nombre' => $row['lavador']->nombre,
                'cantidad' => $row['cantidad'],
                'comision_total' => $row['comision_total'],
                'pagado' => $row['pagado'],
                'saldo' => $row['saldo'],
            ];
        })->toArray());
        
        return Excel::download(new ComisionesLavadorExport($data, $fechaInicio, $fechaFin), 'comisiones_lavadores.xlsx');
    }
}
