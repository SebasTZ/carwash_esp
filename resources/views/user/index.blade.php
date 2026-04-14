@extends('layouts.app')

@section('title','Usuarios')

@section('content')

@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <div class="cw-page-header mt-4">
        <h1 class="cw-page-title">Usuarios</h1>
        @can('crear-user')
        <div class="cw-page-actions">
            <a href="{{ route('users.create') }}" class="btn btn-primary">Agregar nuevo usuario</a>
        </div>
        @endcan
    </div>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Usuarios</li>
    </ol>

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
<script type="application/json" id="users-index-config">{!! json_encode([
    'users' => $users->items(),
    'canEdit' => auth()->user()->can('editar-user'),
    'canDelete' => auth()->user()->can('eliminar-user'),
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
