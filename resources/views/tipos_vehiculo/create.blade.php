@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Agregar Tipo de Vehículo</h1>
    @can('crear-tipo-vehiculo')
    <form action="{{ route('tipos_vehiculo.store') }}" method="POST" id="tipoVehiculoForm">
        @csrf
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
            <input type="text" name="nombre" id="nombre" class="form-control" required>
            <div class="invalid-feedback"></div>
        </div>
        <div class="mb-3">
            <label for="comision" class="form-label">Comisión <span class="text-danger">*</span></label>
            <input type="number" step="0.01" name="comision" id="comision" class="form-control" required>
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
        <button type="submit" class="btn btn-success">Registrar tipo de vehículo</button>
        <a href="{{ route('tipos_vehiculo.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
    @endcan
</div>

<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof window.CarWash?.FormValidator !== 'function') {
            console.error('FormValidator no está disponible en window.CarWash');
            return;
        }

        const formElement = document.getElementById('tipoVehiculoForm');
        if (!formElement) {
            console.error('Formulario no encontrado');
            return;
        }

        const validationRules = {
            nombre: [
                { type: 'required', message: 'El nombre es obligatorio' },
                { type: 'minLength', value: 2, message: 'El nombre debe tener al menos 2 caracteres' },
                { type: 'maxLength', value: 100, message: 'El nombre no puede exceder 100 caracteres' }
            ],
            comision: [
                { type: 'required', message: 'La comisión es obligatoria' },
                { type: 'number', message: 'La comisión debe ser un número válido' },
                { type: 'min', value: 0, message: 'La comisión no puede ser negativa' },
                { type: 'max', value: 999.99, message: 'La comisión no puede exceder 999.99' }
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
                    console.log('Evento submit disparado');
                    if (!validator.validate()) {
                        e.preventDefault();
                        console.warn('Formulario con errores de validación');
                    } else {
                        console.log('Formulario válido, enviando...');
                        // Ejecutar el submit real
                        formElement.submit();
                    }
                });

            console.log('✅ FormValidator inicializado correctamente para crear TipoVehiculo');
        } catch (error) {
            console.error('❌ Error al inicializar FormValidator:', error);
        }
    });
</script>
@endsection
