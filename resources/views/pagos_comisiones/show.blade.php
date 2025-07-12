@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Historial de Pagos de Comisión de <span class="badge bg-light text-primary">{{ $lavador->nombre }}</span></h4>
                    <a href="{{ $reporteUrl }}" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-chart-line me-1"></i> Ver Reporte de Comisiones
                    </a>
                </div>
                <div class="card-body">
                    @if($pagos->isEmpty())
                        <div class="alert alert-info">No hay pagos de comisión registrados para este lavador.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Monto Pagado</th>
                                        <th>Desde</th>
                                        <th>Hasta</th>
                                        <th>Fecha de Pago</th>
                                        <th>Observación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pagos as $pago)
                                        <tr>
                                            <td><span class="badge bg-success">S/ {{ number_format($pago->monto_pagado, 2) }}</span></td>
                                            <td>{{ \Carbon\Carbon::parse($pago->desde)->format('d/m/Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($pago->hasta)->format('d/m/Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}</td>
                                            <td>{{ $pago->observacion ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                <div class="card-footer text-end">
                    <a href="{{ route('pagos_comisiones.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Volver a Pagos
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection