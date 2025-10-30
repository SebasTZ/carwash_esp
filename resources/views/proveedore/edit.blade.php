@extends('layouts.app')

@section('title','Editar Proveedor')

@push('css')

@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Editar Proveedor</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('proveedores.index')}}">Proveedores</a></li>
        <li class="breadcrumb-item active">Editar Proveedor</li>
    </ol>

    <div class="card text-bg-light">
        <form id="proveedor-edit-form" action="{{ route('proveedores.update',['proveedore'=>$proveedore]) }}" method="post" data-validate>
            @method('PATCH')
            @csrf
            <div class="card-header">
                <p>Tipo de proveedor: <span class="fw-bold">{{ strtoupper($proveedore->persona->tipo_persona)}}</span></p>
            </div>
            <div class="card-body">
                <div id="proveedor-edit-form-fields"></div>
            </div>
            <div class="card-footer text-center">
                <button type="submit" class="btn btn-primary">Actualizar proveedor</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
@vite(['resources/js/components/forms/FormValidator.js', 'resources/js/components/tables/ProveedorFormManager.js'])
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.FormValidator) {
            new FormValidator('#proveedor-edit-form');
        }
        if (window.ProveedorFormManager) {
            window.ProveedorFormManager.init({
                el: '#proveedor-edit-form-fields',
                documentos: @json($documentos),
                persona: @json($proveedore->persona),
                old: {
                    razon_social: @json(old('razon_social')),
                    direccion: @json(old('direccion')),
                    telefono: @json(old('telefono')),
                    documento_id: @json(old('documento_id')),
                    numero_documento: @json(old('numero_documento'))
                }
            });
        }
    });
</script>
@endpush