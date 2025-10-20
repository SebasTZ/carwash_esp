@extends('layouts.app')

@section('title','Proveedores')

@push('css-datatable')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
@endpush

@push('css')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')

@include('layouts.partials.alert')


<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Proveedores</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Proveedores</li>
    </ol>

    @can('crear-proveedore')
    <div class="mb-4">
        <a href="{{route('proveedores.create')}}">
            <button type="button" class="btn btn-primary">Agregar nuevo proveedor</button>
        </a>
    </div>
    @endcan

    <div class="card">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Tabla de Proveedores
        </div>
        <div class="card-body">
            <table class="table table-striped fs-6">
                <thead>
                    <tr>
                        <th>Proveedor</th>
                        <th>Tipo</th>
                        <th>Dirección</th>
                        <th>Teléfono</th>
                        <th>Documento</th>
                        <th>Número</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($proveedores as $item)
                    <tr>
                        <td>
                            {{$item->persona->razon_social}}
                        </td>
                        <td>
                            {{$item->persona->tipo_persona}}
                        </td>
                        <td>
                            {{$item->persona->direccion}}
                        </td>
                        <td>
                            {{$item->persona->telefono}}
                        </td>
                        <td>
                            <p class="fw-semibold mb-1">{{$item->persona->documento->tipo_documento}}</p>
                            <p class="text-muted mb-0">{{$item->persona->numero_documento}}</p>
                        </td>
                        <td>
                            @if ($item->persona->estado == 1)
                            <span class="badge rounded-pill text-bg-success">activo</span>
                            @else
                            <span class="badge rounded-pill text-bg-danger">eliminado</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-around">

                                <div>
                                    <button title="Options" class="btn btn-datatable btn-icon btn-transparent-dark me-2" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu text-bg-light" style="font-size: small;">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('proveedores.edit', $item->id) }}">Editar</a>
                                        </li>
                                    </ul>
                                </div>
                                <div>
                                    <!----Separador----->
                                    <div class="vr"></div>
                                </div>
                                <div>
                                    <!------Eliminar proveedore---->
                                    @can('eliminar-proveedore')
                                    @if ($item->persona->estado == 1)
                                    <button title="Eliminar" data-bs-toggle="modal" data-bs-target="#confirmModal-{{$item->id}}" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmModal-{{$item->id}}">Eliminar</button>
                                    @else
                                    <button title="Restaurar" data-bs-toggle="modal" data-bs-target="#confirmModal-{{$item->id}}" class="btn btn-datatable btn-icon btn-transparent-dark">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                    @endif
                                    @endcan
                                </div>
                            </div>
                        </td>
                    </tr>

                    <!-- Modal de confirmación-->
                    <div class="modal fade" id="confirmModal-{{$item->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel">Mensaje de Confirmación</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    @if ($item->persona->estado == 1)
                                    ¿Estás seguro de que deseas eliminar este proveedor?
                                    @else
                                    ¿Estás seguro de que deseas restaurar este proveedor?
                                    @endif
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                    <form action="{{ route('proveedores.destroy', $item->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Confirmar</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </tbody>
            </table>

            <!-- Paginación usando componente -->
            <x-pagination-info :paginator="$proveedores" entity="proveedores" />
        </div>
    </div>
</div>
@endsection

@push('js')
<!-- DataTables removido para usar paginación de Laravel -->
@endpush