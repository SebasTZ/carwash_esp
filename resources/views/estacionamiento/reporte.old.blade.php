@extends('layouts.app')

@section('title', 'Reporte de Estacionamiento')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">
        Reporte de Estacionamiento
        @if($reporte == 'diario')
            Diario
        @elseif($reporte == 'semanal')
            Semanal
        @elseif($reporte == 'mensual')
            Mensual
        @else
            Personalizado
        @endif
    </h1>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-chart-bar me-1"></i>
            Resumen de Registros
            <div class="float-end">
                @if($reporte == 'personalizado')
                    <a href="{{ route('estacionamiento.export.personalizado', ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin]) }}" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel"></i> Exportar Excel
                    </a>
                @else
                    <a href="{{ route('estacionamiento.export.' . $reporte) }}" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel"></i> Exportar Excel
                    </a>
                @endif
                <a href="{{ route('estacionamiento.index') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
        <div class="card-body">
            @if($reporte == 'personalizado')
                <div class="alert alert-info">
                    Mostrando registros desde {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} 
                    hasta {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
                </div>
            @endif

            <div class="row mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-primary text-white mb-4">
                        <div class="card-body">
                            <h4>{{ $estacionamientos->count() }}</h4>
                            <div>Total de Registros</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-warning text-white mb-4">
                        <div class="card-body">
                            <h4>{{ $estacionamientos->where('estado', 'ocupado')->count() }}</h4>
                            <div>En Uso</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-success text-white mb-4">
                        <div class="card-body">
                            <h4>{{ $estacionamientos->where('estado', 'finalizado')->count() }}</h4>
                            <div>Finalizados</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-info text-white mb-4">
                        <div class="card-body">
                            <h4>S/. {{ number_format($estacionamientos->sum('monto_total'), 2) }}</h4>
                            <div>Total Recaudado</div>
                        </div>
                    </div>
                </div>
            </div>

            <table id="datatablesSimple" class="table table-striped">
                <thead>
                    <tr>
                        <th>Placa</th>
                        <th>Cliente</th>
                        <th>Marca/Modelo</th>
                        <th>Entrada</th>
                        <th>Salida</th>
                        <th>Tiempo</th>
                        <th>Tarifa/Hora</th>
                        <th>Monto Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($estacionamientos as $estacionamiento)
                    <tr>
                        <td>{{ $estacionamiento->placa }}</td>
                        <td>{{ $estacionamiento->cliente->persona->razon_social }}</td>
                        <td>{{ $estacionamiento->marca }} / {{ $estacionamiento->modelo }}</td>
                        <td>{{ $estacionamiento->hora_entrada->format('d/m/Y H:i') }}</td>
                        <td>{{ $estacionamiento->hora_salida ? $estacionamiento->hora_salida->format('d/m/Y H:i') : '-' }}</td>
                        <td>{{ $estacionamiento->hora_salida ? $estacionamiento->hora_entrada->diffForHumans($estacionamiento->hora_salida, true) : '-' }}</td>
                        <td>S/. {{ number_format($estacionamiento->tarifa_hora, 2) }}</td>
                        <td>S/. {{ number_format($estacionamiento->monto_total ?? 0, 2) }}</td>
                        <td>
                            <span class="badge rounded-pill bg-{{ $estacionamiento->estado == 'ocupado' ? 'warning' : 'success' }}">
                                {{ $estacionamiento->estado }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection