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
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Marca</th>
                            <th>Stock</th>
                            <th>Precio Venta</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productos as $producto)
                        <tr>
                            <td><strong>{{ $producto->codigo }}</strong></td>
                            <td>{{ $producto->nombre }}</td>
                            <td>
                                @forelse($producto->categorias as $categoria)
                                    <span class="badge bg-info">{{ $categoria->caracteristica->nombre }}</span>
                                @empty
                                    <span class="text-muted">-</span>
                                @endforelse
                            </td>
                            <td>{{ $producto->marca->caracteristica->nombre ?? '-' }}</td>
                            <td>
                                @php
                                    $stock = $producto->stock ?? 0;
                                @endphp
                                @if($producto->es_servicio_lavado)
                                    <span class="badge bg-primary">Servicio</span>
                                @elseif($stock <= 0)
                                    <span class="badge bg-danger">{{ $stock }}</span>
                                @elseif($stock <= 10)
                                    <span class="badge bg-warning">{{ $stock }}</span>
                                @else
                                    <span class="badge bg-success">{{ $stock }}</span>
                                @endif
                            </td>
                            <td>S/ {{ number_format($producto->precio_venta, 2) }}</td>
                            <td>
                                @if($producto->estado == 1)
                                    <span class="badge rounded-pill text-bg-success">Activo</span>
                                @else
                                    <span class="badge rounded-pill text-bg-danger">Eliminado</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    @can('editar-producto')
                                    <a href="{{ route('productos.edit', $producto->id) }}" class="btn btn-outline-primary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @can('eliminar-producto')
                                    <form method="POST" action="{{ route('productos.destroy', $producto->id) }}" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Eliminar" onclick="return confirm('¿Está seguro?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <p>No hay productos registrados</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <nav aria-label="Page navigation" class="mt-4">
                {{ $productos->links('pagination::bootstrap-4') }}
            </nav>
        </div>
    </div>
</div>
@endsection

@push('js')
@endpush
