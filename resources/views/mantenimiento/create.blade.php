@extends('layouts.app')

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
                                            @csrf
                                            <div id="form-validator-mantenimiento-create"></div>
                                            <script>
                                            document.addEventListener('DOMContentLoaded', function() {
                                                if (window.FormValidator) {
                                                    window.FormValidator.render({
                                                        target: document.getElementById('form-validator-mantenimiento-create'),
                                                        action: "{{ route('mantenimientos.store') }}",
                                                        method: "POST",
                                                        fields: [
                                                            { name: "cliente_id", label: "Cliente", type: "select", required: true, options: [ { value: "", label: "Seleccione un cliente" }, @foreach($clientes as $cliente) { value: "{{ $cliente->id }}", label: "{{ $cliente->persona->razon_social }} - {{ $cliente->persona->documento->tipo_documento }} {{ $cliente->persona->numero_documento }}" }, @endforeach ], value: "{{ old('cliente_id') }}" },
                                                            { name: "placa", label: "Placa", type: "text", required: true, value: "{{ old('placa') }}", maxlength: 20 },
                                                            { name: "modelo", label: "Modelo", type: "text", required: true, value: "{{ old('modelo') }}", maxlength: 100 },
                                                            { name: "tipo_vehiculo", label: "Tipo de Vehículo", type: "select", required: true, options: [ { value: "", label: "Seleccione" }, { value: "Automóvil", label: "Automóvil" }, { value: "Camioneta", label: "Camioneta" }, { value: "SUV", label: "SUV" }, { value: "Motocicleta", label: "Motocicleta" }, { value: "Camión", label: "Camión" }, { value: "Van", label: "Van" }, { value: "Otro", label: "Otro" } ], value: "{{ old('tipo_vehiculo') }}" },
                                                            { name: "tipo_servicio", label: "Tipo de Servicio", type: "text", required: true, value: "{{ old('tipo_servicio') }}", maxlength: 100 },
                                                            { name: "fecha_ingreso", label: "Fecha de Ingreso", type: "date", required: true, value: "{{ old('fecha_ingreso') ?: date('Y-m-d') }}" },
                                                            { name: "fecha_entrega_estimada", label: "Fecha de Entrega Estimada", type: "date", value: "{{ old('fecha_entrega_estimada') }}" },
                                                            { name: "mecanico_responsable", label: "Mecánico Responsable", type: "text", value: "{{ old('mecanico_responsable') }}", maxlength: 100 },
                                                            { name: "costo_estimado", label: "Costo Estimado (S/)", type: "number", step: "0.01", min: 0, value: "{{ old('costo_estimado') }}" },
                                                            { name: "descripcion_trabajo", label: "Diagnóstico / Trabajo a Realizar", type: "textarea", required: true, value: `{{ old('descripcion_trabajo') }}` },
                                                            { name: "observaciones", label: "Observaciones Adicionales", type: "textarea", value: `{{ old('observaciones') }}` }
                                                        ],
                                                        submit: { label: "Registrar Mantenimiento", class: "btn btn-success" },
                                                        csrf: "{{ csrf_token() }}"
                                                    });
                                                }
                                            });
                                            </script>
                                            <div class="form-group text-right mt-3">
                                                <a href="{{ route('mantenimientos.index') }}" class="btn btn-secondary">Cancelar</a>
                                            </div>
                                        </form>