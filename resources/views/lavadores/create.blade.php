@extends('layouts.app')

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
        if (typeof window.CarWash?.FormValidator !== 'function') {
            console.error('FormValidator no está disponible en window.CarWash');
            return;
        }

        const formElement = document.getElementById('lavadorForm');
        if (!formElement) {
            console.error('Formulario no encontrado');
            return;
        }

        const validationRules = {
            nombre: [
                { type: 'required', message: 'El nombre es obligatorio' },
                { type: 'minLength', value: 3, message: 'El nombre debe tener al menos 3 caracteres' },
                { type: 'maxLength', value: 100, message: 'El nombre no puede exceder 100 caracteres' }
            ],
            dni: [
                { type: 'required', message: 'El DNI es obligatorio' },
                { type: 'digits', message: 'El DNI debe contener solo números' },
                { type: 'minLength', value: 8, message: 'El DNI debe tener 8 dígitos' },
                { type: 'maxLength', value: 8, message: 'El DNI debe tener 8 dígitos' }
            ],
            telefono: [
                { type: 'phone', message: 'El teléfono debe tener un formato válido (9 dígitos)' }
            ],
            estado: [
                { type: 'required', message: 'Debe seleccionar un estado' }
            ]
        };

        try {
            const validator = new window.CarWash.FormValidator(formElement, validationRules, {
                validateOnBlur: true,
                validateOnInput: false,
                showErrors: true
            });

            formElement.addEventListener('submit', (e) => {
                if (!validator.validateAll()) {
                    e.preventDefault();
                    console.warn('Formulario con errores de validación');
                }
            });

            console.log('✅ FormValidator inicializado correctamente para crear Lavador');
        } catch (error) {
            console.error('❌ Error al inicializar FormValidator:', error);
        }
    });
</script>
@endsection
