@extends('layouts.app')

@section('title', 'Registrar Mantenimiento')

@section('content')
@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <div class="cw-page-header mt-4">
        <h1 class="cw-page-title">Registrar Nuevo Mantenimiento</h1>
        <div class="cw-page-actions">
            <a href="{{ route('mantenimientos.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('mantenimientos.index') }}">Mantenimiento</a></li>
        <li class="breadcrumb-item active">Registrar</li>
    </ol>

    <div class="card mb-4">
        <form class="cw-form" action="{{ route('mantenimientos.store') }}" method="POST">
            @csrf
            <div class="card-body text-bg-light">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="cliente_id" class="form-label">Cliente *</label>
                        <select name="cliente_id" id="cliente_id" class="form-select" required>
                            <option value="">Seleccione un cliente</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                    {{ $cliente->persona->razon_social }} - {{ $cliente->persona->documento->tipo_documento }} {{ $cliente->persona->numero_documento }}
                                </option>
                            @endforeach
                        </select>
                        @error('cliente_id')<small class="text-danger">{{ '*' . $message }}</small>@enderror
                    </div>

                    <div class="col-md-3">
                        <label for="placa" class="form-label">Placa *</label>
                        <input type="text" name="placa" id="placa" class="form-control" value="{{ old('placa') }}" maxlength="20" required>
                        @error('placa')<small class="text-danger">{{ '*' . $message }}</small>@enderror
                    </div>

                    <div class="col-md-3">
                        <label for="tipo_vehiculo" class="form-label">Tipo de vehículo *</label>
                        <select name="tipo_vehiculo" id="tipo_vehiculo" class="form-select" required>
                            <option value="">Seleccione</option>
                            @foreach(['Automóvil','Camioneta','SUV','Motocicleta','Camión','Van','Otro'] as $tipo)
                                <option value="{{ $tipo }}" {{ old('tipo_vehiculo') == $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
                            @endforeach
                        </select>
                        @error('tipo_vehiculo')<small class="text-danger">{{ '*' . $message }}</small>@enderror
                    </div>

                    <div class="col-md-6">
                        <label for="modelo" class="form-label">Modelo *</label>
                        <input type="text" name="modelo" id="modelo" class="form-control" value="{{ old('modelo') }}" maxlength="100" required>
                        @error('modelo')<small class="text-danger">{{ '*' . $message }}</small>@enderror
                    </div>

                    <div class="col-md-6">
                        <label for="tipo_servicio" class="form-label">Tipo de servicio *</label>
                        <input type="text" name="tipo_servicio" id="tipo_servicio" class="form-control" value="{{ old('tipo_servicio') }}" maxlength="100" required>
                        @error('tipo_servicio')<small class="text-danger">{{ '*' . $message }}</small>@enderror
                    </div>

                    <div class="col-md-4">
                        <label for="fecha_ingreso" class="form-label">Fecha de ingreso *</label>
                        <input type="date" name="fecha_ingreso" id="fecha_ingreso" class="form-control" value="{{ old('fecha_ingreso', now()->format('Y-m-d')) }}" required>
                        @error('fecha_ingreso')<small class="text-danger">{{ '*' . $message }}</small>@enderror
                    </div>

                    <div class="col-md-4">
                        <label for="fecha_entrega_estimada" class="form-label">Fecha de entrega estimada</label>
                        <input type="date" name="fecha_entrega_estimada" id="fecha_entrega_estimada" class="form-control" value="{{ old('fecha_entrega_estimada') }}">
                        @error('fecha_entrega_estimada')<small class="text-danger">{{ '*' . $message }}</small>@enderror
                    </div>

                    <div class="col-md-4">
                        <label for="mecanico_responsable" class="form-label">Mecánico responsable</label>
                        <input type="text" name="mecanico_responsable" id="mecanico_responsable" class="form-control" value="{{ old('mecanico_responsable') }}" maxlength="100">
                        @error('mecanico_responsable')<small class="text-danger">{{ '*' . $message }}</small>@enderror
                    </div>

                    <div class="col-md-6">
                        <label for="costo_estimado" class="form-label">Costo estimado (S/)</label>
                        <input type="number" name="costo_estimado" id="costo_estimado" class="form-control" step="0.01" min="0" value="{{ old('costo_estimado') }}">
                        @error('costo_estimado')<small class="text-danger">{{ '*' . $message }}</small>@enderror
                    </div>

                    <div class="col-12">
                        <label for="descripcion_trabajo" class="form-label">Diagnóstico / trabajo a realizar *</label>
                        <textarea name="descripcion_trabajo" id="descripcion_trabajo" class="form-control" rows="4" required>{{ old('descripcion_trabajo') }}</textarea>
                        @error('descripcion_trabajo')<small class="text-danger">{{ '*' . $message }}</small>@enderror
                    </div>

                    <div class="col-12">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea name="observaciones" id="observaciones" class="form-control" rows="3">{{ old('observaciones') }}</textarea>
                        @error('observaciones')<small class="text-danger">{{ '*' . $message }}</small>@enderror
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <div class="cw-form-actions">
                    <a href="{{ route('mantenimientos.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-success">Registrar mantenimiento</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
@endpush