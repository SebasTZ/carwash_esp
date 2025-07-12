@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Agregar Tipo de Vehículo</h1>
    @can('crear-tipo-vehiculo')
    <form action="{{ route('tipos_vehiculo.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" name="nombre" id="nombre" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="comision" class="form-label">Comisión</label>
            <input type="number" step="0.01" name="comision" id="comision" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="estado" class="form-label">Estado</label>
            <select name="estado" id="estado" class="form-control">
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
            </select>
        </div>
        <button type="submit" class="btn btn-success">Guardar</button>
    </form>
    @endcan
</div>
@endsection
