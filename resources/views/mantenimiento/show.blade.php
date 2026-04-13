@extends('layouts.app')

@section('title', 'Detalle de Mantenimiento')

@section('content')
@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <div class="cw-page-header mt-4">
        <h1 class="cw-page-title">Detalle de Mantenimiento #{{ $mantenimiento->id }}</h1>
        <div class="cw-page-actions">
            <a href="{{ route('mantenimientos.edit', $mantenimiento->id) }}" class="btn btn-secondary">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('mantenimientos.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('mantenimientos.index') }}">Mantenimiento</a></li>
        <li class="breadcrumb-item active">Detalle</li>
    </ol>

    @php
        $estadoBadge = match($mantenimiento->estado) {
            'recibido' => 'bg-secondary',
            'en_proceso' => 'bg-primary',
            'terminado' => 'bg-warning text-dark',
            'entregado' => 'bg-success',
            default => 'bg-light text-dark'
        };

        $diasRestantes = $mantenimiento->fecha_entrega_estimada
            ? now()->diffInDays($mantenimiento->fecha_entrega_estimada, false)
            : null;
    @endphp

    <div class="card mb-4">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                Estado actual:
                <span class="badge {{ $estadoBadge }}">{{ ucfirst(str_replace('_', ' ', $mantenimiento->estado)) }}</span>
            </div>

            <div class="d-flex flex-wrap gap-2">
                @if($mantenimiento->estado !== 'entregado')
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Cambiar estado
                        </button>
                        <div class="dropdown-menu">
                            @foreach(['recibido' => 'Recibido', 'en_proceso' => 'En proceso', 'terminado' => 'Terminado', 'entregado' => 'Entregado'] as $estado => $label)
                                @if($mantenimiento->estado !== $estado)
                                    <form action="{{ route('mantenimientos.cambiarEstado', $mantenimiento->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="estado" value="{{ $estado }}">
                                        <button type="submit" class="dropdown-item">{{ $label }}</button>
                                    </form>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(!$mantenimiento->pagado)
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#vincularVentaModal">
                        <i class="fas fa-money-bill"></i> Registrar pago
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">Cliente y vehículo</div>
                <div class="card-body">
                    <h6 class="text-muted mb-3">Datos del cliente</h6>
                    <dl class="row mb-4">
                        <dt class="col-sm-4">Cliente</dt>
                        <dd class="col-sm-8">{{ $mantenimiento->cliente->persona->razon_social }}</dd>

                        <dt class="col-sm-4">{{ $mantenimiento->cliente->persona->documento->tipo_documento }}</dt>
                        <dd class="col-sm-8">{{ $mantenimiento->cliente->persona->numero_documento }}</dd>

                        <dt class="col-sm-4">Teléfono</dt>
                        <dd class="col-sm-8">{{ $mantenimiento->cliente->persona->telefono ?: 'No disponible' }}</dd>
                    </dl>

                    <h6 class="text-muted mb-3">Datos del vehículo</h6>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Placa</dt>
                        <dd class="col-sm-8"><span class="badge bg-dark">{{ $mantenimiento->placa }}</span></dd>

                        <dt class="col-sm-4">Modelo</dt>
                        <dd class="col-sm-8">{{ $mantenimiento->modelo }}</dd>

                        <dt class="col-sm-4">Tipo</dt>
                        <dd class="col-sm-8">{{ $mantenimiento->tipo_vehiculo }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">Detalles del servicio</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-6">Tipo de servicio</dt>
                        <dd class="col-sm-6">{{ $mantenimiento->tipo_servicio }}</dd>

                        <dt class="col-sm-6">Fecha de ingreso</dt>
                        <dd class="col-sm-6">{{ $mantenimiento->fecha_ingreso?->format('d/m/Y') ?: '—' }}</dd>

                        <dt class="col-sm-6">Entrega estimada</dt>
                        <dd class="col-sm-6">
                            @if($mantenimiento->fecha_entrega_estimada)
                                {{ $mantenimiento->fecha_entrega_estimada->format('d/m/Y') }}
                                @if($mantenimiento->estado !== 'entregado')
                                    @if($diasRestantes < 0)
                                        <span class="badge bg-danger">Atrasado {{ abs($diasRestantes) }} día(s)</span>
                                    @elseif($diasRestantes === 0)
                                        <span class="badge bg-warning text-dark">Hoy</span>
                                    @else
                                        <span class="badge bg-info text-dark">Faltan {{ $diasRestantes }} día(s)</span>
                                    @endif
                                @endif
                            @else
                                No especificado
                            @endif
                        </dd>

                        <dt class="col-sm-6">Entrega real</dt>
                        <dd class="col-sm-6">{{ $mantenimiento->fecha_entrega_real?->format('d/m/Y') ?: 'Pendiente' }}</dd>

                        <dt class="col-sm-6">Mecánico responsable</dt>
                        <dd class="col-sm-6">{{ $mantenimiento->mecanico_responsable ?: 'No asignado' }}</dd>

                        <dt class="col-sm-6">Estado de pago</dt>
                        <dd class="col-sm-6">
                            @if($mantenimiento->pagado)
                                <span class="badge bg-success">Pagado</span>
                                @if($mantenimiento->venta_id)
                                    <a href="{{ route('ventas.show', $mantenimiento->venta_id) }}" class="badge bg-info text-dark">Venta #{{ $mantenimiento->venta_id }}</a>
                                @endif
                            @else
                                <span class="badge bg-danger">Pendiente</span>
                            @endif
                        </dd>

                        <dt class="col-sm-6">Costo estimado</dt>
                        <dd class="col-sm-6">{{ $mantenimiento->costo_estimado ? 'S/ ' . number_format($mantenimiento->costo_estimado, 2) : 'No especificado' }}</dd>

                        <dt class="col-sm-6">Costo final</dt>
                        <dd class="col-sm-6">{{ $mantenimiento->costo_final ? 'S/ ' . number_format($mantenimiento->costo_final, 2) : 'Pendiente' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Descripción del trabajo</div>
        <div class="card-body">
            <h6 class="text-muted mb-2">Diagnóstico / trabajo a realizar</h6>
            <div class="p-3 bg-light border rounded">{!! nl2br(e($mantenimiento->descripcion_trabajo)) !!}</div>

            @if($mantenimiento->observaciones)
                <h6 class="text-muted mt-4 mb-2">Observaciones adicionales</h6>
                <div class="p-3 bg-light border rounded">{!! nl2br(e($mantenimiento->observaciones)) !!}</div>
            @endif
        </div>
    </div>
</div>

@if(!$mantenimiento->pagado)
    <div class="modal fade" id="vincularVentaModal" tabindex="-1" aria-labelledby="vincularVentaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="vincularVentaModalLabel">Registrar pago</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p>Ingrese el ID de la venta asociada a este servicio.</p>
                    <form action="{{ route('mantenimientos.vincularVenta', $mantenimiento->id) }}" method="POST" id="formVincularVenta">
                        @csrf
                        <div class="mb-3">
                            <label for="venta_id" class="form-label">ID de venta</label>
                            <input type="number" name="venta_id" id="venta_id" class="form-control" min="1" required>
                            <div class="form-text">Ingrese el número de venta que contiene el pago del mantenimiento.</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formVincularVenta" class="btn btn-primary">Vincular venta</button>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection