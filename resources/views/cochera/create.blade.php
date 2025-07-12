@extends('adminlte::page')

@section('title', 'Registrar Vehículo en Cochera')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Registrar Vehículo en Cochera</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('panel') }}"><i class="fas fa-home"></i> Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('cocheras.index') }}">Cochera</a></li>
                <li class="breadcrumb-item active">Registrar</li>
            </ol>
        </div>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información del Vehículo</h3>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form action="{{ route('cocheras.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cliente_id">Cliente <span class="text-danger">*</span></label>
                                    <select name="cliente_id" id="cliente_id" class="form-control select2" required>
                                        <option value="">Seleccione un cliente</option>
                                        @foreach($clientes as $cliente)
                                        <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                            {{ $cliente->persona->razon_social }} - {{ $cliente->persona->documento->tipo_documento }} {{ $cliente->persona->numero_documento }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="placa">Placa <span class="text-danger">*</span></label>
                                    <input type="text" name="placa" id="placa" class="form-control" value="{{ old('placa') }}" required placeholder="Ej: ABC-123" maxlength="20">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="modelo">Modelo <span class="text-danger">*</span></label>
                                    <input type="text" name="modelo" id="modelo" class="form-control" value="{{ old('modelo') }}" required placeholder="Ej: Toyota Corolla" maxlength="100">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="color">Color <span class="text-danger">*</span></label>
                                    <input type="text" name="color" id="color" class="form-control" value="{{ old('color') }}" required placeholder="Ej: Blanco" maxlength="50">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tipo_vehiculo">Tipo de Vehículo <span class="text-danger">*</span></label>
                                    <select name="tipo_vehiculo" id="tipo_vehiculo" class="form-control" required>
                                        <option value="">Seleccione</option>
                                        <option value="Automóvil" {{ old('tipo_vehiculo') == 'Automóvil' ? 'selected' : '' }}>Automóvil</option>
                                        <option value="Camioneta" {{ old('tipo_vehiculo') == 'Camioneta' ? 'selected' : '' }}>Camioneta</option>
                                        <option value="SUV" {{ old('tipo_vehiculo') == 'SUV' ? 'selected' : '' }}>SUV</option>
                                        <option value="Motocicleta" {{ old('tipo_vehiculo') == 'Motocicleta' ? 'selected' : '' }}>Motocicleta</option>
                                        <option value="Camión" {{ old('tipo_vehiculo') == 'Camión' ? 'selected' : '' }}>Camión</option>
                                        <option value="Van" {{ old('tipo_vehiculo') == 'Van' ? 'selected' : '' }}>Van</option>
                                        <option value="Otro" {{ old('tipo_vehiculo') == 'Otro' ? 'selected' : '' }}>Otro</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_ingreso">Fecha y Hora de Ingreso <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="fecha_ingreso" id="fecha_ingreso" class="form-control" value="{{ old('fecha_ingreso') ?: date('Y-m-d\TH:i') }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="ubicacion">Ubicación</label>
                                    <input type="text" name="ubicacion" id="ubicacion" class="form-control" value="{{ old('ubicacion') }}" placeholder="Ej: Zona A-15" maxlength="50">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="tarifa_hora">Tarifa por Hora (S/) <span class="text-danger">*</span></label>
                                    <input type="number" name="tarifa_hora" id="tarifa_hora" class="form-control" step="0.01" min="0" value="{{ old('tarifa_hora') ?: '5.00' }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="tarifa_dia">Tarifa por Día (S/)</label>
                                    <input type="number" name="tarifa_dia" id="tarifa_dia" class="form-control" step="0.01" min="0" value="{{ old('tarifa_dia') ?: '50.00' }}" placeholder="(Opcional)">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="observaciones">Observaciones</label>
                            <textarea name="observaciones" id="observaciones" class="form-control" rows="3" placeholder="Observaciones adicionales sobre el vehículo o la estadía en la cochera">{{ old('observaciones') }}</textarea>
                        </div>

                        <div class="form-group text-right">
                            <a href="{{ route('cocheras.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-success">Registrar Vehículo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css">
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap',
            placeholder: 'Seleccione un cliente',
            allowClear: true
        });
        
        // Convertir placa a mayúsculas automáticamente
        $('#placa').on('input', function() {
            $(this).val($(this).val().toUpperCase());
        });
    });
</script>
@stop