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
            <form id="role-create-form" action="{{ route('roles.store') }}" method="post" data-validate>
                @csrf
                <div id="role-create-form-fields"></div>
                <div class="col-12 text-center">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>


</div>
@endsection

@push('js')
@vite(['resources/js/components/FormValidator.js', 'resources/js/modules/RoleFormManager.js'])
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.FormValidator) {
            new FormValidator('#role-create-form');
        }
        if (window.RoleFormManager) {
            window.RoleFormManager.init({
                el: '#role-create-form-fields',
                permisos: @json($permisos),
                old: {
                    name: @json(old('name')),
                    permission: @json(old('permission'))
                }
            });
        }
    });
</script>
@endpush