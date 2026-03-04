@extends('layouts.app')

@section('title', 'Detalle de Vehículo en Cochera')

@section('content')
@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <div class="cw-page-header mt-4">
        <h1 class="cw-page-title">Detalle de Vehículo en Cochera</h1>
        <div class="cw-page-actions">
            @if($cochera->estado === 'activo')
                <a href="{{ route('cocheras.edit', $cochera->id) }}" class="btn btn-secondary">
                    <i class="fas fa-edit"></i> Editar
                </a>
            @endif
            <a href="{{ route('cocheras.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('cocheras.index') }}">Cochera</a></li>
        <li class="breadcrumb-item active">Detalle</li>
    </ol>

    @php
        $estadoBadge = match($cochera->estado) {
            'activo' => 'bg-success',
            'finalizado' => 'bg-secondary',
            'cancelado' => 'bg-danger',
            default => 'bg-light text-dark'
        };

        if ($cochera->estado === 'activo') {
            $fechaInicio = $cochera->fecha_ingreso;
            $fechaFin = now();
        } elseif ($cochera->fecha_salida) {
            $fechaInicio = $cochera->fecha_ingreso;
            $fechaFin = $cochera->fecha_salida;
        } else {
            $fechaInicio = null;
            $fechaFin = null;
        }

        if ($fechaInicio && $fechaFin) {
            $diff = $fechaInicio->diff($fechaFin);
            $tiempoEstadia = ($diff->days > 0 ? $diff->days . ' día(s) ' : '') . $diff->h . ' hora(s) ' . $diff->i . ' minuto(s)';
        } else {
            $tiempoEstadia = 'No disponible';
        }
    @endphp

    <div class="row">
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Información del vehículo</span>
                    <span class="badge {{ $estadoBadge }}">{{ ucfirst($cochera->estado) }}</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="mb-0">
                                <dt>Placa</dt>
                                <dd><span class="badge bg-dark">{{ $cochera->placa }}</span></dd>

                                <dt>Modelo</dt>
                                <dd>{{ $cochera->modelo }}</dd>

                                <dt>Color</dt>
                                <dd>{{ $cochera->color }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="mb-0">
                                <dt>Tipo de vehículo</dt>
                                <dd>{{ $cochera->tipo_vehiculo }}</dd>

                                <dt>Ubicación</dt>
                                <dd>{{ $cochera->ubicacion ?: 'No especificada' }}</dd>
                            </dl>
                        </div>
                    </div>

                    <div class="alert alert-light border mt-3 mb-0">
                        <strong>Observaciones:</strong>
                        <div>{{ $cochera->observaciones ?: 'Sin observaciones' }}</div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">Cliente</div>
                <div class="card-body">
                    <dl class="mb-0">
                        <dt>Nombre / razón social</dt>
                        <dd>{{ $cochera->cliente->persona->razon_social }}</dd>

                        <dt>{{ $cochera->cliente->persona->documento->tipo_documento }}</dt>
                        <dd>{{ $cochera->cliente->persona->numero_documento }}</dd>

                        <dt>Teléfono</dt>
                        <dd>{{ $cochera->cliente->persona->telefono ?: 'No disponible' }}</dd>

                        <dt>Dirección</dt>
                        <dd>{{ $cochera->cliente->persona->direccion ?: 'No disponible' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">Detalles de estacionamiento</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl>
                                <dt>Fecha y hora de ingreso</dt>
                                <dd>{{ $cochera->fecha_ingreso?->format('d/m/Y H:i') ?: '—' }}</dd>

                                <dt>Fecha y hora de salida</dt>
                                <dd>{{ $cochera->fecha_salida?->format('d/m/Y H:i') ?: 'No registrada aún' }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl>
                                <dt>Tarifa por hora</dt>
                                <dd>S/ {{ number_format($cochera->tarifa_hora, 2) }}</dd>

                                <dt>Tarifa por día</dt>
                                <dd>{{ $cochera->tarifa_dia ? 'S/ ' . number_format($cochera->tarifa_dia, 2) : 'No aplica' }}</dd>
                            </dl>
                        </div>
                    </div>

                    <div class="alert {{ $cochera->estado === 'activo' ? 'alert-primary' : 'alert-secondary' }} mb-0">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="mb-1">Tiempo de estacionamiento</h6>
                                <p class="h5 mb-0">{{ $tiempoEstadia }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-1">Monto a pagar</h6>
                                <p class="h4 mb-0">S/ {{ number_format($montoActualizado, 2) }}</p>
                            </div>
                        </div>
                    </div>

                    @if($cochera->estado === 'activo')
                        <button type="button" class="btn btn-success w-100 mt-3" data-bs-toggle="modal" data-bs-target="#finalizarModal">
                            <i class="fas fa-check"></i> Finalizar estacionamiento
                        </button>
                    @endif
                </div>

                @if($cochera->estado === 'activo')
                    <div class="card-footer text-end">
                        <form action="{{ route('cocheras.destroy', $cochera->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" data-confirm="¿Está seguro de eliminar este registro?" data-confirm-confirm-text="Eliminar">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($cochera->estado === 'activo')
    <x-confirm-action-modal
        modal-id="finalizarModal"
        title="Finalizar estacionamiento"
        :action="route('cocheras.finalizar', $cochera->id)"
        confirm-text="Finalizar y cobrar"
        confirm-class="btn btn-success"
    >
        <p>¿Desea finalizar el estacionamiento del vehículo <strong>{{ $cochera->placa }}</strong>?</p>
        <div class="alert alert-info mb-2">
            <p class="mb-1">Tiempo: <strong>{{ $tiempoEstadia }}</strong></p>
            <p class="mb-0">Monto a pagar: <strong>S/ {{ number_format($montoActualizado, 2) }}</strong></p>
        </div>
        <p class="text-muted mb-0">Al confirmar se registrará la fecha y hora actual como salida y se calculará el monto final.</p>
    </x-confirm-action-modal>
@endif
@endsection