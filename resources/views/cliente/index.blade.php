@extends('layouts.app')

@section('title','Clients')

@push('css-datatable')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
@endpush

@push('css')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')

@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Clients</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Home</a></li>
        <li class="breadcrumb-item active">Clients</li>
    </ol>

    @can('crear-cliente')
    <div class="mb-4">
        <a href="{{route('clientes.create')}}">
            <button type="button" class="btn btn-primary">Agregar nuevo cliente</button>
        </a>
    </div>
    @endcan

    <div class="card">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Clients Table
        </div>
        <div class="card-body">
            <div id="clientesTable"></div>

            <!-- Paginación usando componente -->
            <x-pagination-info :paginator="$clientes" entity="clientes" />
        </div>
    </div>
</div>
@endsection

@push('js')
<script type="module">
    document.addEventListener('DOMContentLoaded', function() {
        const data = @json($clientes->items());

        new window.CarWash.DynamicTable({
            containerId: 'clientesTable',
            data: data,
            columns: [
                { field: 'persona.razon_social', label: 'Name', sortable: true },
                { field: 'persona.numero_documento', label: 'Document', sortable: true },
                { field: 'persona.telefono', label: 'Phone' },
                { field: 'persona.email', label: 'Email' },
                {
                    field: 'persona.estado',
                    label: 'Status',
                    formatter: (value) => value == 1
                        ? '<span class="badge rounded-pill text-bg-success">active</span>'
                        : '<span class="badge rounded-pill text-bg-danger">deleted</span>'
                },
                {
                    field: 'id',
                    label: 'Actions',
                    formatter: (value) => {
                        const editUrl = `{{ route('clientes.index') }}/${value}/edit`;
                        const deleteUrl = `{{ route('clientes.index') }}/${value}`;
                        return `
                            <a class="btn btn-info btn-sm" href="${editUrl}" title="Edit"><i class="fas fa-edit"></i></a>
                            <form action="${deleteUrl}" method="post" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar este cliente?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        `;
                    }
                }
            ],
            searchable: true,
            searchPlaceholder: 'Buscar por nombre o documento...',
            emptyMessage: 'No hay clientes registrados'
        });
    });
</script>
@endpush