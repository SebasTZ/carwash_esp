@extends('layouts.app')

@section('title','Roles')
@push('css-datatable')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
@endpush
@push('css')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')

@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Roles</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Roles</li>
    </ol>

    @can('crear-role')
    <div class="mb-4">
        <a href="{{route('roles.create')}}">
            <button type="button" class="btn btn-primary">Agregar nuevo rol</button>
        </a>
    </div>
    @endcan

    <div class="card">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Tabla de Roles
        </div>
        <div class="card-body">
            <div id="roles-dynamic-table"></div>
        </div>
    </div>


</div>

@endsection

@push('js')
<script>
    window.Laravel = window.Laravel || {};
    window.Laravel.csrfToken = '{{ csrf_token() }}';
</script>
@vite(['resources/js/components/tables/RoleTableManager.js'])
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.RoleTableManager) {
            window.RoleTableManager.init({
                el: '#roles-dynamic-table',
                roles: @json($roles),
                canEdit: @json(auth()->user()->can('editar-role')),
                canDelete: @json(auth()->user()->can('eliminar-role'))
            });
        }
    });
</script>
@endpush