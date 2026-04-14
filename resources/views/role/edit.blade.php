@extends('layouts.app')

@section('title','Editar Rol')

@push('css')

@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Editar Rol</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('roles.index')}}">Roles</a></li>
        <li class="breadcrumb-item active">Editar Rol</li>
    </ol>

    <div class="card">
        <div class="card-header">
            <p>Nota: Los roles son un conjunto de permisos</p>
        </div>
        <div class="card-body">
            <form id="role-edit-form" class="cw-form" action="{{ route('roles.update',['role'=>$role]) }}" method="post" data-validate>
                @method('PATCH')
                @csrf
                <div id="role-edit-form-fields"></div>
                <div class="cw-form-actions">
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                    <button type="reset" class="btn btn-secondary">Restablecer</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('js')
@vite(['resources/js/components/forms/FormValidator.js', 'resources/js/components/tables/RoleFormManager.js'])
<script type="application/json" id="role-edit-config">{!! json_encode([
    'permisos' => $permisos,
    'role' => $role,
    'old' => [
        'name' => old('name'),
        'permission' => old('permission'),
    ],
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endpush
