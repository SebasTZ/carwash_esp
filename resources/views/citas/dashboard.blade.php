@extends('layouts.app')

@section('title', 'Panel de Citas')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3">
    <h1 class="h2 mb-0">Panel de Citas <small class="text-muted">{{ now()->format('d/m/Y') }}</small></h1>
    <div class="d-flex align-items-center gap-3">
        <span class="refresh-timer">
            <i class="fas fa-sync-alt"></i>
            Refrescar en <span id="countdown" class="fw-bold">60</span>s
        </span>
        <div class="btn-group">
            <a href="{{ route('citas.index') }}" class="btn btn-action btn-outline-secondary">
                <i class="fas fa-list"></i> Ver Todas
            </a>
            <a href="{{ route('citas.create') }}" class="btn btn-action btn-outline-primary">
                <i class="fas fa-plus-circle"></i> Nueva Cita
            </a>
            <button type="button" class="btn btn-action btn-outline-success" onclick="window.location.reload()">
                <i class="fas fa-sync-alt"></i> Refrescar
            </button>
        </div>
    </div>
</div>

<x-flash-alert />

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white stats-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1">Total de Citas</h6>
                        <p class="display-5 mb-0">{{ $citas->count() }}</p>
                    </div>
                    <i class="fas fa-calendar-alt stats-icon"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white stats-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1">Pendientes</h6>
                        <p class="display-5 mb-0">{{ $citas->where('estado', 'pendiente')->count() }}</p>
                    </div>
                    <i class="fas fa-clock stats-icon"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white stats-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1">En Proceso</h6>
                        <p class="display-5 mb-0">{{ $citas->where('estado', 'en_proceso')->count() }}</p>
                    </div>
                    <i class="fas fa-spinner stats-icon"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white stats-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1">Completadas</h6>
                        <p class="display-5 mb-0">{{ $citas->where('estado', 'completada')->count() }}</p>
                    </div>
                    <i class="fas fa-check-circle stats-icon"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pending Appointments -->
<div class="card mb-4">
    <div class="section-header bg-light">
        <i class="fas fa-clock text-warning"></i>
        <h5 class="mb-0">Cola de Citas Pendientes</h5>
    </div>
    <div class="card-body">
        @if($citas->where('estado', 'pendiente')->count() > 0)
            <div class="row g-4">
                @foreach($citas->where('estado', 'pendiente')->sortBy('posicion_cola') as $cita)
                <div
                    class="col-xl-3 col-lg-4 col-md-6"
                    x-data="citasDashboardCard({
                        estado: '{{ $cita->estado }}',
                        urls: {
                            iniciar:   '{{ route('citas.iniciar',   $cita) }}',
                            cancelar:  '{{ route('citas.cancelar',  $cita) }}'
                        }
                    })"
                    x-show="!removed"
                    x-cloak
                >
                    <div class="card cita-card h-100 border-warning position-relative">
                        <div class="position-badge border border-warning text-warning">{{ $cita->posicion_cola }}</div>
                        <div class="card-header d-flex justify-content-between align-items-center bg-light py-2 border-0">
                            <span class="status-badge bg-warning text-white">
                                <i class="fas fa-clock"></i> Pendiente
                            </span>
                            <span class="text-muted">{{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}</span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title mb-2">{{ $cita->cliente->persona->razon_social }}</h5>
                            <p class="card-text mb-3">
                                <i class="fas fa-phone me-2 text-muted"></i>
                                {{ $cita->cliente->persona->telefono ?? 'Sin teléfono' }}
                            </p>
                            @if($cita->notas)
                            <div class="small text-muted border-top pt-2 mt-2">
                                <i class="fas fa-sticky-note me-1"></i>
                                {{ \Illuminate\Support\Str::limit($cita->notas, 60) }}
                            </div>
                            @endif
                        </div>
                        <div class="card-footer bg-transparent pt-0 border-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <button
                                    type="button"
                                    class="btn btn-success btn-sm btn-action"
                                    :disabled="loading"
                                    @click="cambiarEstado(urls.iniciar, 'en_proceso')"
                                >
                                    <span x-show="!loading"><i class="fas fa-play"></i> Iniciar</span>
                                    <span x-show="loading" x-cloak><i class="fas fa-spinner fa-spin"></i></span>
                                </button>
                                <div class="card-actions">
                                    <x-tooltip text="Ver detalle">
                                        <a href="{{ route('citas.show', $cita) }}" class="btn btn-outline-secondary btn-sm btn-action">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </x-tooltip>
                                    <button
                                        type="button"
                                        class="btn btn-outline-danger btn-sm btn-action"
                                        :disabled="loading"
                                        @click="cambiarEstado(urls.cancelar, 'cancelada', '¿Está seguro de cancelar esta cita?')"
                                    >
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-info m-0">
                <i class="fas fa-info-circle me-2"></i>
                No hay citas pendientes para hoy
            </div>
        @endif
    </div>
</div>

<!-- In-Process Appointments -->
<div class="card mb-4">
    <div class="section-header bg-light">
        <i class="fas fa-spinner text-info"></i>
        <h5 class="mb-0">Citas en Proceso</h5>
    </div>
    <div class="card-body">
        @if($citas->where('estado', 'en_proceso')->count() > 0)
            <div class="row g-4">
                @foreach($citas->where('estado', 'en_proceso')->sortBy('posicion_cola') as $cita)
                <div
                    class="col-xl-3 col-lg-4 col-md-6"
                    x-data="citasDashboardCard({
                        estado: '{{ $cita->estado }}',
                        urls: {
                            completar: '{{ route('citas.completar', $cita) }}',
                            cancelar:  '{{ route('citas.cancelar',  $cita) }}'
                        }
                    })"
                    x-show="!removed"
                    x-cloak
                >
                    <div class="card cita-card h-100 border-info position-relative">
                        <div class="position-badge border border-info text-info">{{ $cita->posicion_cola }}</div>
                        <div class="card-header d-flex justify-content-between align-items-center bg-light py-2 border-0">
                            <span class="status-badge bg-info text-white">
                                <i class="fas fa-spinner fa-spin"></i> En Proceso
                            </span>
                            <span class="text-muted">{{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}</span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title mb-2">{{ $cita->cliente->persona->razon_social }}</h5>
                            <p class="card-text mb-3">
                                <i class="fas fa-phone me-2 text-muted"></i>
                                {{ $cita->cliente->persona->telefono ?? 'Sin teléfono' }}
                            </p>
                            @if($cita->notas)
                            <div class="small text-muted border-top pt-2 mt-2">
                                <i class="fas fa-sticky-note me-1"></i>
                                {{ \Illuminate\Support\Str::limit($cita->notas, 60) }}
                            </div>
                            @endif
                        </div>
                        <div class="card-footer bg-transparent pt-0 border-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <button
                                    type="button"
                                    class="btn btn-success btn-sm btn-action"
                                    :disabled="loading"
                                    @click="cambiarEstado(urls.completar, 'completada')"
                                >
                                    <span x-show="!loading"><i class="fas fa-check"></i> Completar</span>
                                    <span x-show="loading" x-cloak><i class="fas fa-spinner fa-spin"></i></span>
                                </button>
                                <div class="card-actions">
                                    <x-tooltip text="Ver detalle">
                                        <a href="{{ route('citas.show', $cita) }}" class="btn btn-outline-secondary btn-sm btn-action">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </x-tooltip>
                                    <button
                                        type="button"
                                        class="btn btn-outline-danger btn-sm btn-action"
                                        :disabled="loading"
                                        @click="cambiarEstado(urls.cancelar, 'cancelada', '¿Está seguro de cancelar esta cita?')"
                                    >
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-info m-0">
                <i class="fas fa-info-circle me-2"></i>
                No hay citas en proceso actualmente
            </div>
        @endif
    </div>
</div>

<!-- Completed Appointments -->
<div class="card mb-4">
    <div class="section-header bg-light">
        <i class="fas fa-check-circle text-success"></i>
        <h5 class="mb-0">Citas Completadas Hoy</h5>
    </div>
    <div class="card-body">
        @if($citas->where('estado', 'completada')->count() > 0)
            <div class="row g-4">
                @foreach($citas->where('estado', 'completada')->sortBy('posicion_cola') as $cita)
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="card cita-card h-100 border-success position-relative">
                        <div class="position-badge border border-success text-success">{{ $cita->posicion_cola }}</div>
                        <div class="card-header d-flex justify-content-between align-items-center bg-light py-2 border-0">
                            <span class="status-badge bg-success text-white">
                                <i class="fas fa-check-circle"></i> Completada
                            </span>
                            <span class="text-muted">{{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}</span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title mb-2">{{ $cita->cliente->persona->razon_social }}</h5>
                            <p class="card-text mb-0">
                                <i class="fas fa-phone me-2 text-muted"></i>
                                {{ $cita->cliente->persona->telefono ?? 'Sin teléfono' }}
                            </p>
                        </div>
                        <div class="card-footer bg-transparent border-0">
                            <a href="{{ route('citas.show', $cita) }}" class="btn btn-outline-secondary btn-sm btn-action w-100">
                                <i class="fas fa-eye"></i> Ver Detalle
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-info m-0">
                <i class="fas fa-info-circle me-2"></i>
                No hay citas completadas hoy
            </div>
        @endif
    </div>
</div>

<!-- Canceled Appointments -->
<div class="card mb-4">
    <div class="section-header bg-light">
        <i class="fas fa-ban text-danger"></i>
        <h5 class="mb-0">Citas Canceladas <small class="text-muted">(Colapsable)</small></h5>
        <button class="btn btn-link ms-auto p-0 text-muted" type="button" data-bs-toggle="collapse" data-bs-target="#canceledContent">
            <i class="fas fa-chevron-down"></i>
        </button>
    </div>
    <div id="canceledContent" class="collapse">
        <div class="card-body">
            @if($citas->where('estado', 'cancelada')->count() > 0)
                <div class="row g-4">
                    @foreach($citas->where('estado', 'cancelada')->sortBy('posicion_cola') as $cita)
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="card cita-card h-100 border-danger">
                            <div class="card-header d-flex justify-content-between align-items-center bg-light py-2 border-0">
                                <span class="status-badge bg-danger text-white">
                                    <i class="fas fa-ban"></i> Cancelada
                                </span>
                                <span class="text-muted">{{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}</span>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title mb-2">{{ $cita->cliente->persona->razon_social }}</h5>
                                <p class="card-text mb-0">
                                    <i class="fas fa-phone me-2 text-muted"></i>
                                    {{ $cita->cliente->persona->telefono ?? 'Sin teléfono' }}
                                </p>
                            </div>
                            <div class="card-footer bg-transparent border-0">
                                <a href="{{ route('citas.show', $cita) }}" class="btn btn-outline-secondary btn-sm btn-action w-100">
                                    <i class="fas fa-eye"></i> Ver Detalle
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-info m-0">
                    <i class="fas fa-info-circle me-2"></i>
                    No hay citas canceladas hoy
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('js')
@vite([
    'resources/js/modules/CitasDashboard.js',
    'resources/js/modules/CitasDashboardAutoRefresh.js',
])
@endpush
