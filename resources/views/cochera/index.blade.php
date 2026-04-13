@extends('layouts.app')

@section('title', 'Cochera | Estacionamiento')

@section('content')
@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <div class="cw-page-header mt-4">
        <h1 class="cw-page-title">Cochera</h1>
        <div class="cw-page-actions">
            <a href="{{ route('cocheras.create') }}" class="btn btn-success">
                <i class="fas fa-plus-circle"></i> Nuevo registro
            </a>
            <a href="{{ route('cocheras.reportes') }}" class="btn btn-info">
                <i class="fas fa-chart-bar"></i> Reportes
            </a>
        </div>
    </div>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Cochera</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span><i class="fas fa-car me-1"></i> Vehículos en cochera</span>
            <form action="{{ route('cocheras.index') }}" method="GET" class="d-flex align-items-center gap-2">
                <label for="estado" class="mb-0">Estado:</label>
                <select id="estado" name="estado" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                    <option value="finalizado" {{ request('estado') == 'finalizado' ? 'selected' : '' }}>Finalizado</option>
                    <option value="cancelado" {{ request('estado') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                    <option value="todos" {{ request('estado') == 'todos' ? 'selected' : '' }}>Todos</option>
                </select>
            </form>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Placa</th>
                            <th>Cliente</th>
                            <th>Modelo/Color</th>
                            <th>Tipo</th>
                            <th>Ingreso</th>
                            <th>Tiempo</th>
                            <th>Ubicación</th>
                            <th>Estado</th>
                            <th>Monto</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($cocheras as $cochera)
                            @php
                                $fechaInicio = $cochera->fecha_ingreso;
                                $fechaFin = $cochera->fecha_salida ?? now();
                                $diff = $fechaInicio?->diff($fechaFin);
                                $tiempoFormateado = $diff
                                    ? (($diff->days > 0 ? $diff->days . ' día(s) ' : '') . $diff->h . ' hora(s) ' . $diff->i . ' minuto(s)')
                                    : '—';

                                $montoActual = $cochera->estado === 'activo'
                                    ? $cochera->calcularMonto()
                                    : ($cochera->monto_total ?? $cochera->calcularMonto());

                                $estadoBadge = match($cochera->estado) {
                                    'activo' => 'bg-success',
                                    'finalizado' => 'bg-secondary',
                                    'cancelado' => 'bg-danger',
                                    default => 'bg-light text-dark'
                                };
                            @endphp

                            <tr class="{{ $diff && $diff->days >= 1 && $cochera->estado === 'activo' ? 'table-warning' : '' }}">
                                <td><span class="badge bg-dark">{{ $cochera->placa }}</span></td>
                                <td>{{ $cochera->cliente->persona->razon_social ?? '—' }}</td>
                                <td>{{ $cochera->modelo }} ({{ $cochera->color }})</td>
                                <td>{{ $cochera->tipo_vehiculo }}</td>
                                <td>{{ $cochera->fecha_ingreso?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td>{{ $tiempoFormateado }}</td>
                                <td>{{ $cochera->ubicacion ?: 'No especificada' }}</td>
                                <td><span class="badge {{ $estadoBadge }}">{{ ucfirst($cochera->estado) }}</span></td>
                                <td>S/ {{ number_format($montoActual, 2) }}</td>
                                <td class="text-end">
                                    <a href="{{ route('cocheras.show', $cochera->id) }}" class="btn btn-sm btn-info" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    @if($cochera->estado === 'activo')
                                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#finalizarModal{{ $cochera->id }}" title="Finalizar">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    @else
                                        <a href="{{ route('cocheras.edit', $cochera->id) }}" class="btn btn-sm btn-secondary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif

                                    <form action="{{ route('cocheras.destroy', $cochera->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Eliminar" data-confirm="¿Está seguro de eliminar este registro?" data-confirm-confirm-text="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            @if($cochera->estado === 'activo')
                                <x-confirm-action-modal
                                    :modal-id="'finalizarModal' . $cochera->id"
                                    title="Finalizar estacionamiento"
                                    :action="route('cocheras.finalizar', $cochera->id)"
                                    confirm-text="Finalizar"
                                    confirm-class="btn btn-success"
                                >
                                    <p>¿Desea finalizar el estacionamiento del vehículo <strong>{{ $cochera->placa }}</strong>?</p>
                                    <div class="alert alert-info mb-0">
                                        <p class="mb-1">Tiempo: <strong>{{ $tiempoFormateado }}</strong></p>
                                        <p class="mb-0">Monto actual: <strong>S/ {{ number_format($montoActual, 2) }}</strong></p>
                                    </div>
                                </x-confirm-action-modal>
                            @endif
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">No hay registros de cochera.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-pagination-info :paginator="$cocheras" entity="registros de cochera" :preserve-query="true" />
        </div>
    </div>
</div>
@endsection