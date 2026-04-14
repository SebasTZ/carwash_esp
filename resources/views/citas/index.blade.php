@extends('layouts.app')

@section('title', 'Gestión de Citas')

@push('css')
<style>
    .export-card {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        margin-bottom: 1.5rem;
    }
    
    .export-card:hover {
        box-shadow: 0 6px 12px rgba(0,0,0,0.12);
    }
    
    .export-card .card-header {
        background: linear-gradient(135deg, #6366F1 0%, #4F46E5 100%);
        padding: 1rem;
        border: none;
    }
    
    .export-card .card-header h5 {
        color: white;
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0;
    }
    
    .btn-export {
        padding: 0.75rem 1rem;
        border-radius: 10px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        text-decoration: none;
    }
    
    .btn-export:hover {
        transform: translateY(-2px);
    }
    
    .btn-export i {
        font-size: 1.1rem;
    }
    
    .filter-card {
        border-radius: 12px;
        background: linear-gradient(to right, rgba(13,110,253,0.05), rgba(13,110,253,0.02));
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
</style>
@endpush

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

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="table-responsive">
    <table id="citasTable" class="table table-bordered table-hover"></table>
</div>

<script type="application/json" id="citas-table-data">@json($citas->items())</script>

<!-- Paginación con preservación de filtros -->
<x-pagination-info :paginator="$citas" entity="citas" :preserve-query="true" />
</div>
@endsection

@push('js')
@vite(['resources/js/modules/CitasIndexManager.js'])
@endpush