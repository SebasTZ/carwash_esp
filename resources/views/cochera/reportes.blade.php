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
                        <button type="button" class="btn btn-success" onclick="exportToExcel()">
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
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/dataTables.bootstrap4.min.css">
<style>
    .info-box-number {
        font-size: 1.5rem;
        font-weight: bold;
    }
</style>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
<script>
    $(document).ready(function() {
        $('#tabla-reportes').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
            },
            "pageLength": 10,
            "order": [[0, "desc"]]
        });

        // Datos para el gráfico de ingresos por día
        const ingresosPorDiaCtx = document.getElementById('ingresosPorDia').getContext('2d');
        const ingresosPorDiaChart = new Chart(ingresosPorDiaCtx, {
            type: 'bar',
            data: {
                labels: @json($cocheras->where('estado', 'finalizado')->groupBy(function($date) {
                    return \Carbon\Carbon::parse($date->fecha_salida)->format('d/m/Y');
                })->map(function($group) {
                    return $group->sum('monto_total');
                })->keys()),
                datasets: [{
                    label: 'Ingresos Diarios (S/)',
                    data: @json($cocheras->where('estado', 'finalizado')->groupBy(function($date) {
                        return \Carbon\Carbon::parse($date->fecha_salida)->format('d/m/Y');
                    })->map(function($group) {
                        return $group->sum('monto_total');
                    })->values()),
                    backgroundColor: 'rgba(60, 141, 188, 0.8)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'S/ ' + value;
                            }
                        }
                    }
                }
            }
        });

        // Datos para el gráfico de tipos de vehículos
        const vehiculosPorTipoCtx = document.getElementById('vehiculosPorTipo').getContext('2d');
        const tiposVehiculo = @json($cocheras->groupBy('tipo_vehiculo')->map(function($group) {
            return $group->count();
        })->keys());
        const conteoVehiculos = @json($cocheras->groupBy('tipo_vehiculo')->map(function($group) {
            return $group->count();
        })->values());
        
        const colores = [
            'rgba(60, 141, 188, 0.8)',
            'rgba(255, 193, 7, 0.8)',
            'rgba(40, 167, 69, 0.8)',
            'rgba(220, 53, 69, 0.8)',
            'rgba(108, 117, 125, 0.8)',
            'rgba(23, 162, 184, 0.8)',
            'rgba(111, 66, 193, 0.8)'
        ];

        const vehiculosPorTipoChart = new Chart(vehiculosPorTipoCtx, {
            type: 'pie',
            data: {
                labels: tiposVehiculo,
                datasets: [{
                    data: conteoVehiculos,
                    backgroundColor: colores.slice(0, tiposVehiculo.length),
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
    });

    function exportToExcel() {
        // Crear una tabla temporal para exportar
        const tabla = document.getElementById('tabla-reportes').cloneNode(true);
        
        // Eliminar la última columna de acciones
        Array.from(tabla.querySelectorAll('tr')).forEach(row => {
            if (row.lastElementChild) {
                row.removeChild(row.lastElementChild);
            }
        });
        
        // Extraer títulos y datos
        const titles = Array.from(tabla.querySelectorAll('thead th')).map(th => th.innerText);
        const data = Array.from(tabla.querySelectorAll('tbody tr')).map(row => 
            Array.from(row.querySelectorAll('td')).map(td => td.innerText)
        );
        
        // Crear el libro y la hoja
        const wb = XLSX.utils.book_new();
        wb.Props = {
            Title: "Reporte de Cochera",
            Author: "Sistema Carwash",
            CreatedDate: new Date()
        };
        
        // Agregar los datos a la hoja
        wb.SheetNames.push("Report");
        const ws = XLSX.utils.aoa_to_sheet([
            titles,
            ...data
        ]);
        wb.Sheets["Report"] = ws;
        
        // Generar y descargar el archivo Excel
        const fechaActual = new Date().toISOString().slice(0, 10);
        XLSX.writeFile(wb, `Reporte_Cochera_${fechaActual}.xlsx`);
    }
</script>
@stop