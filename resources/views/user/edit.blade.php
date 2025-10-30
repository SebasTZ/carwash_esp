@extends('layouts.app')

@section('title','Editar Usuario')

@push('css')

@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Editar Usuario</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('users.index')}}">Usuarios</a></li>
        <li class="breadcrumb-item active">Editar Usuario</li>
    </ol>

    <div class="card text-bg-light">
        <form action="{{ route('users.update',['user' => $user]) }}" method="post">
            @method('PATCH')
            @csrf
            <div class="card-header">
                <p class="">Nota: Los usuarios son quienes pueden acceder al sistema</p>
            </div>
            <div class="card-body">
                <div id="user-edit-form-fields"></div>
            </div>
            <div class="card-footer text-center">
                <button type="submit" class="btn btn-primary">Actualizar usuario</button>
                <button type="reset" class="btn btn-secondary">Restablecer</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
@vite(['resources/js/components/tables/UserFormManager.js', 'resources/js/components/forms/FormValidator.js'])
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.UserFormManager) {
            window.UserFormManager.init({
                el: '#user-edit-form-fields',
                user: Object.assign({}, @json($user), { role: @json($user->getRoleNames()->first()) }),
                roles: @json($roles->pluck('name')->toArray()),
                old: {
                    name: @json(old('name')),
                    email: @json(old('email')),
                    role: @json(old('role')),
                    status: @json(old('status'))
                }
            });
        }
        if (window.FormValidator) {
            new FormValidator('#user-edit-form');
        }
    });
</script>
@endpush