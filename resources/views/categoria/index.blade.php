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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                            const isActive = row.caracteristica.estado === 1;
                            window.CarWash.openActionModal({
                                modalId: 'confirmModal',
                                title: 'Mensaje de Confirmación',
                                message: isActive
                                    ? '¿Está seguro de que desea eliminar esta categoría?'
                                    : '¿Está seguro de que desea restaurar esta categoría?',
                                action: isActive
                                    ? `/categorias/${row.id}`
                                    : `/categorias/${row.id}/restore`,
                                method: isActive ? 'DELETE' : 'PATCH',
                                confirmText: isActive ? 'Eliminar' : 'Restaurar',
                                confirmClass: isActive ? 'btn btn-danger' : 'btn btn-warning',
                                bodyId: 'confirmModalBody',
                                formId: 'confirmForm',
                                methodInputId: 'confirmMethod',
                                confirmButtonId: 'confirmButton',
                            });
                        }
                    }] : [])
                ]
            });

        } catch (error) {
            console.error('❌ Error al inicializar DynamicTable:', error);
            console.error('Stack trace:', error.stack);
        }
    });
</script>
@endpush
