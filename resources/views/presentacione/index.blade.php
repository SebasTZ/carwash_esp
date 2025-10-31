@extends('layouts.app')

@section('title','Presentaciones')

@section('content')

@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Presentaciones</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Presentaciones</li>
    </ol>

    @can('crear-presentacione')
    <div class="mb-4">
        <a href="{{ route('presentaciones.create') }}">
            <button type="button" class="btn btn-primary">Agregar nuevo registro</button>
        </a>
    </div>
    @endcan

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

<!-- Modal dinámico para eliminar/restaurar -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Mensaje de Confirmación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="deleteModalBody">
                <!-- Texto dinámico -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" id="confirmButton">Confirmar</button>
                </form>
            </div>
        </div>
    </div>
</div>

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
                        confirmAction(row.id, true);
                    }
                },
                {
                    label: 'Restaurar',
                    class: 'btn-outline-success',
                    icon: 'fa-rotate',
                    show: (row) => row.caracteristica.estado != 1,
                    callback: (row) => {
                        confirmAction(row.id, false);
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

// Función global para confirmar acción
function confirmAction(presentacioneId, isActive) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const modalBody = document.getElementById('deleteModalBody');
    const deleteForm = document.getElementById('deleteForm');
    const confirmButton = document.getElementById('confirmButton');
    
    // Configurar texto del modal
    if (isActive) {
        modalBody.textContent = '¿Está seguro de que desea eliminar esta presentación?';
        confirmButton.textContent = 'Eliminar';
        confirmButton.className = 'btn btn-danger';
    } else {
        modalBody.textContent = '¿Está seguro de que desea restaurar esta presentación?';
        confirmButton.textContent = 'Restaurar';
        confirmButton.className = 'btn btn-success';
    }
    
    // Configurar form action
    deleteForm.action = `/presentaciones/${presentacioneId}`;
    
    // Mostrar modal
    modal.show();
}
</script>
@endpush