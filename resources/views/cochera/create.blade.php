@extends('layouts.app')

@section('title', 'Registrar Vehículo en Cochera')

@section('content')
@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <div class="cw-page-header mt-4">
        <h1 class="cw-page-title">Registrar Vehículo en Cochera</h1>
        <div class="cw-page-actions">
            <a href="{{ route('cocheras.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('cocheras.index') }}">Cochera</a></li>
        <li class="breadcrumb-item active">Registrar</li>
    </ol>

    <div class="card mb-4">
        <form class="cw-form" action="{{ route('cocheras.store') }}" method="POST">
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

                    <div class="col-md-4">
                        <label for="modelo" class="form-label">Modelo *</label>
                        <input type="text" name="modelo" id="modelo" class="form-control" value="{{ old('modelo') }}" maxlength="100" required>
                        @error('modelo')<small class="text-danger">{{ '*' . $message }}</small>@enderror
                    </div>

                    <div class="col-md-4">
                        <label for="color" class="form-label">Color *</label>
                        <input type="text" name="color" id="color" class="form-control" value="{{ old('color') }}" maxlength="50" required>
                        @error('color')<small class="text-danger">{{ '*' . $message }}</small>@enderror
                    </div>

                    <div class="col-md-4">
                        <label for="ubicacion" class="form-label">Ubicación</label>
                        <input type="text" name="ubicacion" id="ubicacion" class="form-control" value="{{ old('ubicacion') }}" maxlength="50">
                        @error('ubicacion')<small class="text-danger">{{ '*' . $message }}</small>@enderror
                    </div>

                    <div class="col-md-4">
                        <label for="fecha_ingreso" class="form-label">Fecha y hora de ingreso *</label>
                        <input type="datetime-local" name="fecha_ingreso" id="fecha_ingreso" class="form-control" value="{{ old('fecha_ingreso', now()->format('Y-m-d\\TH:i')) }}" required>
                        @error('fecha_ingreso')<small class="text-danger">{{ '*' . $message }}</small>@enderror
                    </div>

                    <div class="col-md-4">
                        <label for="tarifa_hora" class="form-label">Tarifa por hora (S/) *</label>
                        <input type="number" name="tarifa_hora" id="tarifa_hora" class="form-control" step="0.01" min="0" value="{{ old('tarifa_hora', '5.00') }}" required>
                        @error('tarifa_hora')<small class="text-danger">{{ '*' . $message }}</small>@enderror
                    </div>

                    <div class="col-md-4">
                        <label for="tarifa_dia" class="form-label">Tarifa por día (S/)</label>
                        <input type="number" name="tarifa_dia" id="tarifa_dia" class="form-control" step="0.01" min="0" value="{{ old('tarifa_dia', '50.00') }}">
                        @error('tarifa_dia')<small class="text-danger">{{ '*' . $message }}</small>@enderror
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
                    <a href="{{ route('cocheras.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-success">Registrar vehículo</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const placa = document.getElementById('placa');
        placa?.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    });
</script>
@endpush