@extends('layouts.app')

@section('title', 'Editar Registro de Cochera')

@section('content')
@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <div class="cw-page-header mt-4">
        <h1 class="cw-page-title">Editar Registro de Cochera</h1>
        <div class="cw-page-actions">
            <a href="{{ route('cocheras.show', $cochera->id) }}" class="btn btn-info">
                <i class="fas fa-eye"></i> Ver detalle
            </a>
            <a href="{{ route('cocheras.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('cocheras.index') }}">Cochera</a></li>
        <li class="breadcrumb-item active">Editar</li>
    </ol>

    <div class="card mb-4">
        <form class="cw-form" action="{{ route('cocheras.update', $cochera->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card-body text-bg-light">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="cliente_id" class="form-label">Cliente *</label>
                        <select name="cliente_id" id="cliente_id" class="form-select" required>
                            <option value="">Seleccione un cliente</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}" {{ old('cliente_id', $cochera->cliente_id) == $cliente->id ? 'selected' : '' }}>
                                    {{ $cliente->persona->razon_social }} - {{ $cliente->persona->documento->tipo_documento }} {{ $cliente->persona->numero_documento }}
                                </option>
                            @endforeach
                        </select>
                        @error('cliente_id')<small class="text-danger">{{ '*' . $message }}</small>@enderror
                    </div>

                    <div class="col-md-3">
                        <label for="placa" class="form-label">Placa *</label>
                        <input type="text" name="placa" id="placa" class="form-control" value="{{ old('placa', $cochera->placa) }}" maxlength="20" required>
                        @error('placa')<small class="text-danger">{{ '*' . $message }}</small>@enderror
                    </div>

                    <div class="col-md-3">
                        <label for="tipo_vehiculo" class="form-label">Tipo de vehículo *</label>
                        <select name="tipo_vehiculo" id="tipo_vehiculo" class="form-select" required>
                            <option value="">Seleccione</option>
                            @foreach(['Automóvil','Camioneta','SUV','Motocicleta','Camión','Van','Otro'] as $tipo)
                                <option value="{{ $tipo }}" {{ old('tipo_vehiculo', $cochera->tipo_vehiculo) == $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
                            @endforeach
                        </select>
                        @error('tipo_vehiculo')<small class="text-danger">{{ '*' . $message }}</small>@enderror
                    </div>

                    <div class="col-md-4">
                        <label for="modelo" class="form-label">Modelo *</label>
                        <input type="text" name="modelo" id="modelo" class="form-control" value="{{ old('modelo', $cochera->modelo) }}" maxlength="100" required>
                        @error('modelo')<small class="text-danger">{{ '*' . $message }}</small>@enderror
                    </div>

                    <div class="col-md-4">
                        <label for="color" class="form-label">Color *</label>
                        <input type="text" name="color" id="color" class="form-control" value="{{ old('color', $cochera->color) }}" maxlength="50" required>
                        @error('color')<small class="text-danger">{{ '*' . $message }}</small>@enderror
                    </div>

                    <div class="col-md-4">
                        <label for="ubicacion" class="form-label">Ubicación</label>
                        <input type="text" name="ubicacion" id="ubicacion" class="form-control" value="{{ old('ubicacion', $cochera->ubicacion) }}" maxlength="50">
                        @error('ubicacion')<small class="text-danger">{{ '*' . $message }}</small>@enderror
                    </div>

                    <div class="col-md-3">
                        <label for="fecha_ingreso" class="form-label">Fecha y hora de ingreso *</label>
                        <input type="datetime-local" name="fecha_ingreso" id="fecha_ingreso" class="form-control" value="{{ old('fecha_ingreso', $cochera->fecha_ingreso?->format('Y-m-d\\TH:i')) }}" required>
                        @error('fecha_ingreso')<small class="text-danger">{{ '*' . $message }}</small>@enderror
                    </div>

                    <div class="col-md-3">
                        <label for="fecha_salida" class="form-label">Fecha y hora de salida</label>
                        <input type="datetime-local" name="fecha_salida" id="fecha_salida" class="form-control" value="{{ old('fecha_salida', $cochera->fecha_salida?->format('Y-m-d\\TH:i')) }}">
                        @error('fecha_salida')<small class="text-danger">{{ '*' . $message }}</small>@enderror
                    </div>

                    <div class="col-md-3">
                        <label for="estado" class="form-label">Estado *</label>
                        <select name="estado" id="estado" class="form-select" required>
                            @foreach(['activo' => 'Activo', 'finalizado' => 'Finalizado', 'cancelado' => 'Cancelado'] as $value => $label)
                                <option value="{{ $value }}" {{ old('estado', $cochera->estado) == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('estado')<small class="text-danger">{{ '*' . $message }}</small>@enderror
                    </div>

                    <div class="col-md-3">
                        <label for="monto_total" class="form-label">Monto total (S/)</label>
                        <input type="number" name="monto_total" id="monto_total" class="form-control" step="0.01" min="0" value="{{ old('monto_total', $cochera->monto_total) }}" {{ old('estado', $cochera->estado) === 'activo' ? 'readonly' : '' }}>
                        @error('monto_total')<small class="text-danger">{{ '*' . $message }}</small>@enderror
                    </div>

                    <div class="col-md-4">
                        <label for="tarifa_hora" class="form-label">Tarifa por hora (S/) *</label>
                        <input type="number" name="tarifa_hora" id="tarifa_hora" class="form-control" step="0.01" min="0" value="{{ old('tarifa_hora', $cochera->tarifa_hora) }}" required>
                        @error('tarifa_hora')<small class="text-danger">{{ '*' . $message }}</small>@enderror
                    </div>

                    <div class="col-md-4">
                        <label for="tarifa_dia" class="form-label">Tarifa por día (S/)</label>
                        <input type="number" name="tarifa_dia" id="tarifa_dia" class="form-control" step="0.01" min="0" value="{{ old('tarifa_dia', $cochera->tarifa_dia) }}">
                        @error('tarifa_dia')<small class="text-danger">{{ '*' . $message }}</small>@enderror
                    </div>

                    <div class="col-12">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea name="observaciones" id="observaciones" class="form-control" rows="3">{{ old('observaciones', $cochera->observaciones) }}</textarea>
                        @error('observaciones')<small class="text-danger">{{ '*' . $message }}</small>@enderror
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <div class="cw-form-actions">
                    <a href="{{ route('cocheras.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Actualizar vehículo</button>
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
        const estado = document.getElementById('estado');
        const fechaSalida = document.getElementById('fecha_salida');
        const montoTotal = document.getElementById('monto_total');

        placa?.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });

        estado?.addEventListener('change', function() {
            if (this.value === 'finalizado' && !fechaSalida.value) {
                const now = new Date();
                fechaSalida.value = now.toISOString().slice(0, 16);
            }

            if (montoTotal) {
                montoTotal.readOnly = this.value === 'activo';
            }
        });
    });
</script>
@endpush