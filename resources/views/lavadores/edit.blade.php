@extends('layouts.app')

@vite('resources/js/app.js')
@section('content')
<div class="container">
    <h1>Editar Lavador</h1>
    @can('editar-lavador')
    <form action="{{ route('lavadores.update', ['lavadore' => $lavador->id]) }}" method="POST" id="lavadorEditForm">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
            <input type="text" name="nombre" id="nombre" class="form-control" value="{{ $lavador->nombre }}" required>
            <div class="invalid-feedback"></div>
        </div>
        <div class="mb-3">
            <label for="dni" class="form-label">DNI <span class="text-danger">*</span></label>
            <input type="text" name="dni" id="dni" class="form-control" value="{{ $lavador->dni }}" required>
            <div class="invalid-feedback"></div>
        </div>
        <div class="mb-3">
            <label for="telefono" class="form-label">Teléfono</label>
            <input type="text" name="telefono" id="telefono" class="form-control" value="{{ $lavador->telefono }}">
            <div class="invalid-feedback"></div>
        </div>
        <div class="mb-3">
            <label for="estado" class="form-label">Estado <span class="text-danger">*</span></label>
            <select name="estado" id="estado" class="form-control" required>
                <option value="">Seleccione...</option>
                <option value="activo" @if($lavador->estado=='activo') selected @endif>Activo</option>
                <option value="inactivo" @if($lavador->estado=='inactivo') selected @endif>Inactivo</option>
            </select>
            <div class="invalid-feedback"></div>
        </div>
        <button type="submit" class="btn btn-success">Actualizar</button>
        <a href="{{ route('lavadores.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
    @endcan
</div>

<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
        const formElement = document.getElementById('lavadorEditForm');
        if (!formElement) {
            console.error('Formulario no encontrado');
            return;
        }
        new window.CarWash.LavadorEditFormManager(formElement);
        console.log('✅ LavadorEditFormManager inicializado correctamente para editar Lavador');
    });
</script>
@endsection
