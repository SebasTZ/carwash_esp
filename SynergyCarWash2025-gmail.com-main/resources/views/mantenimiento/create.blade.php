@extends('adminlte::page')

@section('title', 'Register Maintenance')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Register New Maintenance</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('panel') }}"><i class="fas fa-home"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('mantenimientos.index') }}">Maintenance</a></li>
                <li class="breadcrumb-item active">Register</li>
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
                    <h3 class="card-title">Maintenance Data</h3>
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

                    <form action="{{ route('mantenimientos.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card card-outline card-primary">
                                    <div class="card-header">
                                        <h3 class="card-title">Client and Vehicle</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="cliente_id">Client <span class="text-danger">*</span></label>
                                            <select name="cliente_id" id="cliente_id" class="form-control select2" required>
                                                <option value="">Select a client</option>
                                                @foreach($clientes as $cliente)
                                                <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                                    {{ $cliente->persona->razon_social }} - {{ $cliente->persona->documento->tipo_documento }} {{ $cliente->persona->numero_documento }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="placa">Plate <span class="text-danger">*</span></label>
                                            <input type="text" name="placa" id="placa" class="form-control" value="{{ old('placa') }}" required placeholder="E.g.: ABC-123" maxlength="20">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="modelo">Model <span class="text-danger">*</span></label>
                                            <input type="text" name="modelo" id="modelo" class="form-control" value="{{ old('modelo') }}" required placeholder="E.g.: Toyota Corolla" maxlength="100">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="tipo_vehiculo">Vehicle Type <span class="text-danger">*</span></label>
                                            <select name="tipo_vehiculo" id="tipo_vehiculo" class="form-control" required>
                                                <option value="">Select</option>
                                                <option value="Automóvil" {{ old('tipo_vehiculo') == 'Automóvil' ? 'selected' : '' }}>Car</option>
                                                <option value="Camioneta" {{ old('tipo_vehiculo') == 'Camioneta' ? 'selected' : '' }}>Pickup</option>
                                                <option value="SUV" {{ old('tipo_vehiculo') == 'SUV' ? 'selected' : '' }}>SUV</option>
                                                <option value="Motocicleta" {{ old('tipo_vehiculo') == 'Motocicleta' ? 'selected' : '' }}>Motorcycle</option>
                                                <option value="Camión" {{ old('tipo_vehiculo') == 'Camión' ? 'selected' : '' }}>Truck</option>
                                                <option value="Van" {{ old('tipo_vehiculo') == 'Van' ? 'selected' : '' }}>Van</option>
                                                <option value="Otro" {{ old('tipo_vehiculo') == 'Otro' ? 'selected' : '' }}>Other</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card card-outline card-primary">
                                    <div class="card-header">
                                        <h3 class="card-title">Service Details</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="tipo_servicio">Service Type <span class="text-danger">*</span></label>
                                            <input type="text" name="tipo_servicio" id="tipo_servicio" class="form-control" value="{{ old('tipo_servicio') }}" required placeholder="E.g.: Oil change, Alignment and balancing, etc." maxlength="100">
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="fecha_ingreso">Entry Date <span class="text-danger">*</span></label>
                                                    <input type="date" name="fecha_ingreso" id="fecha_ingreso" class="form-control" value="{{ old('fecha_ingreso') ?: date('Y-m-d') }}" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="fecha_entrega_estimada">Estimated Delivery Date</label>
                                                    <input type="date" name="fecha_entrega_estimada" id="fecha_entrega_estimada" class="form-control" value="{{ old('fecha_entrega_estimada') }}">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="mecanico_responsable">Responsible Mechanic</label>
                                            <input type="text" name="mecanico_responsable" id="mecanico_responsable" class="form-control" value="{{ old('mecanico_responsable') }}" placeholder="(Optional)" maxlength="100">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="costo_estimado">Estimated Cost (S/)</label>
                                            <input type="number" name="costo_estimado" id="costo_estimado" class="form-control" step="0.01" min="0" value="{{ old('costo_estimado') }}" placeholder="(Optional)">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card card-outline card-primary">
                                    <div class="card-header">
                                        <h3 class="card-title">Work Description</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="descripcion_trabajo">Diagnosis / Work to be Done <span class="text-danger">*</span></label>
                                            <textarea name="descripcion_trabajo" id="descripcion_trabajo" class="form-control" rows="5" required placeholder="Describe the work to be done, initial diagnosis or problems reported by the client">{{ old('descripcion_trabajo') }}</textarea>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="observaciones">Additional Observations</label>
                                            <textarea name="observaciones" id="observaciones" class="form-control" rows="3" placeholder="Additional observations about the vehicle, required parts, etc.">{{ old('observaciones') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group text-right">
                                    <a href="{{ route('mantenimientos.index') }}" class="btn btn-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-success">Register Maintenance</button>
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
            placeholder: 'Select a client',
            allowClear: true
        });
        
        // Convert plate to uppercase automatically
        $('#placa').on('input', function() {
            $(this).val($(this).val().toUpperCase());
        });
        
        // Suggest estimated delivery date (2 days after by default)
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