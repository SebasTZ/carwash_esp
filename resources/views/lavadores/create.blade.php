@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Add Washer</h1>
    @can('crear-lavador')
    <form action="{{ route('lavadores.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="nombre" class="form-label">Name</label>
            <input type="text" name="nombre" id="nombre" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="dni" class="form-label">DNI</label>
            <input type="text" name="dni" id="dni" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="telefono" class="form-label">Phone</label>
            <input type="text" name="telefono" id="telefono" class="form-control">
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
