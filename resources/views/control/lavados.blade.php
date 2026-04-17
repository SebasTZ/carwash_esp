@extends('layouts.app')

@section('title', 'Control de Lavados')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center pb-2 mb-3">
        <h1 class="h2 mb-0">Control de Lavados</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Control de Lavados</li>
            </ol>
        </nav>
    </div>

    <div class="control-card mb-4">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0">
                <i class="fas fa-file-export me-2"></i>
                Exportar Reportes
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <a href="{{ route('control.lavados.export.diario') }}" class="btn btn-success w-100 btn-action">
                        <i class="fas fa-file-excel"></i> Exportar Diario
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('control.lavados.export.semanal') }}" class="btn btn-success w-100 btn-action">
                        <i class="fas fa-file-excel"></i> Exportar Semanal
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('control.lavados.export.mensual') }}" class="btn btn-success w-100 btn-action">
                        <i class="fas fa-file-excel"></i> Exportar Mensual
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
                <label for="filtro_lavador" class="form-label">Filtrar por lavador:</label>
                @php
                $lavadorOptions = $lavadores->map(fn($l) => [
                    'value' => $l->id,
                    'label' => $l->persona->razon_social ?? $l->nombre ?? 'Lavador ' . $l->id,
                    'tokens' => $l->persona->razon_social ?? '',
                ])->values()->toArray();
                @endphp
                <select id="filtro_lavador" name="lavador_id" class="form-select">
                    <option value="">Todos los lavadores</option>
                    @foreach($lavadorOptions as $option)
                        <option value="{{ $option['value'] }}" {{ (string) request('lavador_id') === (string) $option['value'] ? 'selected' : '' }}>
                            {{ $option['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="filtro_estado" class="form-label">Filtrar por estado:</label>
                <select id="filtro_estado" name="estado" class="form-control">
                    <option value="" {{ request('estado') == '' ? 'selected' : '' }}>Todos</option>
                    <option value="En espera" {{ request('estado') == 'En espera' ? 'selected' : '' }}>Pendiente</option>
                    <option value="En proceso" {{ request('estado') == 'En proceso' ? 'selected' : '' }}>En proceso</option>
                    <option value="Terminado" {{ request('estado') == 'Terminado' ? 'selected' : '' }}>Terminado</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="fecha" class="form-label">Filtrar por fecha:</label>
                <input type="date" id="fecha" name="fecha" class="form-control" 
                       value="{{ request('fecha') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100 btn-action">
                    <i class="fas fa-filter me-2"></i>
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <div class="control-card">
        <div class="card-body">
            <x-flash-alert />
            @if(session('confirmar_inicio'))
                <div class="alert alert-warning" role="alert">
                    <form method="POST" action="{{ route('control.lavados.inicioLavado', session('confirmar_inicio')) }}">
                        @csrf
                        <input type="hidden" name="confirmar" value="si">
                        <h5 class="alert-heading"><i class="fas fa-exclamation-circle me-2"></i>Confirmación requerida</h5>
                        <p>¿Está seguro de iniciar el lavado? El lavador asignado recibirá la comisión.</p>
                        <hr>
                        <p class="mb-3">
                            <strong>Lavador:</strong> {{ $lavados->where('id', session('confirmar_inicio'))->first()->lavador->nombre ?? '-' }}
                        </p>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-check me-2"></i>Confirmar inicio
                            </button>
                            <a href="{{ route('control.lavados') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            @endif
            
            <div id="lavados-table-wrapper">
                @include('control.partials.lavados_table', [
                    'lavados' => $lavados,
                    'lavadores' => $lavadores,
                    'tiposVehiculo' => $tiposVehiculo,
                ])
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
@vite([
    'resources/js/modules/LavadosManager.js'
])
@endpush
