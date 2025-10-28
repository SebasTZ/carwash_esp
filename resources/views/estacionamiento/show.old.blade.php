@extends('layouts.app')

@section('title', 'Detalle de Estacionamiento')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mt-4 text-primary">
            <i class="fas fa-car-side me-2"></i>Detalle de Estacionamiento
        </h1>
        <a href="{{ route('estacionamiento.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-list me-2"></i>Volver a la Lista
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Información del Vehículo</h5>
        </div>
        <div class="card-body">
            <p><strong>Cliente:</strong> {{ $estacionamiento->cliente->persona->razon_social }}</p>
            <p><strong>Placa:</strong> {{ $estacionamiento->placa }}</p>
            <p><strong>Marca:</strong> {{ $estacionamiento->marca }}</p>
            <p><strong>Modelo:</strong> {{ $estacionamiento->modelo }}</p>
            <p><strong>Teléfono:</strong> {{ $estacionamiento->telefono }}</p>
            <p><strong>Tarifa por Hora:</strong> S/. {{ number_format($estacionamiento->tarifa_hora, 2) }}</p>
            <p><strong>Hora de Entrada:</strong> {{ $estacionamiento->hora_entrada }}</p>
            <p><strong>Hora de Salida:</strong> {{ $estacionamiento->hora_salida ?? 'N/A' }}</p>
            <p><strong>Monto Total:</strong> S/. {{ number_format($estacionamiento->monto_total, 2) ?? 'N/A' }}</p>
            <p><strong>Estado:</strong> {{ ucfirst($estacionamiento->estado) }}</p>
        </div>
    </div>
</div>
@endsection