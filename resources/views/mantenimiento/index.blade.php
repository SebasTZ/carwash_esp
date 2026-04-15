@extends('layouts.app')

@section('title', 'Mantenimiento de Vehículos')

@section('content')
@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <div class="cw-page-header mt-4">
        <h1 class="cw-page-title">Mantenimiento de Vehículos</h1>
        <div class="cw-page-actions">
            <a href="{{ route('mantenimientos.create') }}" class="btn btn-success">
                <i class="fas fa-plus-circle"></i> Nuevo mantenimiento
            </a>
            <a href="{{ route('mantenimientos.reportes') }}" class="btn btn-info">
                <i class="fas fa-chart-bar"></i> Reportes
            </a>
        </div>
    </div>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Mantenimiento</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span><i class="fas fa-tools me-1"></i> Servicios registrados</span>
            <form action="{{ route('mantenimientos.index') }}" method="GET" class="d-flex align-items-center gap-2">
                <label for="estado" class="mb-0">Estado:</label>
                <select id="estado" name="estado" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="recibido" {{ request('estado') == 'recibido' ? 'selected' : '' }}>Recibido</option>
                    <option value="en_proceso" {{ request('estado') == 'en_proceso' ? 'selected' : '' }}>En proceso</option>
                    <option value="terminado" {{ request('estado') == 'terminado' ? 'selected' : '' }}>Terminado</option>
                    <option value="entregado" {{ request('estado') == 'entregado' ? 'selected' : '' }}>Entregado</option>
                    <option value="todos" {{ request('estado') == 'todos' ? 'selected' : '' }}>Todos</option>
                </select>
            </form>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Placa</th>
                            <th>Cliente</th>
                            <th>Vehículo</th>
                            <th>Servicio</th>
                            <th>Ingreso</th>
                            <th>Entrega est.</th>
                            <th>Estado</th>
                            <th>Pago</th>
                            <th>Costo</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($mantenimientos as $mantenimiento)
                            @php
                                $estadoBadge = match($mantenimiento->estado) {
                                    'recibido' => 'bg-secondary',
                                    'en_proceso' => 'bg-primary',
                                    'terminado' => 'bg-warning text-dark',
                                    'entregado' => 'bg-success',
                                    default => 'bg-light text-dark'
                                };

                                $fechaIngreso = $mantenimiento->fecha_ingreso?->format('d/m/Y');
                                $fechaEntregaEstimada = $mantenimiento->fecha_entrega_estimada?->format('d/m/Y');
                                $diasRestantes = $mantenimiento->fecha_entrega_estimada
                                    ? now()->diffInDays($mantenimiento->fecha_entrega_estimada, false)
                                    : null;
                                $costo = $mantenimiento->costo_final ?? $mantenimiento->costo_estimado;
                            @endphp

                            <tr>
                                <td>{{ $mantenimiento->id }}</td>
                                <td><span class="badge bg-dark">{{ $mantenimiento->placa }}</span></td>
                                <td>{{ $mantenimiento->cliente->persona->razon_social ?? '—' }}</td>
                                <td>{{ $mantenimiento->modelo }} ({{ $mantenimiento->tipo_vehiculo }})</td>
                                <td>{{ $mantenimiento->tipo_servicio }}</td>
                                <td>{{ $fechaIngreso ?? '—' }}</td>
                                <td>
                                    @if($fechaEntregaEstimada)
                                        <div>{{ $fechaEntregaEstimada }}</div>
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
                                        —
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $estadoBadge }}">
                                        {{ ucfirst(str_replace('_', ' ', $mantenimiento->estado)) }}
                                    </span>
                                </td>
                                <td>
                                    @if($mantenimiento->pagado)
                                        <span class="badge bg-success">Pagado</span>
                                    @else
                                        <span class="badge bg-danger">Pendiente</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!is_null($costo))
                                        S/ {{ number_format($costo, 2) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('mantenimientos.show', $mantenimiento->id) }}" class="btn btn-sm btn-info" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('mantenimientos.edit', $mantenimiento->id) }}" class="btn btn-sm btn-secondary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <x-confirm-delete
                                        :action="route('mantenimientos.destroy', $mantenimiento->id)"
                                        message="¿Está seguro de eliminar este mantenimiento?"
                                        icon-only
                                    />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center">No hay mantenimientos registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-pagination-info :paginator="$mantenimientos" entity="mantenimientos" :preserve-query="true" />
        </div>
    </div>
</div>
@endsection