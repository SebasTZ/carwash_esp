@extends('layouts.app')

@section('title','Editar Presentación')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Editar Presentación</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('presentaciones.index')}}">Presentaciones</a></li>
        <li class="breadcrumb-item active">Editar Presentación</li>
    </ol>

    <div class="card text-bg-light">
        <form class="cw-form" action="{{ route('presentaciones.update',['presentacione'=>$presentacione]) }}" method="post" id="presentacioneForm">
            @method('PATCH')
            @csrf
            <div class="card-body">

                <div class="row g-4">

                    <div class="col-md-6">
                        <label for="nombre" class="form-label">Nombre:</label>
                        <input type="text" name="nombre" id="nombre" class="form-control" value="{{old('nombre',$presentacione->caracteristica->nombre)}}">
                        @error('nombre')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="col-12">
                        <label for="descripcion" class="form-label">Descripción:</label>
                        <textarea name="descripcion" id="descripcion" rows="3" class="form-control">{{old('descripcion',$presentacione->caracteristica->descripcion)}}</textarea>
                        @error('descripcion')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                        <div class="invalid-feedback"></div>
                    </div>

                </div>

            </div>
            <div class="card-footer">
                <div class="cw-form-actions">
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                    <button type="reset" class="btn btn-secondary">Restablecer</button>
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
        return;
    }

    const formElement = document.querySelector('#presentacioneForm');
    if (!formElement) {
       return;
    }

    // Configurar FormValidator (misma config que create)
    const validator = new window.CarWash.FormValidator('#presentacioneForm', {
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
        if (!validator.validate()) {
            e.preventDefault();
        } else {
            formElement.submit();
        }
    });

});
</script>
@endpush