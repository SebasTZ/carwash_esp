@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Tipo de Vehículo</h1>
    @can('editar-tipo-vehiculo')
    <form action="{{ route('tipos_vehiculo.update', $tipoVehiculo) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" name="nombre" id="nombre" class="form-control" value="{{ $tipoVehiculo->nombre }}" required>
        </div>
        <div class="mb-3">
            <label for="comision" class="form-label">Comisión</label>
            <input type="number" step="0.01" name="comision" id="comision" class="form-control" value="{{ $tipoVehiculo->comision }}" required>
        </div>
        <div class="mb-3">
            <label for="estado" class="form-label">Estado</label>
            <select name="estado" id="estado" class="form-control">
                <option value="activo" @if($tipoVehiculo->estado=='activo') selected @endif>Activo</option>
                <option value="inactivo" @if($tipoVehiculo->estado=='inactivo') selected @endif>Inactivo</option>
            </select>
        </div>
        <button type="submit" class="btn btn-success">Actualizar tipo de vehículo</button>
    </form>
    @endcan
</div>
@endsection
