@extends('layouts.app')

@section('title','Categorías')

@section('content')

@include('layouts.partials.alert')
 
<div class="container-fluid px-4">
    <div class="cw-page-header mt-4">
        <h1 class="cw-page-title">Categorías</h1>
        @can('crear-categoria')
        <div class="cw-page-actions">
            <a href="{{route('categorias.create')}}" class="btn btn-primary">Agregar nueva categoría</a>
        </div>
        @endcan
    </div>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Categorías</li>
    </ol>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Tabla de Categorías
        </div>
        <div class="card-body">
            {{-- Tabla para DynamicTable --}}
            <table id="categorias-table" class="table"></table>

            {{-- Paginación Laravel --}}
            <div class="mt-3">
                <x-pagination-info :paginator="$categorias" entity="categorías" />
            </div>
        </div>
    </div>

</div>

<x-confirm-action-modal
    modal-id="confirmModal"
    title="Mensaje de Confirmación"
    action="#"
    method="DELETE"
    body-id="confirmModalBody"
    form-id="confirmForm"
    method-input-id="confirmMethod"
    confirm-button-id="confirmButton"
    confirm-class="btn btn-danger"
    cancel-text="Cerrar"
>
    ¿Está seguro de que desea realizar esta acción?
</x-confirm-action-modal>

@endsection

@push('js')
@vite('resources/js/app.js')
<script type="application/json" id="categorias-index-config">{!! json_encode([
    'data' => $categorias->items(),
    'canEdit' => auth()->user()->can('editar-categoria'),
    'canDelete' => auth()->user()->can('eliminar-categoria'),
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endpush

