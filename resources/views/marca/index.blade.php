@extends('layouts.app')

@section('title','Marcas')

@section('content')

@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Marcas</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Marcas</li>
    </ol>

    @can('crear-marca')
    <div class="mb-4">
        <a href="{{ route('marcas.create') }}">
            <button type="button" class="btn btn-primary">Agregar Nuevo Registro</button>
        </a>
    </div>
    @endcan

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
    console.log('Datos de marcas:', marcasData);
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
                confirmAction(row.id, true);
            }
        });
        actions.push({
            label: 'Restaurar',
            class: 'btn-outline-success',
            icon: 'fa-rotate',
            show: (row) => row.caracteristica.estado != 1,
            callback: (row) => {
                confirmAction(row.id, false);
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
                    console.log('Estado recibido:', value, typeof value);
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
    console.log('✅ DynamicTable de Marcas inicializada');
});

// Función global para confirmar acción
function confirmAction(marcaId, isActive) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const modalBody = document.getElementById('deleteModalBody');
    const deleteForm = document.getElementById('deleteForm');
    const confirmButton = document.getElementById('confirmButton');
    
    // Configurar texto del modal
    if (isActive) {
        modalBody.textContent = '¿Estás seguro de que deseas eliminar esta marca?';
        confirmButton.textContent = 'Eliminar';
        confirmButton.className = 'btn btn-danger';
    } else {
        modalBody.textContent = '¿Estás seguro de que deseas restaurar esta marca?';
        confirmButton.textContent = 'Restaurar';
        confirmButton.className = 'btn btn-success';
    }
    
    // Configurar form action
    deleteForm.action = `/marcas/${marcaId}`;
    
    // Mostrar modal
    modal.show();
}
</script>
@endpush