@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Add Vehicle Type</h1>
    @can('crear-tipo-vehiculo')
    <form action="{{ route('tipos_vehiculo.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="nombre" class="form-label">Name</label>
            <input type="text" name="nombre" id="nombre" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="comision" class="form-label">Commission</label>
            <input type="number" step="0.01" name="comision" id="comision" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="estado" class="form-label">Status</label>
            <select name="estado" id="estado" class="form-control">
                <option value="activo">Active</option>
                <option value="inactivo">Inactive</option>
            </select>
        </div>
        <button type="submit" class="btn btn-success">Save</button>
    </form>
    @endcan
</div>
@endsection
