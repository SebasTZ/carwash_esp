@extends('layouts.app')

@section('title','Proveedores')

@section('content')

@include('layouts.partials.alert')


<div class="container-fluid px-4">
    <div class="cw-page-header mt-4">
        <h1 class="cw-page-title">Proveedores</h1>
        @can('crear-proveedore')
        <div class="cw-page-actions">
            <a href="{{ route('proveedores.create') }}" class="btn btn-primary">Agregar nuevo proveedor</a>
        </div>
        @endcan
    </div>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Proveedores</li>
    </ol>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Tabla de Proveedores
        </div>
        <div class="card-body">
            <div id="proveedores-dynamic-table"></div>
        </div>
    </div>
</div>
@endsection

@push('js')
@vite(['resources/js/components/tables/ProveedorTableManager.js'])
<script type="application/json" id="proveedores-index-config">{!! json_encode([
    'proveedores' => $proveedores->items(),
    'canEdit' => auth()->user()->can('editar-proveedore'),
    'canDelete' => auth()->user()->can('eliminar-proveedore'),
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endpush
