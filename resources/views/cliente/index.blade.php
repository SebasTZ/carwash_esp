@extends('layouts.app')

@section('title','Clientes')

@push('css-datatable')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
@endpush

@section('content')

@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <div class="cw-page-header mt-4">
        <h1 class="cw-page-title">Clientes</h1>
        @can('crear-cliente')
        <div class="cw-page-actions">
            <a href="{{route('clientes.create')}}" class="btn btn-primary">Agregar nuevo cliente</a>
        </div>
        @endcan
    </div>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Clientes</li>
    </ol>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Tabla de Clientes
        </div>
        <div class="card-body">
            <table class="table table-striped fs-6">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Documento</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($clientes as $cliente)
                    <tr>
                        <td>{{ $cliente->persona->razon_social }}</td>
                        <td>{{ $cliente->persona->numero_documento }}</td>
                        <td>{{ $cliente->persona->telefono }}</td>
                        <td>{{ $cliente->persona->email }}</td>
                        <td>
                            @if ($cliente->persona->estado == 1)
                            <span class="badge rounded-pill text-bg-success">activo</span>
                            @else
                            <span class="badge rounded-pill text-bg-danger">eliminado</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-around">
                                <div>
                                    <a class="btn btn-info btn-sm" href="{{route('clientes.edit',['cliente'=>$cliente])}}" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                                <div>
                                    <form action="{{ route('clientes.destroy',['cliente'=>$cliente->id]) }}" method="post">
                                        @method('DELETE')
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm" title="Eliminar" data-confirm="¿Está seguro de eliminar este cliente?" data-confirm-confirm-text="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Paginación usando componente -->
            <x-pagination-info :paginator="$clientes" entity="clientes" />
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- DataTables removido para usar paginación de Laravel -->
@endpush