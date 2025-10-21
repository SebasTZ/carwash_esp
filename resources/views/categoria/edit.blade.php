@extends('layouts.app')

@section('title','Editar Categoría')

@push('css')
<style>
    #descripcion {
        resize: none;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Editar Categoría</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('categorias.index')}}">Categorías</a></li>
        <li class="breadcrumb-item active">Editar Categoría</li>
    </ol>

    <div class="card text-bg-light">
        <form action="{{ route('categorias.update',['categoria'=>$categoria]) }}" method="post" id="form-categoria-edit">
            @method('PATCH')
            @csrf
            <div class="card-body">
                <div class="row g-4">

                    <div class="col-md-6">
                        <label for="nombre" class="form-label">Nombre: <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            name="nombre" 
                            id="nombre" 
                            class="form-control @error('nombre') is-invalid @enderror" 
                            value="{{old('nombre',$categoria->caracteristica->nombre)}}"
                            placeholder="Ej: Autos, Camionetas, etc.">
                        @error('nombre')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @else
                        <div class="invalid-feedback"></div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="descripcion" class="form-label">Descripción:</label>
                        <textarea 
                            name="descripcion" 
                            id="descripcion" 
                            rows="3" 
                            class="form-control @error('descripcion') is-invalid @enderror"
                            placeholder="Descripción opcional de la categoría">{{old('descripcion',$categoria->caracteristica->descripcion)}}</textarea>
                        @error('descripcion')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @else
                        <div class="invalid-feedback"></div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Los campos marcados con <span class="text-danger">*</span> son obligatorios
                        </small>
                    </div>

                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Categoría:</strong> {{ $categoria->caracteristica->nombre }} 
                            ({{ $categoria->caracteristica->estado == 1 ? 'Activa' : 'Inactiva' }})
                        </div>
                    </div>
                </div>

            </div>
            <div class="card-footer text-center">
                <button type="submit" class="btn btn-primary" id="btn-submit">
                    <i class="fas fa-save"></i> Actualizar categoría
                </button>
                
                @if($categoria->caracteristica->estado == 0)
                <form action="{{ route('categorias.restore', $categoria->id) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-undo"></i> Restablecer categoría
                    </button>
                </form>
                @else
                <button type="reset" class="btn btn-secondary">
                    <i class="fas fa-eraser"></i> Limpiar cambios
                </button>
                @endif
                
                <a href="{{ route('categorias.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
    
</div>
@endsection

@push('js')
@vite('resources/js/app.js')
<script>
    // Usar FormValidator desde window.CarWash
    const FormValidator = window.CarWash.FormValidator;

    document.addEventListener('DOMContentLoaded', function() {
        const validator = new FormValidator('#form-categoria-edit', {
        rules: {
            nombre: {
                required: true,
                minLength: 3,
                maxLength: 100,
                pattern: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/  // Solo letras, espacios y acentos
            },
            descripcion: {
                required: false,
                maxLength: 500
            }
        },
        messages: {
            nombre: {
                required: 'El nombre de la categoría es obligatorio',
                minLength: 'El nombre debe tener al menos 3 caracteres',
                maxLength: 'El nombre no puede superar 100 caracteres',
                pattern: 'El nombre solo puede contener letras y espacios'
            },
            descripcion: {
                maxLength: 'La descripción no puede superar 500 caracteres'
            }
        },
        validateOnBlur: true,
        validateOnInput: false,
        validateOnSubmit: true,
        scrollToError: true,
        focusOnError: true,
        disableSubmitOnInvalid: false,
        onValid: (form) => {
            console.log('Formulario de edición válido, enviando...');
        },
        onInvalid: (errors) => {
            console.log('Errores de validación:', errors);
            
            if (window.CarWash && window.CarWash.showError) {
                window.CarWash.showError('Por favor, corrija los errores en el formulario');
            }
        },
        onFieldValid: (field, value) => {
            console.log(`Campo ${field} válido:`, value);
        },
        onFieldInvalid: (field, error) => {
            console.log(`Campo ${field} inválido:`, error);
        }
    });

    console.log('FormValidator inicializado en formulario de edición');

    // Prevenir doble submit
    const form = document.getElementById('form-categoria-edit');
    const btnSubmit = document.getElementById('btn-submit');
    let isSubmitting = false;

    form.addEventListener('submit', function(e) {
        if (isSubmitting) {
            e.preventDefault();
            return false;
        }

        if (validator.validate()) {
            isSubmitting = true;
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualizando...';
        }
    });

    // Manejar reset
    form.addEventListener('reset', function() {
        validator.clearErrors();
        console.log('Formulario restablecido');
    });
    });
</script>
@endpush
