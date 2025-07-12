@extends('layouts.app')

@section('title', 'Appointment Management')

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
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Appointment Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group mr-2">
            <a href="{{ route('citas.create') }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-plus-circle"></i> New Appointment
            </a>
            <a href="{{ route('citas.dashboard') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-tachometer-alt"></i> Real-Time Dashboard
            </a>
        </div>
    </div>
</div>

<div class="export-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-file-export me-2"></i>
            Export Reports
        </h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <a href="{{ route('citas.export.diario') }}" class="btn btn-success w-100 btn-export">
                    <i class="fas fa-file-excel"></i>
                    <span>Export Daily</span>
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('citas.export.semanal') }}" class="btn btn-success w-100 btn-export">
                    <i class="fas fa-file-excel"></i>
                    <span>Export Weekly</span>
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('citas.export.mensual') }}" class="btn btn-success w-100 btn-export">
                    <i class="fas fa-file-excel"></i>
                    <span>Export Monthly</span>
                </a>
            </div>
            <div class="col-md-3">
                <form action="{{ route('citas.export.personalizado') }}" method="GET" class="d-flex gap-2">
                    <input type="date" name="fecha_inicio" class="form-control" required placeholder="Start date">
                    <input type="date" name="fecha_fin" class="form-control" required placeholder="End date">
                    <button type="submit" class="btn btn-success btn-export flex-shrink-0">
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
            <label for="fecha" class="form-label">Date</label>
            <input type="date" class="form-control" id="fecha" name="fecha" value="{{ request('fecha', date('Y-m-d')) }}">
        </div>
        <div class="col-md-4">
            <label for="estado" class="form-label">Status</label>
            <select class="form-control" id="estado" name="estado">
                <option value="">All</option>
                <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pending</option>
                <option value="en_proceso" {{ request('estado') == 'en_proceso' ? 'selected' : '' }}>In Process</option>
                <option value="completada" {{ request('estado') == 'completada' ? 'selected' : '' }}>Completed</option>
                <option value="cancelada" {{ request('estado') == 'cancelada' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100 btn-export">
                <i class="fas fa-filter me-2"></i>
                Filter
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
    <table class="table table-bordered table-hover">
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Client</th>
                <th>Date</th>
                <th>Time</th>
                <th>Position</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($citas as $cita)
            <tr>
                <td>{{ $cita->id }}</td>
                <td>{{ $cita->cliente->persona->razon_social }} - {{ $cita->cliente->persona->numero_documento }}</td>
                <td>{{ $cita->fecha->format('d/m/Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}</td>
                <td><span class="badge bg-info">{{ $cita->posicion_cola }}</span></td>
                <td>
                    @switch($cita->estado)
                        @case('pendiente')
                            <span class="badge bg-warning">Pending</span>
                            @break
                        @case('en_proceso')
                            <span class="badge bg-primary">In Process</span>
                            @break
                        @case('completada')
                            <span class="badge bg-success">Completed</span>
                            @break
                        @case('cancelada')
                            <span class="badge bg-danger">Cancelled</span>
                            @break
                    @endswitch
                </td>
                <td>
                    <div class="btn-group" role="group">
                        <a href="{{ route('citas.show', $cita) }}" class="btn btn-info btn-sm" title="View details">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if($cita->estado != 'completada' && $cita->estado != 'cancelada')
                        <a href="{{ route('citas.edit', $cita) }}" class="btn btn-primary btn-sm" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        @endif

                        @if($cita->estado == 'pendiente')
                        <form action="{{ route('citas.iniciar', $cita) }}" method="POST" style="display:inline">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm" title="Start Appointment">
                                <i class="fas fa-play"></i>
                            </button>
                        </form>
                        @endif

                        @if($cita->estado == 'en_proceso')
                        <form action="{{ route('citas.completar', $cita) }}" method="POST" style="display:inline">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm" title="Complete Appointment">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                        @endif

                        @if($cita->estado != 'completada' && $cita->estado != 'cancelada')
                        <form action="{{ route('citas.cancelar', $cita) }}" method="POST" style="display:inline">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm" title="Cancel Appointment"
                                onclick="return confirm('Are you sure you want to cancel this appointment?')">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                        @endif

                        <form action="{{ route('citas.destroy', $cita) }}" method="POST" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" title="Delete"
                                onclick="return confirm('Are you sure you want to delete this appointment?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">No appointments registered</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection