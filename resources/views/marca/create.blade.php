@extends('layouts.app')

@section('title','Crear Marca')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Crear Marca</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('marcas.index')}}">Marcas</a></li>
        <li class="breadcrumb-item active">Crear Marca</li>
    </ol>

    <div class="card">
        <form class="cw-form" action="{{ route('marcas.store') }}" method="post" id="marcaForm">
            @csrf
            <div class="card-body text-bg-light">

                <div class="row g-4">

                    <div class="col-md-6">
                        <label for="nombre" class="form-label">Nombre:</label>
                        <input type="text" name="nombre" id="nombre" class="form-control" value="{{old('nombre')}}">
                        @error('nombre')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="col-12">
                        <label for="descripcion" class="form-label">Descripción:</label>
                        <textarea name="descripcion" id="descripcion" rows="3" class="form-control">{{old('descripcion')}}</textarea>
                        @error('descripcion')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

            </div>
            <div class="card-footer">
                <div class="cw-form-actions">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('js')
<script>
window.addEventListener('load', () => {
    // Validar que CarWash y FormValidator existan
    if (!window.CarWash || !window.CarWash.FormValidator) {
        console.error('FormValidator no está disponible');
        return;
    }

    const formElement = document.querySelector('#marcaForm');
    if (!formElement) {
        console.error('Elemento #marcaForm no encontrado');
        return;
    }

    // Configurar FormValidator
    const validator = new window.CarWash.FormValidator('#marcaForm', {
        rules: {
            nombre: {
                required: true,
                maxLength: 60,
            },
            descripcion: {
                maxLength: 255,
            },
        },
        messages: {
            nombre: {
                required: 'El nombre es obligatorio',
                maxLength: 'El nombre no puede exceder 60 caracteres',
            },
            descripcion: {
                maxLength: 'La descripción no puede exceder 255 caracteres',
            },
        },
        validateOnSubmit: false,
    });

    formElement.addEventListener('submit', function(e) {
        console.log('Evento submit disparado');
        if (!validator.validate()) {
            e.preventDefault();
            console.warn('Formulario con errores de validación');
        } else {
            console.log('Formulario válido, enviando...');
            formElement.submit();
        }
    });

    console.log('✅ FormValidator de Marca (create) inicializado');
});
</script>
@endpush