@extends('layouts.app')

@section('title','Presentaciones')

@section('content')

@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <div class="cw-page-header mt-4">
        <h1 class="cw-page-title">Presentaciones</h1>
        @can('crear-presentacione')
        <div class="cw-page-actions">
            <a href="{{ route('presentaciones.create') }}" class="btn btn-primary">Agregar nuevo registro</a>
        </div>
        @endcan
    </div>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Presentaciones</li>
    </ol>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Tabla de Presentaciones
        </div>
        <div class="card-body">
            <!-- Tabla dinámica -->
            <table id="presentacionesTable" class="table table-striped"></table>
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
    // Validar que CarWash y DynamicTable existan
    if (!window.CarWash || !window.CarWash.DynamicTable) {
        return;
    }

    const tableElement = document.querySelector('#presentacionesTable');
    if (!tableElement) {
        return;
    }

    // Permisos desde Blade
    const canEdit = {{ auth()->user()->can('editar-presentacione') ? 'true' : 'false' }};
    const canDelete = {{ auth()->user()->can('eliminar-presentacione') ? 'true' : 'false' }};

    // Configurar DynamicTable
    const table = new window.CarWash.DynamicTable('#presentacionesTable', {
        data: @json($presentaciones->items()),
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
                formatter: (value) => {
                    return value == 1
                        ? '<span class="badge rounded-pill text-bg-success">activo</span>'
                        : '<span class="badge rounded-pill text-bg-danger">eliminado</span>';
                }
            }
        ],
        actions: [
            ...(canEdit ? [{
                label: 'Editar',
                class: 'btn-outline-primary',
                icon: 'fa-edit',
                callback: (row) => {
                    window.location.href = `/presentaciones/${row.id}/edit`;
                }
            }] : []),
            ...(canDelete ? [
                {
                    label: 'Eliminar',
                    class: 'btn-outline-danger',
                    icon: 'fa-trash-can',
                    show: (row) => row.caracteristica.estado == 1,
                    callback: (row) => {
                        window.CarWash.openActionModal({
                            modalId: 'deleteModal',
                            title: 'Mensaje de Confirmación',
                            message: '¿Está seguro de que desea eliminar esta presentación?',
                            action: `/presentaciones/${row.id}`,
                            method: 'DELETE',
                            confirmText: 'Eliminar',
                            confirmClass: 'btn btn-danger',
                            bodyId: 'deleteModalBody',
                            formId: 'deleteForm',
                            confirmButtonId: 'confirmButton',
                        });
                    }
                },
                {
                    label: 'Restaurar',
                    class: 'btn-outline-success',
                    icon: 'fa-rotate',
                    show: (row) => row.caracteristica.estado != 1,
                    callback: (row) => {
                        window.CarWash.openActionModal({
                            modalId: 'deleteModal',
                            title: 'Mensaje de Confirmación',
                            message: '¿Está seguro de que desea restaurar esta presentación?',
                            action: `/presentaciones/${row.id}`,
                            method: 'DELETE',
                            confirmText: 'Restaurar',
                            confirmClass: 'btn btn-success',
                            bodyId: 'deleteModalBody',
                            formId: 'deleteForm',
                            confirmButtonId: 'confirmButton',
                        });
                    }
                }
            ] : [])
        ],
        searchable: true,
        searchPlaceholder: 'Buscar presentaciones...',
        language: {
            search: 'Buscar:',
            noData: 'No hay presentaciones registradas',
            noResults: 'No se encontraron presentaciones',
            info: 'Mostrando {start} a {end} de {total} presentaciones',
            infoEmpty: 'Mostrando 0 presentaciones',
            infoFiltered: '(filtrado de {max} presentaciones totales)'
        }
    });

});
</script>
@endpush