@extends('layouts.app')

@section('title','Crear Rol')

@push('css')

@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Crear Rol</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('roles.index')}}">Roles</a></li>
        <li class="breadcrumb-item active">Crear Rol</li>
    </ol>

    <div class="card">
        <div class="card-header">
            <p>Nota: Los roles son un conjunto de permisos</p>
        </div>
        <div class="card-body">
            <form id="role-create-form" class="cw-form" action="{{ route('roles.store') }}" method="post" data-validate>
                @csrf
                <div id="role-create-form-fields"></div>
                <div class="cw-form-actions">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>


</div>
@endsection

@push('js')
@vite(['resources/js/components/tables/RoleFormManager.js'])
<script type="application/json" id="role-create-config">{!! json_encode([
    'permisos' => $permisos,
    'old' => [
        'name' => old('name'),
        'permission' => old('permission'),
    ],
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endpush
