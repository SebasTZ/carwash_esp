@extends('layouts.app')

@section('title','Crear Usuario')

@push('css')

@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Crear Usuario</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('users.index')}}">Usuarios</a></li>
        <li class="breadcrumb-item active">Crear Usuario</li>
    </ol>

    <div class="card text-bg-light">
        <form action="{{ route('users.store') }}" method="post">
            @csrf
            <div class="card-header">
                <p class="">Nota: Los usuarios son quienes pueden acceder al sistema</p>
            </div>
            <div class="card-body">

                <div id="user-create-form-fields"></div>
            </div>
            <div class="card-footer text-center">
                <button type="submit" class="btn btn-primary">Registrar usuario</button>
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
                el: '#user-create-form-fields',
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
            new FormValidator('#user-create-form');
        }
    });
</script>
@endpush