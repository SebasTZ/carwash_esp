@extends('layouts.app')

@section('title', 'Editar Mantenimiento')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Editar Mantenimiento</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('panel') }}"><i class="fas fa-home"></i> Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('mantenimientos.index') }}">Mantenimiento</a></li>
                <li class="breadcrumb-item active">Editar</li>
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
                    <h3 class="card-title">Datos de Mantenimiento</h3>
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

                    <form action="{{ route('mantenimientos.update', $mantenimiento->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card card-outline card-primary">
                                    <div class="card-header">
                                        <h3 class="card-title">Cliente y Vehículo</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="cliente_id">Cliente <span class="text-danger">*</span></label>
                                            <select name="cliente_id" id="cliente_id" class="form-control select2" required>
                                                <option value="">Seleccione un cliente</option>
                                                @foreach($clientes as $cliente)
                                                <option value="{{ $cliente->id }}" {{ old('cliente_id', $mantenimiento->cliente_id) == $cliente->id ? 'selected' : '' }}>
                                                    {{ $cliente->persona->razon_social }} - {{ $cliente->persona->documento->tipo_documento }} {{ $cliente->persona->numero_documento }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="placa">Placa <span class="text-danger">*</span></label>
                                            <input type="text" name="placa" id="placa" class="form-control" value="{{ old('placa', $mantenimiento->placa) }}" required placeholder="Ej: ABC-123" maxlength="20">
                                        </div>
                                        <div class="form-group">
                                            <label for="modelo">Modelo <span class="text-danger">*</span></label>
                                            <input type="text" name="modelo" id="modelo" class="form-control" value="{{ old('modelo', $mantenimiento->modelo) }}" required placeholder="Ej: Toyota Corolla" maxlength="100">
                                        </div>
                                        <div class="form-group">
                                            <label for="tipo_vehiculo">Tipo de Vehículo <span class="text-danger">*</span></label>
                                            <select name="tipo_vehiculo" id="tipo_vehiculo" class="form-control" required>
                                                <option value="">Seleccione</option>
                                                <option value="Automóvil" {{ old('tipo_vehiculo', $mantenimiento->tipo_vehiculo) == 'Automóvil' ? 'selected' : '' }}>Automóvil</option>
                                                <option value="Camioneta" {{ old('tipo_vehiculo', $mantenimiento->tipo_vehiculo) == 'Camioneta' ? 'selected' : '' }}>Camioneta</option>
                                                <option value="SUV" {{ old('tipo_vehiculo', $mantenimiento->tipo_vehiculo) == 'SUV' ? 'selected' : '' }}>SUV</option>
                                                <option value="Motocicleta" {{ old('tipo_vehiculo', $mantenimiento->tipo_vehiculo) == 'Motocicleta' ? 'selected' : '' }}>Motocicleta</option>
                                                <option value="Camión" {{ old('tipo_vehiculo', $mantenimiento->tipo_vehiculo) == 'Camión' ? 'selected' : '' }}>Camión</option>
                                                <option value="Van" {{ old('tipo_vehiculo', $mantenimiento->tipo_vehiculo) == 'Van' ? 'selected' : '' }}>Van</option>
                                                <option value="Otro" {{ old('tipo_vehiculo', $mantenimiento->tipo_vehiculo) == 'Otro' ? 'selected' : '' }}>Otro</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card card-outline card-primary">
                                    <div class="card-header">
                                        <h3 class="card-title">Detalles del Servicio</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="tipo_servicio">Tipo de Servicio <span class="text-danger">*</span></label>
                                            <input type="text" name="tipo_servicio" id="tipo_servicio" class="form-control" value="{{ old('tipo_servicio', $mantenimiento->tipo_servicio) }}" required placeholder="Ej: Cambio de aceite, Alineación y balanceo, etc." maxlength="100">
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="fecha_ingreso">Fecha de Ingreso <span class="text-danger">*</span></label>
                                                    <input type="date" name="fecha_ingreso" id="fecha_ingreso" class="form-control" value="{{ old('fecha_ingreso', $mantenimiento->fecha_ingreso ? $mantenimiento->fecha_ingreso->format('Y-m-d') : '') }}" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="fecha_entrega_estimada">Fecha de Entrega Estimada</label>
                                                    <input type="date" name="fecha_entrega_estimada" id="fecha_entrega_estimada" class="form-control" value="{{ old('fecha_entrega_estimada', $mantenimiento->fecha_entrega_estimada ? $mantenimiento->fecha_entrega_estimada->format('Y-m-d') : '') }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="mecanico_responsable">Mecánico Responsable</label>
                                            <input type="text" name="mecanico_responsable" id="mecanico_responsable" class="form-control" value="{{ old('mecanico_responsable', $mantenimiento->mecanico_responsable) }}" placeholder="(Opcional)" maxlength="100">
                                        </div>
                                        <div class="form-group">
                                            <label for="costo_estimado">Costo Estimado (S/)</label>
                                            <input type="number" name="costo_estimado" id="costo_estimado" class="form-control" step="0.01" min="0" value="{{ old('costo_estimado', $mantenimiento->costo_estimado) }}" placeholder="(Opcional)">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card card-outline card-primary">
                                    <div class="card-header">
                                        <h3 class="card-title">Descripción del Trabajo</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="descripcion_trabajo">Diagnóstico / Trabajo a Realizar <span class="text-danger">*</span></label>
                                            <textarea name="descripcion_trabajo" id="descripcion_trabajo" class="form-control" rows="5" required placeholder="Describa el trabajo a realizar, diagnóstico inicial o problemas reportados por el cliente">{{ old('descripcion_trabajo', $mantenimiento->descripcion_trabajo) }}</textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="observaciones">Observaciones Adicionales</label>
                                            <textarea name="observaciones" id="observaciones" class="form-control" rows="3" placeholder="Observaciones adicionales sobre el vehículo, repuestos requeridos, etc.">{{ old('observaciones', $mantenimiento->observaciones) }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group text-right">
                                    <a href="{{ route('mantenimientos.index') }}" class="btn btn-secondary">Cancelar</a>
                                    <button type="submit" class="btn btn-primary">Actualizar Mantenimiento</button>
                                </div>
                            </div>
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
        $('#placa').on('input', function() {
            $(this).val($(this).val().toUpperCase());
        });
        $('#fecha_ingreso').on('change', function() {
            if (!$('#fecha_entrega_estimada').val()) {
                const fechaIngreso = new Date($(this).val());
                fechaIngreso.setDate(fechaIngreso.getDate() + 2);
                const fechaEntrega = fechaIngreso.toISOString().split('T')[0];
                $('#fecha_entrega_estimada').val(fechaEntrega);
            }
        });
    });
</script>
@stop
