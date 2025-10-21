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
    // Validar que CarWash y DynamicTable existan
    if (!window.CarWash || !window.CarWash.DynamicTable) {
        console.error('DynamicTable no está disponible');
        return;
    }

    const tableElement = document.querySelector('#marcasTable');
    if (!tableElement) {
        console.error('Elemento #marcasTable no encontrado');
        return;
    }

    // Configurar DynamicTable
    const table = new window.CarWash.DynamicTable('#marcasTable', {
        data: @json($marcas->items()),
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
            },
            { 
                key: 'actions', 
                label: 'Acciones',
                formatter: (value, row) => {
                    const canEdit = {{ auth()->user()->can('editar-marca') ? 'true' : 'false' }};
                    const canDelete = {{ auth()->user()->can('eliminar-marca') ? 'true' : 'false' }};
                    const isActive = row.caracteristica.estado == 1;
                    
                    let actions = '<div class="d-flex justify-content-center gap-2">';
                    
                    // Botón Editar
                    if (canEdit) {
                        actions += `
                            <a href="/marcas/${row.id}/edit" class="btn btn-sm btn-outline-primary" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                        `;
                    }
                    
                    // Botón Eliminar/Restaurar
                    if (canDelete) {
                        const btnClass = isActive ? 'btn-outline-danger' : 'btn-outline-success';
                        const icon = isActive ? 'fa-trash-can' : 'fa-rotate';
                        const title = isActive ? 'Eliminar' : 'Restaurar';
                        
                        actions += `
                            <button onclick="confirmAction(${row.id}, ${isActive})" 
                                    class="btn btn-sm ${btnClass}" 
                                    title="${title}">
                                <i class="fas ${icon}"></i>
                            </button>
                        `;
                    }
                    
                    actions += '</div>';
                    return actions;
                }
            }
        ],
        searchable: true,
        searchPlaceholder: 'Buscar marcas...',
        language: {
            search: 'Buscar:',
            noData: 'No hay marcas registradas',
            noResults: 'No se encontraron marcas',
            info: 'Mostrando {start} a {end} de {total} marcas',
            infoEmpty: 'Mostrando 0 marcas',
            infoFiltered: '(filtrado de {max} marcas totales)'
        }
    });

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