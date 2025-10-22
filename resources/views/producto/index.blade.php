@extends('layouts.app')

@section('title','Productos')

@push('css-datatable')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
@endpush

@push('css')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            <table id="productosTable" class="table table-striped fs-6"></table>

            <!-- Paginación usando componente -->
            <x-pagination-info :paginator="$productos" entity="productos" />
        </div>
    </div>
</div>

<script type="module">
window.addEventListener('load', () => {
    const { DynamicTable } = window.CarWash;

    const columns = [
        { key: 'codigo', label: 'Código' },
        { key: 'nombre', label: 'Nombre' },
        { 
            key: 'categorias', 
            label: 'Categoría',
            formatter: (value) => {
                if (!value || value.length === 0) return '-';
                // Mostrar primera categoría
                return value[0]?.caracteristica?.nombre || '-';
            }
        },
        { 
            key: 'marca.nombre', 
            label: 'Marca',
            formatter: (value) => value || '-'
        },
        { 
            key: 'stock', 
            label: 'Stock',
            formatter: (value) => {
                const stock = parseInt(value) || 0;
                if (stock <= 0) {
                    return `<span class="badge bg-danger">${stock}</span>`;
                } else if (stock <= 10) {
                    return `<span class="badge bg-warning">${stock}</span>`;
                } else {
                    return `<span class="badge bg-success">${stock}</span>`;
                }
            }
        },
        { 
            key: 'precio_venta', 
            label: 'Precio',
            formatter: (value) => {
                const precio = parseFloat(value) || 0;
                return `S/ ${precio.toFixed(2)}`;
            }
        },
        {
            key: 'estado',
            label: 'Estado',
            formatter: (value) => {
                if (value == 1) {
                    return '<span class="badge rounded-pill text-bg-success">activo</span>';
                } else {
                    return '<span class="badge rounded-pill text-bg-danger">eliminado</span>';
                }
            }
        },
        {
            key: 'actions',
            label: 'Acciones',
            formatter: (value, row) => {
                let buttons = '<div class="d-flex justify-content-around">';
                
                buttons += `<a class="btn btn-info btn-sm" href="/productos/${row.id}/edit" title="Editar">
                    <i class="fas fa-edit"></i>
                </a>`;
                
                buttons += `<form action="/productos/${row.id}" method="post" style="display:inline">
                    @csrf
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger btn-sm" title="Eliminar"
                        onclick="return confirm('¿Está seguro de que desea eliminar este producto?')">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>`;
                
                buttons += '</div>';
                return buttons;
            }
        }
    ];

    const data = @json($productos->items());

    new DynamicTable('#productosTable', {
        columns,
        data,
        searchPlaceholder: 'Buscar productos...',
        emptyMessage: 'No hay productos registrados'
    });
});
</script>
@endsection

@push('js')
<!-- DataTables removido para usar paginación de Laravel -->
@endpush
