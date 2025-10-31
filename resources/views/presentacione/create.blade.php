@extends('layouts.app')

@section('title','Crear Presentación')

@push('css')
<style>
    #descripcion {
        resize: none;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Crear Presentación</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('presentaciones.index')}}">Presentaciones</a></li>
        <li class="breadcrumb-item active">Crear Presentación</li>
    </ol>

    <div class="card">
        <form action="{{ route('presentaciones.store') }}" method="post" id="presentacioneForm">
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

            <div class="card-footer text-center">
                <button type="submit" class="btn btn-primary">Guardar</button>
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

    const formElement = document.querySelector('#presentacioneForm');
    if (!formElement) {
        console.error('Elemento #presentacioneForm no encontrado');
        return;
    }

    // Configurar FormValidator
    const validator = new window.CarWash.FormValidator('#presentacioneForm', {
    validators: {
            nombre: {
                required: { 
                    message: 'El nombre es obligatorio' 
                },
                maxLength: { 
                    value: 60, 
                    message: 'El nombre no puede exceder 60 caracteres' 
                }
            },
            descripcion: {
                maxLength: { 
                    value: 255, 
                    message: 'La descripción no puede exceder 255 caracteres' 
                }
            }
        },
        onSuccess: () => {
        onValid: () => {
            formElement.submit();
        },
        onError: (errors) => {
        }
    });

});
</script>
@endpush