@extends('layouts.app')

@section('title', 'Detalles de la Cita')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Detalles de la Cita #{{ $cita->id }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('citas.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            @if($cita->estado != 'completada' && $cita->estado != 'cancelada')
            <a href="{{ route('citas.edit', $cita) }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-edit"></i> Editar
            </a>
            @endif
            <a href="{{ route('citas.dashboard') }}" class="btn btn-sm btn-outline-info">
                <i class="fas fa-tachometer-alt"></i> Panel
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5>Información de la Cita</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Cliente:</strong> {{ $cita->cliente->persona->razon_social }}</p>
                        <p><strong>Documento:</strong> {{ $cita->cliente->persona->documento->tipo_documento ?? 'N/D' }} {{ $cita->cliente->persona->numero_documento }}</p>
                        <p><strong>Teléfono:</strong> {{ $cita->cliente->persona->telefono ?? 'No registrado' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Fecha:</strong> {{ $cita->fecha->format('d/m/Y') }}</p>
                        <p><strong>Hora:</strong> {{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}</p>
                        <p><strong>Estado:</strong> 
                            @switch($cita->estado)
                                @case('pendiente')
                                    Pendiente
                                    @break
                                @case('en_proceso')
                                    En Proceso
                                    @break
                                @case('completada')
                                    Completada
                                    @break
                                @case('cancelada')
                                    Cancelada
                                    @break
                            @endswitch
                        </p>
                        <p><strong>Posición en Cola:</strong> {{ $cita->posicion_cola }}</p>
                    </div>
                </div>
                <div class="mb-3">
                    <strong>Notas:</strong>
                    <div class="border rounded p-2 bg-light">{{ $cita->notas ?? 'Sin notas' }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5>Acciones</h5>
            </div>
            <div class="card-body">
                <div class="d-flex flex-column gap-2">
                    <a href="{{ route('citas.index') }}" class="btn btn-secondary">Volver a la Lista</a>
                    @if($cita->estado != 'completada' && $cita->estado != 'cancelada')
                    <a href="{{ route('citas.edit', $cita) }}" class="btn btn-primary">Editar Cita</a>
                    @endif
                    <a href="{{ route('citas.dashboard') }}" class="btn btn-info">Ir al Panel</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection