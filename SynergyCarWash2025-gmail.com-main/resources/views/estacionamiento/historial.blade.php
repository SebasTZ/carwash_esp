@extends('layouts.app')

@section('title', 'Parking History')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Parking History</h1>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-history me-1"></i>
            Finished Records
            <a href="{{ route('estacionamiento.index') }}" class="btn btn-primary btn-sm float-end">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-striped">
                <thead>
                    <tr>
                        <th>License Plate</th>
                        <th>Customer</th>
                        <th>Brand/Model</th>
                        <th>Entry</th>
                        <th>Exit</th>
                        <th>Total Time</th>
                        <th>Rate/Hour</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($estacionamientos as $estacionamiento)
                    <tr>
                        <td>{{ $estacionamiento->placa }}</td>
                        <td>{{ $estacionamiento->cliente->persona->razon_social }}</td>
                        <td>{{ $estacionamiento->marca }} / {{ $estacionamiento->modelo }}</td>
                        <td>{{ $estacionamiento->hora_entrada->format('d/m/Y H:i') }}</td>
                        <td>{{ $estacionamiento->hora_salida->format('d/m/Y H:i') }}</td>
                        <td>{{ $estacionamiento->hora_entrada->diffForHumans($estacionamiento->hora_salida, true) }}</td>
                        <td>S/. {{ number_format($estacionamiento->tarifa_hora, 2) }}</td>
                        <td>S/. {{ number_format($estacionamiento->monto_total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection