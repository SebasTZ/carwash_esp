@extends('layouts.app')

@section('title','Roles')

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
@vite(['resources/js/components/tables/RoleTableManager.js'])
<script type="application/json" id="roles-index-config">{!! json_encode([
    'roles' => $roles->items(),
    'canEdit' => auth()->user()->can('editar-role'),
    'canDelete' => auth()->user()->can('eliminar-role'),
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endpush
