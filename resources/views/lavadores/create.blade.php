@extends('layouts.app')

@vite('resources/js/app.js')
@section('content')
<div class="container">
    <h1>Agregar Lavador</h1>
    @can('crear-lavador')
    <form action="{{ route('lavadores.store') }}" method="POST" id="lavadorForm">
        @csrf
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
            <input type="text" name="nombre" id="nombre" class="form-control" required>
            <div class="invalid-feedback"></div>
        </div>
        <div class="mb-3">
            <label for="dni" class="form-label">DNI <span class="text-danger">*</span></label>
            <input type="text" name="dni" id="dni" class="form-control" required>
            <div class="invalid-feedback"></div>
        </div>
        <div class="mb-3">
            <label for="telefono" class="form-label">Teléfono</label>
            <input type="text" name="telefono" id="telefono" class="form-control">
            <div class="invalid-feedback"></div>
        </div>
        <div class="mb-3">
            <label for="estado" class="form-label">Estado <span class="text-danger">*</span></label>
            <select name="estado" id="estado" class="form-control" required>
                <option value="">Seleccione...</option>
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
            </select>
            <div class="invalid-feedback"></div>
        </div>
        <button type="submit" class="btn btn-success">Guardar</button>
        <a href="{{ route('lavadores.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
    @endcan
</div>

<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
        const formElement = document.getElementById('lavadorForm');
        if (!formElement) {
            console.error('Formulario no encontrado');
            return;
        }
        new window.CarWash.LavadorFormManager(formElement);
        console.log('✅ LavadorFormManager inicializado correctamente para crear Lavador');
    });
</script>
@endsection
