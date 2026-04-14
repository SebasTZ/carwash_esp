@extends('layouts.app')

@section('title','Editar Tipo de Vehículo')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Editar Tipo de Vehículo</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('tipos_vehiculo.index') }}">Tipos de Vehículo</a></li>
        <li class="breadcrumb-item active">Editar Tipo de Vehículo</li>
    </ol>

    @can('editar-tipo-vehiculo')
    <div class="card text-bg-light">
        <form class="cw-form" action="{{ route('tipos_vehiculo.update', $tipoVehiculo) }}" method="POST" id="tipoVehiculoEditForm">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="nombre" class="form-control" value="{{ $tipoVehiculo->nombre }}" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6">
                        <label for="comision" class="form-label">Comisión <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="comision" id="comision" class="form-control" value="{{ $tipoVehiculo->comision }}" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6">
                        <label for="estado" class="form-label">Estado <span class="text-danger">*</span></label>
                        <select name="estado" id="estado" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <option value="activo" @if($tipoVehiculo->estado=='activo') selected @endif>Activo</option>
                            <option value="inactivo" @if($tipoVehiculo->estado=='inactivo') selected @endif>Inactivo</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="cw-form-actions">
                    <button type="submit" class="btn btn-success">Actualizar tipo de vehículo</button>
                    <a href="{{ route('tipos_vehiculo.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
    @endcan
</div>

@endsection
