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
        <form id="proveedor-form" class="cw-form" action="{{ route('proveedores.store') }}" method="post" data-validate>
            @csrf
            <div class="card-body">
                <div id="proveedor-form-fields"></div>
            </div>
            <div class="card-footer">
                <div class="cw-form-actions">
                    <button type="submit" class="btn btn-primary">Registrar proveedor</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
@vite(['resources/js/components/forms/FormValidator.js', 'resources/js/components/tables/ProveedorFormManager.js'])
<script type="application/json" id="proveedor-create-config">{!! json_encode([
    'documentos' => $documentos,
    'old' => [
        'tipo_persona' => old('tipo_persona'),
        'razon_social' => old('razon_social'),
        'direccion' => old('direccion'),
        'telefono' => old('telefono'),
        'documento_id' => old('documento_id'),
        'numero_documento' => old('numero_documento'),
    ],
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endpush
