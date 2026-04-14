@extends('layouts.app')

@section('title','Marcas')

@section('content')

@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <div class="cw-page-header mt-4">
        <h1 class="cw-page-title">Marcas</h1>
        @can('crear-marca')
        <div class="cw-page-actions">
            <a href="{{ route('marcas.create') }}" class="btn btn-primary">Agregar Nuevo Registro</a>
        </div>
        @endcan
    </div>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Marcas</li>
    </ol>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Tabla de Marcas
        </div>
        <div class="card-body">
            <!-- Tabla dinámica -->
            <table id="marcasTable" class="table table-striped"></table>
        </div>
    </div>
</div>

<x-confirm-action-modal
    modal-id="deleteModal"
    title="Mensaje de Confirmación"
    action="#"
    method="DELETE"
    body-id="deleteModalBody"
    form-id="deleteForm"
    confirm-button-id="confirmButton"
    confirm-class="btn btn-danger"
    cancel-text="Cerrar"
>
    ¿Está seguro de que desea realizar esta acción?
</x-confirm-action-modal>

@endsection

@push('js')
<script type="application/json" id="marcas-index-config">{!! json_encode([
    'data' => $marcas->items(),
    'canEdit' => auth()->user()->can('editar-marca'),
    'canDelete' => auth()->user()->can('eliminar-marca'),
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endpush
