@extends('adminlte::page')

@section('title', 'Reportes de Cochera')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Reportes de Cochera / Estacionamiento</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('panel') }}"><i class="fas fa-home"></i> Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('cocheras.index') }}">Cochera</a></li>
                <li class="breadcrumb-item active">Reportes</li>
            </ol>
        </div>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Filtros de Reporte</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('cocheras.reportes') }}" method="GET">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fecha_inicio">Fecha de Inicio:</label>
                                    <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="{{ $fechaInicio }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fecha_fin">Fecha de Fin:</label>
                                    <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" value="{{ $fechaFin }}">
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-group w-100">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-filter mr-1"></i> Filtrar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-car"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total de Vehículos</span>
                    <span class="info-box-number">{{ $cocheras->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-primary"><i class="fas fa-money-bill"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Ingreso Total</span>
                    <span class="info-box-number">S/ {{ number_format($cocheras->sum('monto_total'), 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Vehículos Activos</span>
                    <span class="info-box-number">{{ $cocheras->where('estado', 'activo')->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-secondary"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Vehículos Finalizados</span>
                    <span class="info-box-number">{{ $cocheras->where('estado', 'finalizado')->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Resultados del Reporte</h3>
                    <div class="card-tools">
                        <button type="button" id="btnExportCochera" class="btn btn-success">
                            <i class="fas fa-file-excel mr-1"></i> Exportar a Excel
                        </button>
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <table id="tabla-reportes" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Placa</th>
                                <th>Cliente</th>
                                <th>Modelo</th>
                                <th>Tipo</th>
                                <th>Fecha Ingreso</th>
                                <th>Fecha Salida</th>
                                <th>Duración</th>
                                <th>Monto (S/)</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cocheras as $cochera)
                            @php
                                // Cálculo del tiempo de estacionamiento
                                if ($cochera->fecha_salida) {
                                    $fechaInicio = \Carbon\Carbon::parse($cochera->fecha_ingreso);
                                    $fechaFin = \Carbon\Carbon::parse($cochera->fecha_salida);
                                    $diff = $fechaInicio->diff($fechaFin);
                                    
                                    if ($diff->days > 0) {
                                        $tiempoEstadia = $diff->days . 'd ' . $diff->h . 'h';
                                    } else {
                                        $tiempoEstadia = $diff->h . 'h ' . $diff->i . 'm';
                                    }
                                } else if ($cochera->estado == 'activo') {
                                    $fechaInicio = \Carbon\Carbon::parse($cochera->fecha_ingreso);
                                    $fechaFin = now();
                                    $diff = $fechaInicio->diff($fechaFin);
                                    
                                    if ($diff->days > 0) {
                                        $tiempoEstadia = $diff->days . 'd ' . $diff->h . 'h';
                                    } else {
                                        $tiempoEstadia = $diff->h . 'h ' . $diff->i . 'm';
                                    }
                                } else {
                                    $tiempoEstadia = "-";
                                }
                                
                                // Calcular monto si está activo o usar el guardado si ya finalizó
                                if ($cochera->estado == 'activo') {
                                    $monto = $cochera->calcularMonto();
                                } else {
                                    $monto = $cochera->monto_total ?: 0;
                                }
                            @endphp
                            <tr>
                                <td>{{ $cochera->id }}</td>
                                <td>
                                    <span class="badge badge-dark">{{ $cochera->placa }}</span>
                                </td>
                                <td>{{ $cochera->cliente->persona->razon_social }}</td>
                                <td>{{ $cochera->modelo }} ({{ $cochera->color }})</td>
                                <td>{{ $cochera->tipo_vehiculo }}</td>
                                <td>{{ $cochera->fecha_ingreso->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($cochera->fecha_salida)
                                        {{ \Carbon\Carbon::parse($cochera->fecha_salida)->format('d/m/Y H:i') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $tiempoEstadia }}</td>
                                <td>{{ number_format($monto, 2) }}</td>
                                <td>
                                    @if($cochera->estado == 'activo')
                                        <span class="badge badge-success">Activo</span>
                                    @elseif($cochera->estado == 'finalizado')
                                        <span class="badge badge-secondary">Finalizado</span>
                                    @elseif($cochera->estado == 'cancelado')
                                        <span class="badge badge-danger">Cancelado</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('cocheras.show', $cochera->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center">No hay registros disponibles para el periodo seleccionado</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="8" style="text-align:right">Total:</th>
                                <th>S/ {{ number_format($cocheras->sum(function($cochera) {
                                    return $cochera->estado == 'activo' ? $cochera->calcularMonto() : ($cochera->monto_total ?: 0);
                                }), 2) }}</th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Ingresos Diarios</h3>
                </div>
                <div class="card-body">
                    <canvas id="ingresosPorDia" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Distribución por Tipo de Vehículo</h3>
                </div>
                <div class="card-body">
                    <canvas id="vehiculosPorTipo" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script type="application/json" id="cochera-ingresos-labels">@json($cocheras->where('estado', 'finalizado')->groupBy(function($date) {
        return \Carbon\Carbon::parse($date->fecha_salida)->format('d/m/Y');
    })->map(function($group) {
        return $group->sum('monto_total');
    })->keys())</script>

    <script type="application/json" id="cochera-ingresos-data">@json($cocheras->where('estado', 'finalizado')->groupBy(function($date) {
        return \Carbon\Carbon::parse($date->fecha_salida)->format('d/m/Y');
    })->map(function($group) {
        return $group->sum('monto_total');
    })->values())</script>

    <script type="application/json" id="cochera-tipos-labels">@json($cocheras->groupBy('tipo_vehiculo')->map(function($group) {
        return $group->count();
    })->keys())</script>

    <script type="application/json" id="cochera-tipos-data">@json($cocheras->groupBy('tipo_vehiculo')->map(function($group) {
        return $group->count();
    })->values())</script>
</div>
@stop

@section('css')
<style>
    .info-box-number {
        font-size: 1.5rem;
        font-weight: bold;
    }
</style>
@stop

@section('js')
@vite(['resources/js/modules/CocheraReportesManager.js'])
@stop