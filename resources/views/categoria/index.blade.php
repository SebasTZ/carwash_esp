@extends('layouts.app')

@section('title','Categorías')

@push('css')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')

@include('layouts.partials.alert')
 
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Categorías</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Categorías</li>
    </ol>

    @can('crear-categoria')
    <div class="mb-4">
        <a href="{{route('categorias.create')}}">
            <button type="button" class="btn btn-primary">Agregar nueva categoría</button>
        </a>
    </div>
    @endcan

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

{{-- Modal único de confirmación --}}
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="confirmModalLabel">Mensaje de Confirmación</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="confirmModalBody">
                ¿Está seguro de que desea realizar esta acción?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <form id="confirmForm" method="post">
                    <input type="hidden" name="_method" id="confirmMethod" value="DELETE">
                    @csrf
                    <button type="submit" class="btn" id="confirmButton">Confirmar</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
@vite('resources/js/app.js')
<script>
    // Esperar a que TODO esté listo (DOM + scripts)
    window.addEventListener('load', function() {
        
        // Verificar que el contenedor existe
        const container = document.getElementById('categorias-table');
        if (!container) {
            console.error('❌ Contenedor #categorias-table no encontrado');
            return;
        }
        
        // Verificar que window.CarWash existe
        if (!window.CarWash || !window.CarWash.DynamicTable) {
            console.error('❌ window.CarWash.DynamicTable no está disponible');
            return;
        }

        const DynamicTable = window.CarWash.DynamicTable;
        
        // Datos desde Laravel
        const categoriasData = @json($categorias->items());
        const canEdit = {{ auth()->user()->can('editar-categoria') ? 'true' : 'false' }};
        const canDelete = {{ auth()->user()->can('eliminar-categoria') ? 'true' : 'false' }};

        // Configurar DynamicTable
        try {
            const tabla = new DynamicTable('#categorias-table', {
                columns: [
                    { 
                        key: 'caracteristica.nombre', 
                        label: 'Nombre' 
                    },
                    { 
                        key: 'caracteristica.descripcion', 
                        label: 'Descripción' 
                    },
                    { 
                        key: 'caracteristica.estado', 
                        label: 'Estado',
                        formatter: 'badge'
                    }
                ],
                customFormatters: {
                    badge: (value) => {
                        if (value === 1) {
                            return '<span class="badge rounded-pill text-bg-success">activo</span>';
                        } else {
                            return '<span class="badge rounded-pill text-bg-danger">eliminado</span>';
                        }
                    }
                },
                data: categoriasData,
                searchable: true,
                searchPlaceholder: 'Buscar categoría...',
                actions: [
                    ...(canEdit ? [{
                        label: 'Editar',
                        class: 'btn-primary',
                        icon: 'fas fa-edit',
                        callback: (row) => {
                            window.location.href = `/categorias/${row.id}/edit`;
                        }
                    }] : []),
                    ...(canDelete ? [{
                        label: 'Acción',
                        class: 'btn-secondary',
                        icon: 'fas fa-ellipsis-v',
                        callback: (row) => {
                            showDeleteModal(row);
                        }
                    }] : [])
                ]
            });

            // Función para mostrar modal de confirmación
            function showDeleteModal(categoria) {
                const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
                const modalBody = document.getElementById('confirmModalBody');
                const confirmForm = document.getElementById('confirmForm');
                const confirmMethod = document.getElementById('confirmMethod');
                const confirmButton = document.getElementById('confirmButton');
                
                // Configurar según estado (activo o eliminado)
                if (categoria.caracteristica.estado === 1) {
                    // Categoría activa -> Eliminar
                    modalBody.textContent = '¿Está seguro de que desea eliminar esta categoría?';
                    confirmForm.action = `/categorias/${categoria.id}`;
                    confirmMethod.value = 'DELETE';
                    confirmButton.className = 'btn btn-danger';
                    confirmButton.innerHTML = '<i class="fas fa-trash"></i> Eliminar';
                } else {
                    // Categoría eliminada -> Restaurar
                    modalBody.textContent = '¿Está seguro de que desea restaurar esta categoría?';
                    confirmForm.action = `/categorias/${categoria.id}/restore`;
                    confirmMethod.value = 'PATCH';
                    confirmButton.className = 'btn btn-warning';
                    confirmButton.innerHTML = '<i class="fas fa-undo"></i> Restaurar';
                }
                
                // Mostrar modal
                modal.show();
            }

        } catch (error) {
            console.error('❌ Error al inicializar DynamicTable:', error);
            console.error('Stack trace:', error.stack);
        }
    });
</script>
@endpush
