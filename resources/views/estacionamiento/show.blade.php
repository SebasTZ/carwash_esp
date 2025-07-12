@extends('layouts.app')

@section('title', 'Parking Details')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mt-4 text-primary">
            <i class="fas fa-car-side me-2"></i>Parking Details
        </h1>
        <a href="{{ route('estacionamiento.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-list me-2"></i>Back to List
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Vehicle Information</h5>
        </div>
        <div class="card-body">
            <p><strong>Customer:</strong> {{ $estacionamiento->cliente->persona->razon_social }}</p>
            <p><strong>License Plate:</strong> {{ $estacionamiento->placa }}</p>
            <p><strong>Brand:</strong> {{ $estacionamiento->marca }}</p>
            <p><strong>Model:</strong> {{ $estacionamiento->modelo }}</p>
            <p><strong>Phone:</strong> {{ $estacionamiento->telefono }}</p>
            <p><strong>Hourly Rate:</strong> S/. {{ number_format($estacionamiento->tarifa_hora, 2) }}</p>
            <p><strong>Entry Time:</strong> {{ $estacionamiento->hora_entrada }}</p>
            <p><strong>Exit Time:</strong> {{ $estacionamiento->hora_salida ?? 'N/A' }}</p>
            <p><strong>Total Amount:</strong> S/. {{ number_format($estacionamiento->monto_total, 2) ?? 'N/A' }}</p>
            <p><strong>Status:</strong> {{ ucfirst($estacionamiento->estado) }}</p>
        </div>
    </div>
</div>
@endsection