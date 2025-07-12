@extends('layouts.app')

@section('title', 'Appointment Dashboard')

@push('css')
<style>
    .cita-card {
        transition: all 0.3s ease;
        border: none !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .cita-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .status-badge {
        font-size: 0.9rem;
        padding: 8px 15px;
        border-radius: 20px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .position-badge {
        position: absolute;
        top: -12px;
        right: -12px;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background-color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        font-weight: bold;
        box-shadow: 0 3px 6px rgba(0,0,0,0.1);
        z-index: 1;
    }
    
    .card-actions {
        display: flex;
        gap: 8px;
    }
    
    .refresh-timer {
        font-size: 0.85rem;
        color: #6c757d;
        background: rgba(108, 117, 125, 0.1);
        padding: 6px 12px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    
    .stats-card {
        border-radius: 15px;
        transition: all 0.3s ease;
        overflow: hidden;
    }
    
    .stats-card:hover {
        transform: translateY(-5px) scale(1.02);
    }
    
    .stats-card .card-body {
        padding: 1.5rem;
    }
    
    .stats-icon {
        font-size: 2.5rem;
        opacity: 0.5;
        transition: all 0.3s ease;
    }
    
    .stats-card:hover .stats-icon {
        opacity: 0.8;
        transform: scale(1.1);
    }
    
    .display-5 {
        font-size: 2.5rem;
        font-weight: 600;
    }
    
    .section-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 1rem;
        border-radius: 10px;
        margin-bottom: 1rem;
    }
    
    .section-header i {
        font-size: 1.5rem;
    }
    
    .btn-action {
        padding: 8px 16px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn-action:hover {
        transform: translateY(-2px);
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3">
    <h1 class="h2 mb-0">Appointment Dashboard <small class="text-muted">{{ now()->format('d/m/Y') }}</small></h1>
    <div class="d-flex align-items-center gap-3">
        <span class="refresh-timer">
            <i class="fas fa-sync-alt"></i>
            Refresh in <span id="countdown" class="fw-bold">60</span>s
        </span>
        <div class="btn-group">
            <a href="{{ route('citas.index') }}" class="btn btn-action btn-outline-secondary">
                <i class="fas fa-list"></i> View All
            </a>
            <a href="{{ route('citas.create') }}" class="btn btn-action btn-outline-primary">
                <i class="fas fa-plus-circle"></i> New Appointment
            </a>
            <button type="button" class="btn btn-action btn-outline-success" onclick="window.location.reload()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white stats-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1">Total Appointments</h6>
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
                        <h6 class="card-title mb-1">Pending</h6>
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
                        <h6 class="card-title mb-1">In Process</h6>
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
                        <h6 class="card-title mb-1">Completed</h6>
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
        <h5 class="mb-0">Pending Appointments Queue</h5>
    </div>
    <div class="card-body">
        @if($citas->where('estado', 'pendiente')->count() > 0)
            <div class="row g-4">
                @foreach($citas->where('estado', 'pendiente')->sortBy('posicion_cola') as $cita)
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="card cita-card h-100 border-warning position-relative">
                        <div class="position-badge border border-warning text-warning">{{ $cita->posicion_cola }}</div>
                        <div class="card-header d-flex justify-content-between align-items-center bg-light py-2 border-0">
                            <span class="status-badge bg-warning text-white">
                                <i class="fas fa-clock"></i>
                                Pending
                            </span>
                            <span class="text-muted">{{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}</span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title mb-2">{{ $cita->cliente->persona->razon_social }}</h5>
                            <p class="card-text mb-3">
                                <i class="fas fa-phone me-2 text-muted"></i>
                                {{ $cita->cliente->persona->telefono ?? 'Not registered' }}
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
                                <form action="{{ route('citas.iniciar', $cita) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm btn-action">
                                        <i class="fas fa-play"></i> Start
                                    </button>
                                </form>
                                
                                <div class="card-actions">
                                    <a href="{{ route('citas.show', $cita) }}" class="btn btn-outline-secondary btn-sm btn-action">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form action="{{ route('citas.cancelar', $cita) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-danger btn-sm btn-action" 
                                                onclick="return confirm('Confirm cancellation?')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
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
                There are no pending appointments for today
            </div>
        @endif
    </div>
</div>

<!-- In-Process Appointments -->
<div class="card mb-4">
    <div class="section-header bg-light">
        <i class="fas fa-spinner text-info"></i>
        <h5 class="mb-0">Appointments In Process</h5>
    </div>
    <div class="card-body">
        @if($citas->where('estado', 'en_proceso')->count() > 0)
            <div class="row g-4">
                @foreach($citas->where('estado', 'en_proceso')->sortBy('posicion_cola') as $cita)
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="card cita-card h-100 border-info position-relative">
                        <div class="position-badge border border-info text-info">{{ $cita->posicion_cola }}</div>
                        <div class="card-header d-flex justify-content-between align-items-center bg-light py-2 border-0">
                            <span class="status-badge bg-info text-white">
                                <i class="fas fa-spinner fa-spin"></i>
                                In Process
                            </span>
                            <span class="text-muted">{{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}</span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title mb-2">{{ $cita->cliente->persona->razon_social }}</h5>
                            <p class="card-text mb-3">
                                <i class="fas fa-phone me-2 text-muted"></i>
                                {{ $cita->cliente->persona->telefono ?? 'Not registered' }}
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
                                <form action="{{ route('citas.completar', $cita) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm btn-action">
                                        <i class="fas fa-check"></i> Complete
                                    </button>
                                </form>
                                
                                <div class="card-actions">
                                    <a href="{{ route('citas.show', $cita) }}" class="btn btn-outline-secondary btn-sm btn-action">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form action="{{ route('citas.cancelar', $cita) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-danger btn-sm btn-action"
                                                onclick="return confirm('Confirm cancellation?')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
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
                There are no appointments in process currently
            </div>
        @endif
    </div>
</div>

<!-- Completed Appointments -->
<div class="card mb-4">
    <div class="section-header bg-light">
        <i class="fas fa-check-circle text-success"></i>
        <h5 class="mb-0">Appointments Completed Today</h5>
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
                                <i class="fas fa-check-circle"></i>
                                Completed
                            </span>
                            <span class="text-muted">{{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}</span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title mb-2">{{ $cita->cliente->persona->razon_social }}</h5>
                            <p class="card-text mb-0">
                                <i class="fas fa-phone me-2 text-muted"></i>
                                {{ $cita->cliente->persona->telefono ?? 'Not registered' }}
                            </p>
                        </div>
                        <div class="card-footer bg-transparent border-0">
                            <a href="{{ route('citas.show', $cita) }}" class="btn btn-outline-secondary btn-sm btn-action w-100">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-info m-0">
                <i class="fas fa-info-circle me-2"></i>
                There are no appointments completed today
            </div>
        @endif
    </div>
</div>

<!-- Canceled Appointments -->
<div class="card mb-4">
    <div class="section-header bg-light">
        <i class="fas fa-ban text-danger"></i>
        <h5 class="mb-0">Canceled Appointments <small class="text-muted">(Collapsible)</small></h5>
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
                                    <i class="fas fa-ban"></i>
                                    Canceled
                                </span>
                                <span class="text-muted">{{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}</span>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title mb-2">{{ $cita->cliente->persona->razon_social }}</h5>
                                <p class="card-text mb-0">
                                    <i class="fas fa-phone me-2 text-muted"></i>
                                    {{ $cita->cliente->persona->telefono ?? 'Not registered' }}
                                </p>
                            </div>
                            <div class="card-footer bg-transparent border-0">
                                <a href="{{ route('citas.show', $cita) }}" class="btn btn-outline-secondary btn-sm btn-action w-100">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-info m-0">
                    <i class="fas fa-info-circle me-2"></i>
                    There are no canceled appointments today
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto-refresh dashboard timer
let countdownTime = 60;
const countdownEl = document.getElementById('countdown');

function updateCountdown() {
    countdownEl.textContent = countdownTime;
    
    if (countdownTime <= 0) {
        window.location.reload();
    } else {
        countdownTime--;
        setTimeout(updateCountdown, 1000);
    }
}

// Start countdown
updateCountdown();
</script>
@endpush