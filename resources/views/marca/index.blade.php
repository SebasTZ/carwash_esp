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
<script>

window.addEventListener('load', () => {
    if (!window.CarWash || !window.CarWash.DynamicTable) {
        console.error('DynamicTable no está disponible');
        return;
    }

    const tableElement = document.querySelector('#marcasTable');
    if (!tableElement) {
        console.error('Elemento #marcasTable no encontrado');
        return;
    }

    const marcasData = @json($marcas->items());
    const canEdit = {{ auth()->user()->can('editar-marca') ? 'true' : 'false' }};
    const canDelete = {{ auth()->user()->can('eliminar-marca') ? 'true' : 'false' }};

    const actions = [];
    if (canEdit) {
        actions.push({
            label: 'Editar',
            class: 'btn-outline-primary',
            icon: 'fa-edit',
            callback: (row) => {
                window.location.href = `/marcas/${row.id}/edit`;
            }
        });
    }
    if (canDelete) {
        actions.push({
            label: 'Eliminar',
            class: 'btn-outline-danger',
            icon: 'fa-trash-can',
            show: (row) => row.caracteristica.estado == 1,
            callback: (row) => {
                window.CarWash.openActionModal({
                    modalId: 'deleteModal',
                    title: 'Mensaje de Confirmación',
                    message: '¿Estás seguro de que deseas eliminar esta marca?',
                    action: `/marcas/${row.id}`,
                    method: 'DELETE',
                    confirmText: 'Eliminar',
                    confirmClass: 'btn btn-danger',
                    bodyId: 'deleteModalBody',
                    formId: 'deleteForm',
                    confirmButtonId: 'confirmButton',
                });
            }
        });
        actions.push({
            label: 'Restaurar',
            class: 'btn-outline-success',
            icon: 'fa-rotate',
            show: (row) => row.caracteristica.estado != 1,
            callback: (row) => {
                window.CarWash.openActionModal({
                    modalId: 'deleteModal',
                    title: 'Mensaje de Confirmación',
                    message: '¿Estás seguro de que deseas restaurar esta marca?',
                    action: `/marcas/${row.id}`,
                    method: 'DELETE',
                    confirmText: 'Restaurar',
                    confirmClass: 'btn btn-success',
                    bodyId: 'deleteModalBody',
                    formId: 'deleteForm',
                    confirmButtonId: 'confirmButton',
                });
            }
        });
    }

    const config = {
        searchable: true,
        searchPlaceholder: 'Buscar marcas...',
        perPage: 15,
        data: marcasData,
        columns: [
            {
                key: 'caracteristica.nombre',
                label: 'Nombre',
                searchable: true
            },
            {
                key: 'caracteristica.descripcion',
                label: 'Descripción',
                searchable: true
            },
            {
                key: 'caracteristica.estado',
                label: 'Estado',
                formatter: (value, row) => {
                    if (value === 1 || value === true || value === "1" || value === "true") {
                        return '<span class="badge rounded-pill text-bg-success">Activo</span>';
                    } else {
                        return '<span class="badge rounded-pill text-bg-secondary">Inactivo</span>';
                    }
                }
            }
        ],
        actions: actions,
        language: {
            search: 'Buscar:',
            noData: 'No hay marcas registradas',
            noResults: 'No se encontraron marcas',
            info: 'Mostrando {start} a {end} de {total} marcas',
            infoEmpty: 'Mostrando 0 marcas',
            infoFiltered: '(filtrado de {max} marcas totales)'
        }
    };

    new window.CarWash.DynamicTable(tableElement, config);
});
</script>
@endpush