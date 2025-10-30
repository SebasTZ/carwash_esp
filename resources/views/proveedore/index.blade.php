@extends('layouts.app')

@section('title','Proveedores')

@push('css-datatable')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
@endpush

@push('css')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')

@include('layouts.partials.alert')


<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Proveedores</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Proveedores</li>
    </ol>

    @can('crear-proveedore')
    <div class="mb-4">
        <a href="{{route('proveedores.create')}}">
            <button type="button" class="btn btn-primary">Agregar nuevo proveedor</button>
        </a>
    </div>
    @endcan

    <div class="card">
        <pre style="background:#f8f9fa;border:1px solid #ccc;padding:10px;max-height:300px;overflow:auto;">
            {{ json_encode($proveedores->items(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
        </pre>
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
@vite(['resources/js/components/DynamicTable.js', 'resources/js/modules/ProveedorTableManager.js'])
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.DynamicTable && window.ProveedorTableManager) {
            window.ProveedorTableManager.init({
                el: '#proveedores-dynamic-table',
                proveedores: @json($proveedores->items()),
                canEdit: @json(auth()->user()->can('crear-proveedore')),
                canDelete: @json(auth()->user()->can('eliminar-proveedore'))
            });
        }
    });
</script>
@endpush