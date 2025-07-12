@extends('adminlte::page')

@section('title', 'Edit Garage Record')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Edit Garage Record</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('panel') }}"><i class="fas fa-home"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('cocheras.index') }}">Garage</a></li>
                <li class="breadcrumb-item active">Edit</li>
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
                    <h3 class="card-title">Edit Vehicle Information</h3>
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

                    <form action="{{ route('cocheras.update', $cochera->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cliente_id">Customer <span class="text-danger">*</span></label>
                                    <select name="cliente_id" id="cliente_id" class="form-control select2" required>
                                        <option value="">Select a customer</option>
                                        @foreach($clientes as $cliente)
                                        <option value="{{ $cliente->id }}" {{ old('cliente_id', $cochera->cliente_id) == $cliente->id ? 'selected' : '' }}>
                                            {{ $cliente->persona->razon_social }} - {{ $cliente->persona->documento->tipo_documento }} {{ $cliente->persona->numero_documento }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="placa">License Plate <span class="text-danger">*</span></label>
                                    <input type="text" name="placa" id="placa" class="form-control" value="{{ old('placa', $cochera->placa) }}" required placeholder="Ej: ABC-123" maxlength="20">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="modelo">Model <span class="text-danger">*</span></label>
                                    <input type="text" name="modelo" id="modelo" class="form-control" value="{{ old('modelo', $cochera->modelo) }}" required placeholder="Ej: Toyota Corolla" maxlength="100">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="color">Color <span class="text-danger">*</span></label>
                                    <input type="text" name="color" id="color" class="form-control" value="{{ old('color', $cochera->color) }}" required placeholder="Ej: Blanco" maxlength="50">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tipo_vehiculo">Vehicle Type <span class="text-danger">*</span></label>
                                    <select name="tipo_vehiculo" id="tipo_vehiculo" class="form-control" required>
                                        <option value="">Seleccione</option>
                                        <option value="Automóvil" {{ old('tipo_vehiculo', $cochera->tipo_vehiculo) == 'Automóvil' ? 'selected' : '' }}>Automóvil</option>
                                        <option value="Camioneta" {{ old('tipo_vehiculo', $cochera->tipo_vehiculo) == 'Camioneta' ? 'selected' : '' }}>Camioneta</option>
                                        <option value="SUV" {{ old('tipo_vehiculo', $cochera->tipo_vehiculo) == 'SUV' ? 'selected' : '' }}>SUV</option>
                                        <option value="Motocicleta" {{ old('tipo_vehiculo', $cochera->tipo_vehiculo) == 'Motocicleta' ? 'selected' : '' }}>Motocicleta</option>
                                        <option value="Camión" {{ old('tipo_vehiculo', $cochera->tipo_vehiculo) == 'Camión' ? 'selected' : '' }}>Camión</option>
                                        <option value="Van" {{ old('tipo_vehiculo', $cochera->tipo_vehiculo) == 'Van' ? 'selected' : '' }}>Van</option>
                                        <option value="Otro" {{ old('tipo_vehiculo', $cochera->tipo_vehiculo) == 'Otro' ? 'selected' : '' }}>Otro</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_ingreso">Check-in Date and Time <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="fecha_ingreso" id="fecha_ingreso" class="form-control" value="{{ old('fecha_ingreso', $cochera->fecha_ingreso->format('Y-m-d\TH:i')) }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_salida">Check-out Date and Time</label>
                                    <input type="datetime-local" name="fecha_salida" id="fecha_salida" class="form-control" value="{{ old('fecha_salida', $cochera->fecha_salida ? \Carbon\Carbon::parse($cochera->fecha_salida)->format('Y-m-d\TH:i') : '') }}">
                                    <small class="form-text text-muted">Leave blank if not yet checked out</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="ubicacion">Location</label>
                                    <input type="text" name="ubicacion" id="ubicacion" class="form-control" value="{{ old('ubicacion', $cochera->ubicacion) }}" placeholder="Ej: Zona A-15" maxlength="50">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="estado">Status <span class="text-danger">*</span></label>
                                    <select name="estado" id="estado" class="form-control" required>
                                        <option value="activo" {{ old('estado', $cochera->estado) == 'activo' ? 'selected' : '' }}>Active</option>
                                        <option value="finalizado" {{ old('estado', $cochera->estado) == 'finalizado' ? 'selected' : '' }}>Checked Out</option>
                                        <option value="cancelado" {{ old('estado', $cochera->estado) == 'cancelado' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tarifa_hora">Hourly Rate (S/) <span class="text-danger">*</span></label>
                                    <input type="number" name="tarifa_hora" id="tarifa_hora" class="form-control" step="0.01" min="0" value="{{ old('tarifa_hora', $cochera->tarifa_hora) }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tarifa_dia">Daily Rate (S/)</label>
                                    <input type="number" name="tarifa_dia" id="tarifa_dia" class="form-control" step="0.01" min="0" value="{{ old('tarifa_dia', $cochera->tarifa_dia) }}" placeholder="(Optional)">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="monto_total">Total Amount (S/)</label>
                                    <input type="number" name="monto_total" id="monto_total" class="form-control" step="0.01" min="0" value="{{ old('monto_total', $cochera->monto_total) }}" {{ $cochera->estado == 'activo' ? 'readonly' : '' }}>
                                    @if($cochera->estado == 'activo')
                                    <small class="form-text text-muted">The amount is automatically calculated upon checkout</small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="observaciones">Observations</label>
                            <textarea name="observaciones" id="observaciones" class="form-control" rows="3" placeholder="Additional observations about the vehicle or stay in the garage">{{ old('observaciones', $cochera->observaciones) }}</textarea>
                        </div>

                        <div class="form-group text-right">
                            <a href="{{ route('cocheras.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Record</button>
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
        
        // Verificar estado y fecha de salida
        $('#estado').on('change', function() {
            if ($(this).val() == 'finalizado' && !$('#fecha_salida').val()) {
                $('#fecha_salida').val('{{ now()->format('Y-m-d\TH:i') }}');
            }
        });
        
        // Si se cambia fecha de salida y hay estado activo, advertir
        $('#fecha_salida').on('change', function() {
            if ($(this).val() && $('#estado').val() == 'activo') {
                alert('Aviso: Ha establecido una fecha de salida pero el estado sigue siendo Activo');
            }
        });
    });
</script>
@stop