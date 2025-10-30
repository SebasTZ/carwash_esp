@extends('layouts.app')

@section('title','Crear Proveedor')

@push('css')
<style>
    #box-razon-social {
        display: none;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Crear Proveedor</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('proveedores.index')}}">Proveedores</a></li>
        <li class="breadcrumb-item active">Crear Proveedor</li>
    </ol>

    <div class="card text-bg-light">
        <form id="proveedor-form" action="{{ route('proveedores.store') }}" method="post" data-validate>
            @csrf
            <div class="card-body">
                <div id="proveedor-form-fields"></div>
            </div>
            <div class="card-footer text-center">
                <button type="submit" class="btn btn-primary">Registrar proveedor</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
@vite(['resources/js/components/forms/FormValidator.js', 'resources/js/components/tables/ProveedorFormManager.js'])
<script>
    window.documentos = @json($documentos);
    document.addEventListener('DOMContentLoaded', function() {
        if (window.FormValidator) {
            new FormValidator('#proveedor-form');
        }
        if (window.ProveedorFormManager) {
            window.ProveedorFormManager.init({
                el: '#proveedor-form-fields',
                documentos: window.documentos,
                old: {
                    tipo_persona: @json(old('tipo_persona')),
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