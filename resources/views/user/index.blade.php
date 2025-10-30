@extends('layouts.app')

@section('title','Usuarios')

@push('css-datatable')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
@endpush

@push('css')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')

@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Usuarios</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Usuarios</li>
    </ol>

    @can('crear-user')
    <div class="mb-4">
        <a href="{{route('users.create')}}">
            <button type="button" class="btn btn-primary">Agregar nuevo usuario</button>
        </a>
    </div>
    @endcan



    <div class="card">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Tabla de Usuarios
        </div>
        <div class="card-body">
            <!-- Contenedor dinámico -->
            <div id="users-dynamic-table"></div>
            <!-- Paginación -->
            <div class="mt-3">
                <x-pagination-info :paginator="$users" entity="usuarios" />
            </div>
        </div>
    </div>


</div>

@endsection

@push('js')
<!-- DataTables removido para usar paginación de Laravel -->
@endpush
@vite(['resources/js/components/tables/UserTableManager.js'])
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.UserTableManager) {
            // Preparar datos con roles
            const rawUsers = @json($users->items());
            const usersData = rawUsers.map(user => {
                let roleName = '';
                if (user.roles && user.roles.length > 0) {
                    roleName = user.roles[0].name;
                }
                return {
                    id: user.id,
                    name: user.name,
                    email: user.email,
                    status: user.status_text,
                    role: roleName
                };
            });
            window.UserTableManager.init({
                el: '#users-dynamic-table',
                users: usersData,
                canEdit: @json(auth()->user()->can('editar-user')),
                canDelete: @json(auth()->user()->can('eliminar-user'))
            });
        }
    });
</script>