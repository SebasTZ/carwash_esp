@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Washer</h1>
    @can('editar-lavador')
    <form action="{{ route('lavadores.update', ['lavadore' => $lavador->id]) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="nombre" class="form-label">Name</label>
            <input type="text" name="nombre" id="nombre" class="form-control" value="{{ $lavador->nombre }}" required>
        </div>
        <div class="mb-3">
            <label for="dni" class="form-label">DNI</label>
            <input type="text" name="dni" id="dni" class="form-control" value="{{ $lavador->dni }}" required>
        </div>
        <div class="mb-3">
            <label for="telefono" class="form-label">Phone</label>
            <input type="text" name="telefono" id="telefono" class="form-control" value="{{ $lavador->telefono }}">
        </div>
        <div class="mb-3">
            <label for="estado" class="form-label">Status</label>
            <select name="estado" id="estado" class="form-control">
                <option value="activo" @if($lavador->estado=='activo') selected @endif>Active</option>
                <option value="inactivo" @if($lavador->estado=='inactivo') selected @endif>Inactive</option>
            </select>
        </div>
        <button type="submit" class="btn btn-success">Update</button>
    </form>
    @endcan
</div>
@endsection
