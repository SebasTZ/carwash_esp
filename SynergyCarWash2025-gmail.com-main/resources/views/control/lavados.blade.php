@extends('layouts.app')

@section('title', 'Wash Control')

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
<style>
    .control-card {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        margin-bottom: 1.5rem;
    }
    
    .control-card:hover {
        box-shadow: 0 6px 12px rgba(0,0,0,0.12);
        transform: translateY(-3px);
    }

    .control-card .card-header {
        border-bottom: none;
        padding: 1.25rem;
        background: linear-gradient(135deg, #6366F1 0%, #4F46E5 100%);
    }
    
    .control-card .card-header h5 {
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0;
        color: white;
    }
    
    .status-badge {
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .filter-section {
        background: linear-gradient(to right, rgba(13,110,253,0.05), rgba(13,110,253,0.02));
        padding: 1.5rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
    }
    
    .btn-action {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
    }
    
    .btn-action:hover {
        transform: translateY(-2px);
    }

    .time-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        background-color: #f8f9fa;
        border-radius: 15px;
        font-size: 0.85rem;
        color: #6c757d;
        margin-top: 0.5rem;
    }

    .table > :not(caption) > * > * {
        padding: 1rem 0.75rem;
        vertical-align: middle;
    }

    .progress-step {
        position: relative;
    }

    .progress-step::after {
        content: '';
        position: absolute;
        top: 50%;
        right: -1rem;
        width: 2rem;
        height: 2px;
        background-color: #dee2e6;
        transform: translateY(-50%);
    }

    .progress-step:last-child::after {
        display: none;
    }

    .progress-step.completed::after {
        background-color: #198754;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center pb-2 mb-3">
        <h1 class="h2 mb-0">Wash Control</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('panel') }}">Home</a></li>
                <li class="breadcrumb-item active">Wash Control</li>
            </ol>
        </nav>
    </div>

    <div class="control-card mb-4">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0">
                <i class="fas fa-file-export me-2"></i>
                Export Reports
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <a href="{{ route('control.lavados.export.diario') }}" class="btn btn-success w-100 btn-action">
                        <i class="fas fa-file-excel"></i> Export Daily
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('control.lavados.export.semanal') }}" class="btn btn-success w-100 btn-action">
                        <i class="fas fa-file-excel"></i> Export Weekly
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('control.lavados.export.mensual') }}" class="btn btn-success w-100 btn-action">
                        <i class="fas fa-file-excel"></i> Export Monthly
                    </a>
                </div>
                <div class="col-md-3">
                    <form action="{{ route('control.lavados.export.personalizado') }}" method="GET" 
                          class="d-flex gap-2">
                        <input type="date" name="fecha_inicio" class="form-control" required>
                        <input type="date" name="fecha_fin" class="form-control" required>
                        <button type="submit" class="btn btn-success btn-action">
                            <i class="fas fa-file-excel"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="filter-section">
        <form method="GET" action="{{ route('control.lavados') }}" class="row g-3">
            <div class="col-md-3">
                <label for="filtro_lavador" class="form-label">Filter by washer:</label>
                <select id="filtro_lavador" name="lavador_id" class="form-control selectpicker" data-live-search="true">
                    <option value="">All</option>
                    @foreach($lavadores as $lavador)
                        <option value="{{ $lavador->id }}" {{ request('lavador_id') == $lavador->id ? 'selected' : '' }}>{{ $lavador->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="filtro_estado" class="form-label">Filter by status:</label>
                <select id="filtro_estado" name="estado" class="form-control">
                    <option value="" {{ request('estado') == '' ? 'selected' : '' }}>All</option>
                    <option value="En espera" {{ request('estado') == 'En espera' ? 'selected' : '' }}>Pending</option>
                    <option value="En proceso" {{ request('estado') == 'En proceso' ? 'selected' : '' }}>In process</option>
                    <option value="Terminado" {{ request('estado') == 'Terminado' ? 'selected' : '' }}>Finished</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="fecha" class="form-label">Filter by date:</label>
                <input type="date" id="fecha" name="fecha" class="form-control" 
                       value="{{ request('fecha') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100 btn-action">
                    <i class="fas fa-filter me-2"></i>
                    Filter
                </button>
            </div>
        </form>
    </div>

    <div class="control-card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Receipt</th>
                            <th>Client</th>
                            <th>Washer / Vehicle Type</th>
                            <th>Arrival Time</th>
                            <th>Wash Start</th>
                            <th>Wash End</th>
                            <th>Interior Start</th>
                            <th>Interior End</th>
                            <th>Final Hour</th>
                            <th>Total Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lavados as $lavado)
                        <tr>
                            <td>{{ $lavado->venta->numero_comprobante ?? '-' }}</td>
                            <td>{{ $lavado->cliente->persona->razon_social ?? '-' }}</td>
                            <td>
                                @if(!$lavado->lavador_id || !$lavado->tipo_vehiculo_id)
                                    <form method="POST" action="{{ route('control.lavados.asignarLavador', $lavado->id) }}" class="d-flex gap-2 align-items-center">
                                        @csrf
                                        <select name="lavador_id" class="form-control form-control-sm" required>
                                            <option value="">Select washer</option>
                                            @foreach($lavadores as $lavador)
                                                <option value="{{ $lavador->id }}" {{ $lavado->lavador_id == $lavador->id ? 'selected' : '' }}>{{ $lavador->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <select name="tipo_vehiculo_id" class="form-control form-control-sm" required>
                                            <option value="">Select type</option>
                                            @foreach($tiposVehiculo as $tipo)
                                                <option value="{{ $tipo->id }}" {{ $lavado->tipo_vehiculo_id == $tipo->id ? 'selected' : '' }}>{{ $tipo->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-primary btn-sm btn-action">
                                            <i class="fas fa-user-check"></i>
                                        </button>
                                    </form>
                                @else
                                    <span class="badge bg-success">
                                        <i class="fas fa-user me-1"></i>
                                        {{ $lavado->lavador->nombre }}
                                    </span>
                                    <span class="badge bg-info ms-2">
                                        <i class="fas fa-car me-1"></i>
                                        {{ $lavado->tipoVehiculo->nombre ?? '-' }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="time-badge">
                                    {{ $lavado->hora_llegada ? \Carbon\Carbon::parse($lavado->hora_llegada)->format('H:i') : '-' }}
                                </span>
                            </td>
                            <td class="progress-step {{ $lavado->inicio_lavado ? 'completed' : '' }}">
                                <form method="POST" action="{{ route('control.lavados.inicioLavado', $lavado->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $lavado->inicio_lavado ? 'btn-success' : 'btn-outline-success' }} btn-action"
                                            {{ (!$lavado->lavador_id || !$lavado->tipo_vehiculo_id || $lavado->inicio_lavado) ? 'disabled' : '' }}>
                                        <i class="fas fa-play"></i>
                                        Start
                                    </button>
                                </form>
                                @if($lavado->inicio_lavado)
                                    <span class="time-badge mt-1 d-block">
                                        {{ \Carbon\Carbon::parse($lavado->inicio_lavado)->format('H:i') }}
                                    </span>
                                @endif
                            </td>
                            <td class="progress-step {{ $lavado->fin_lavado ? 'completed' : '' }}">
                                <form method="POST" action="{{ route('control.lavados.finLavado', $lavado->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $lavado->fin_lavado ? 'btn-success' : 'btn-outline-success' }} btn-action"
                                            {{ !$lavado->inicio_lavado || $lavado->fin_lavado ? 'disabled' : '' }}>
                                        <i class="fas fa-flag-checkered"></i>
                                        Finish
                                    </button>
                                </form>
                                @if($lavado->fin_lavado)
                                    <span class="time-badge mt-1 d-block">
                                        {{ \Carbon\Carbon::parse($lavado->fin_lavado)->format('H:i') }}
                                    </span>
                                @endif
                            </td>
                            <td class="progress-step {{ $lavado->inicio_interior ? 'completed' : '' }}">
                                <form method="POST" action="{{ route('control.lavados.inicioInterior', $lavado->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $lavado->inicio_interior ? 'btn-info' : 'btn-outline-info' }} btn-action"
                                            {{ !$lavado->fin_lavado || $lavado->inicio_interior ? 'disabled' : '' }}>
                                        <i class="fas fa-car"></i>
                                        Start
                                    </button>
                                </form>
                                @if($lavado->inicio_interior)
                                    <span class="time-badge mt-1 d-block">
                                        {{ \Carbon\Carbon::parse($lavado->inicio_interior)->format('H:i') }}
                                    </span>
                                @endif
                            </td>
                            <td class="progress-step {{ $lavado->fin_interior ? 'completed' : '' }}">
                                <form method="POST" action="{{ route('control.lavados.finInterior', $lavado->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $lavado->fin_interior ? 'btn-info' : 'btn-outline-info' }} btn-action"
                                            {{ !$lavado->inicio_interior || $lavado->fin_interior ? 'disabled' : '' }}>
                                        <i class="fas fa-flag-checkered"></i>
                                        Finish
                                    </button>
                                </form>
                                @if($lavado->fin_interior)
                                    <span class="time-badge mt-1 d-block">
                                        {{ \Carbon\Carbon::parse($lavado->fin_interior)->format('H:i') }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="time-badge">
                                    {{ $lavado->hora_final ? \Carbon\Carbon::parse($lavado->hora_final)->format('H:i') : '-' }}
                                </span>
                            </td>
                            <td>
                                @if($lavado->hora_final && $lavado->hora_llegada)
                                    <span class="badge bg-secondary">
                                        {{ \Carbon\Carbon::parse($lavado->hora_llegada)->diffInMinutes(\Carbon\Carbon::parse($lavado->hora_final)) }} min
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @switch($lavado->estado)
                                    @case('En espera')
                                        <span class="status-badge bg-warning text-dark">Pending</span>
                                        @break
                                    @case('En proceso')
                                        <span class="status-badge bg-primary text-white">In process</span>
                                        @break
                                    @case('Terminado')
                                        <span class="status-badge bg-success text-white">Finished</span>
                                        @break
                                    @default
                                        <span class="status-badge bg-secondary text-white">{{ $lavado->estado }}</span>
                                @endswitch
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('control.lavados.show', $lavado->id) }}" 
                                       class="btn btn-sm btn-success btn-action">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form method="POST" action="#" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger btn-action"
                                                onclick="return confirm('Delete this record?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
<script>
    // Activar tooltips de Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })

    // Función para validar el formulario de asignación
    function checkFormValidity(lavadoId) {
        const form = document.getElementById('form-asignar-' + lavadoId);
        const submitBtn = document.getElementById('btn-submit-' + lavadoId);
        if (!submitBtn) return; // Si no hay botón, significa que ya está asignado o iniciado
        
        const lavadorSelect = form.querySelector('select[name="lavador_id"]');
        const tipoVehiculoSelect = form.querySelector('select[name="tipo_vehiculo_id"]');
        
        // Validar que ambos campos estén seleccionados
        const isValid = lavadorSelect.value !== '' && tipoVehiculoSelect.value !== '';
        
        // Agregar/remover clases de validación
        if (!lavadorSelect.disabled) {
            lavadorSelect.classList.toggle('is-invalid', !lavadorSelect.value);
            lavadorSelect.classList.toggle('is-valid', lavadorSelect.value !== '');
        }
        
        if (!tipoVehiculoSelect.disabled) {
            tipoVehiculoSelect.classList.toggle('is-invalid', !tipoVehiculoSelect.value);
            tipoVehiculoSelect.classList.toggle('is-valid', tipoVehiculoSelect.value !== '');
        }
    }

    function checkFormValidity(lavadoId) {
        const form = document.getElementById('form-asignar-' + lavadoId);
        const btnSubmit = document.getElementById('btn-submit-' + lavadoId);
        if (form.checkValidity()) {
            btnSubmit.removeAttribute('disabled');
        } else {
            btnSubmit.setAttribute('disabled', 'disabled');
        }
    }
</script>
@endpush
