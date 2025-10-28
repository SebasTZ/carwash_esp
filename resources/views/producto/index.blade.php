@extends('layouts.app')

@section('title','Productos')

@push('css')
@endpush

@section('content')

@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Productos</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Productos</li>
    </ol>

    @can('crear-producto')
    <div class="mb-4">
        <a href="{{route('productos.create')}}">
            <button type="button" class="btn btn-primary">Agregar nuevo producto</button>
        </a>
    </div>
    @endcan

    <div class="card">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Tabla de Productos
        </div>
        <div class="card-body">
            <div id="dynamicTableProductos"></div>
            <script type="module">
                import DynamicTable from '/js/components/DynamicTable.js';
                document.addEventListener('DOMContentLoaded', function() {
                    new DynamicTable({
                        elementId: 'dynamicTableProductos',
                        columns: [
                            { key: 'codigo', label: 'Código' },
                            { key: 'nombre', label: 'Nombre' },
                            { key: 'categoria', label: 'Categoría' },
                            { key: 'marca', label: 'Marca' },
                            { key: 'stock', label: 'Stock', render: row => {
                                const stock = parseInt(row.stock) || 0;
                                if (stock <= 0) {
                                    return `<span class='badge bg-danger'>${stock}</span>`;
                                } else if (stock <= 10) {
                                    return `<span class='badge bg-warning'>${stock}</span>`;
                                } else {
                                    return `<span class='badge bg-success'>${stock}</span>`;
                                }
                            } },
                            { key: 'precio_venta', label: 'Precio', render: row => `S/ ${parseFloat(row.precio_venta).toFixed(2)}` },
                            { key: 'estado', label: 'Estado', render: row => row.estado == 1 ? '<span class="badge rounded-pill text-bg-success">activo</span>' : '<span class="badge rounded-pill text-bg-danger">eliminado</span>' },
                            { key: 'acciones', label: 'Acciones', render: row => row.acciones, width: 180 }
                        ],
                        dataUrl: '/api/productos',
                        pagination: true,
                        preserveQuery: true
                    });
                });
            </script>
        </div>
    </div>
</div>
@endsection

@push('js')
<!-- DynamicTable maneja la paginación y acciones -->
@endpush
