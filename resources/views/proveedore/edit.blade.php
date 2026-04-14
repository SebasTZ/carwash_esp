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
        <form id="proveedor-edit-form" class="cw-form" action="{{ route('proveedores.update',['proveedore'=>$proveedore]) }}" method="post" data-validate>
            @method('PATCH')
            @csrf
            <div class="card-header">
                <p>Tipo de proveedor: <span class="fw-bold">{{ strtoupper($proveedore->persona->tipo_persona)}}</span></p>
            </div>
            <div class="card-body">
                <div id="proveedor-edit-form-fields"></div>
            </div>
            <div class="card-footer">
                <div class="cw-form-actions">
                    <button type="submit" class="btn btn-primary">Actualizar proveedor</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
@vite(['resources/js/components/forms/FormValidator.js', 'resources/js/components/tables/ProveedorFormManager.js'])
<script type="application/json" id="proveedor-edit-config">{!! json_encode([
    'documentos' => $documentos,
    'persona' => $proveedore->persona,
    'old' => [
        'razon_social' => old('razon_social'),
        'direccion' => old('direccion'),
        'telefono' => old('telefono'),
        'documento_id' => old('documento_id'),
        'numero_documento' => old('numero_documento'),
    ],
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endpush
