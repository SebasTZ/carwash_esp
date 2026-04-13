@extends('layouts.app')

@section('title','Roles')
@push('css-datatable')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
@endpush

@section('content')

@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <div class="cw-page-header mt-4">
        <h1 class="cw-page-title">Roles</h1>
        @can('crear-role')
        <div class="cw-page-actions">
            <a href="{{ route('roles.create') }}" class="btn btn-primary">Agregar nuevo rol</a>
        </div>
        @endcan
    </div>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Roles</li>
    </ol>

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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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