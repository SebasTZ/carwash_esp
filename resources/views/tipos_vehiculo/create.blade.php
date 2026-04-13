@extends('layouts.app')

@section('title','Crear Tipo de Vehículo')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Crear Tipo de Vehículo</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('tipos_vehiculo.index') }}">Tipos de Vehículo</a></li>
        <li class="breadcrumb-item active">Crear Tipo de Vehículo</li>
    </ol>

    @can('crear-tipo-vehiculo')
    <div class="card text-bg-light">
        <form class="cw-form" action="{{ route('tipos_vehiculo.store') }}" method="POST" id="tipoVehiculoForm">
            @csrf
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="nombre" class="form-control" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6">
                        <label for="comision" class="form-label">Comisión <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="comision" id="comision" class="form-control" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6">
                        <label for="estado" class="form-label">Estado <span class="text-danger">*</span></label>
                        <select name="estado" id="estado" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="cw-form-actions">
                    <button type="submit" class="btn btn-success">Registrar tipo de vehículo</button>
                    <a href="{{ route('tipos_vehiculo.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
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

        try {
            const validator = new window.CarWash.FormValidator(formElement, {
                rules: {
                    nombre: {
                        required: true,
                        minLength: 2,
                        maxLength: 100,
                    },
                    comision: {
                        required: true,
                        number: true,
                        min: 0,
                        max: 999.99,
                    },
                    estado: {
                        required: true,
                    },
                },
                messages: {
                    nombre: {
                        required: 'El nombre es obligatorio',
                        minLength: 'El nombre debe tener al menos 2 caracteres',
                        maxLength: 'El nombre no puede exceder 100 caracteres',
                    },
                    comision: {
                        required: 'La comisión es obligatoria',
                        number: 'La comisión debe ser un número válido',
                        min: 'La comisión no puede ser negativa',
                        max: 'La comisión no puede exceder 999.99',
                    },
                    estado: {
                        required: 'Debe seleccionar un estado',
                    },
                },
                validateOnBlur: true,
                validateOnInput: false,
                showErrors: true,
                validateOnSubmit: false,
            });

            formElement.addEventListener('submit', (e) => {
                if (!validator.validate()) {
                    e.preventDefault();
                } else {
                    formElement.submit();
                }
            });
        } catch (error) {
            console.error('❌ Error al inicializar FormValidator:', error);
        }
    });
</script>
@endsection
