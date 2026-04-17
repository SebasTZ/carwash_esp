@extends('layouts.app')

@section('title', 'Gestión de Citas')

@section('content')
<div class="container-fluid px-4">
    <div class="cw-page-header mt-4">
        <h1 class="cw-page-title">Gestión de Citas</h1>
        <div class="cw-page-actions">
            <a href="{{ route('citas.create') }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-plus-circle"></i> Nueva Cita
            </a>
            <a href="{{ route('citas.dashboard') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-tachometer-alt"></i> Panel en Tiempo Real
            </a>
        </div>
    </div>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Gestión de Citas</li>
    </ol>

<div class="export-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-file-export me-2"></i>
            Exportar Reportes
        </h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <a href="{{ route('citas.export.diario') }}" class="btn btn-success w-100 btn-export">
                    <i class="fas fa-file-excel"></i>
                    <span>Exportar Diario</span>
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('citas.export.semanal') }}" class="btn btn-success w-100 btn-export">
                    <i class="fas fa-file-excel"></i>
                    <span>Exportar Semanal</span>
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('citas.export.mensual') }}" class="btn btn-success w-100 btn-export">
                    <i class="fas fa-file-excel"></i>
                    <span>Exportar Mensual</span>
                </a>
            </div>
            <div class="col-md-3">
                <form action="{{ route('citas.export.personalizado') }}" method="GET" class="d-flex gap-2">
                    <input type="date" name="fecha_inicio" class="form-control" required placeholder="Fecha inicio">
                    <input type="date" name="fecha_fin" class="form-control" required placeholder="Fecha fin">
                    <button type="submit" class="btn btn-success btn-export shrink-0">
                        <i class="fas fa-file-excel"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="filter-card">
    <form action="{{ route('citas.index') }}" method="GET" class="row g-3">
        <div class="col-md-4">
            <label for="fecha" class="form-label">Fecha</label>
            <input type="date" class="form-control" id="fecha" name="fecha" value="{{ request('fecha', date('Y-m-d')) }}">
        </div>
        <div class="col-md-4">
            <label for="estado" class="form-label">Estado</label>
            <select class="form-control" id="estado" name="estado">
                <option value="">Todos</option>
                <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                <option value="en_proceso" {{ request('estado') == 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                <option value="completada" {{ request('estado') == 'completada' ? 'selected' : '' }}>Completada</option>
                <option value="cancelada" {{ request('estado') == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
            </select>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100 btn-export">
                <i class="fas fa-filter me-2"></i>
                Filtrar
            </button>
        </div>
    </form>
</div>

<x-flash-alert />

<div class="table-responsive">
    <table id="citasTable" class="table table-bordered table-hover"></table>
</div>

@php
    $citasEndpointsConfig = [
        'show' => route('citas.show', ['cita' => '__cita__']),
        'edit' => route('citas.edit', ['cita' => '__cita__']),
        'iniciar' => route('citas.iniciar', ['cita' => '__cita__']),
        'completar' => route('citas.completar', ['cita' => '__cita__']),
        'cancelar' => route('citas.cancelar', ['cita' => '__cita__']),
        'destroy' => route('citas.destroy', ['cita' => '__cita__']),
    ];
@endphp

<script type="application/json" id="citas-table-data">@json($citas->items())</script>
<script type="application/json" id="citas-endpoints-config">@json($citasEndpointsConfig)</script>

<!-- Paginación con preservación de filtros -->
<x-pagination-info :paginator="$citas" entity="citas" :preserve-query="true" />
</div>
@endsection

@push('js')
@vite(['resources/js/modules/CitasIndexManager.js'])
@endpush