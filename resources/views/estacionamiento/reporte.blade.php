@extends('layouts.app')

@section('title', 'Parking Report')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">
        Parking Report 
        @if($reporte == 'diario')
            Daily
        @elseif($reporte == 'semanal')
            Weekly
        @elseif($reporte == 'mensual')
            Monthly
        @else
            Custom
        @endif
    </h1>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-chart-bar me-1"></i>
            Records Summary
            <div class="float-end">
                @if($reporte == 'personalizado')
                    <a href="{{ route('estacionamiento.export.personalizado', ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin]) }}" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </a>
                @else
                    <a href="{{ route('estacionamiento.export.' . $reporte) }}" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </a>
                @endif
                <a href="{{ route('estacionamiento.index') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            @if($reporte == 'personalizado')
                <div class="alert alert-info">
                    Showing records from {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} 
                    to {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
                </div>
            @endif

            <div class="row mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-primary text-white mb-4">
                        <div class="card-body">
                            <h4>{{ $estacionamientos->count() }}</h4>
                            <div>Total Records</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-warning text-white mb-4">
                        <div class="card-body">
                            <h4>{{ $estacionamientos->where('estado', 'ocupado')->count() }}</h4>
                            <div>In Use</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-success text-white mb-4">
                        <div class="card-body">
                            <h4>{{ $estacionamientos->where('estado', 'finalizado')->count() }}</h4>
                            <div>Finished</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-info text-white mb-4">
                        <div class="card-body">
                            <h4>S/. {{ number_format($estacionamientos->sum('monto_total'), 2) }}</h4>
                            <div>Total Collected</div>
                        </div>
                    </div>
                </div>
            </div>

            <table id="datatablesSimple" class="table table-striped">
                <thead>
                    <tr>
                        <th>Plate</th>
                        <th>Client</th>
                        <th>Brand/Model</th>
                        <th>Entry</th>
                        <th>Exit</th>
                        <th>Time</th>
                        <th>Rate/Hour</th>
                        <th>Total Amount</th>
                        <th>Status</th>
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