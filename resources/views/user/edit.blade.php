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
        <form id="user-edit-form" class="cw-form" action="{{ route('users.update',['user' => $user]) }}" method="post">
            @method('PATCH')
            @csrf
            <div class="card-header">
                <p class="">Nota: Los usuarios son quienes pueden acceder al sistema</p>
            </div>
            <div class="card-body">
                <div id="user-edit-form-fields"></div>
            </div>
            <div class="card-footer">
                <div class="cw-form-actions">
                    <button type="submit" class="btn btn-primary">Actualizar usuario</button>
                    <button type="reset" class="btn btn-secondary">Restablecer</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
@vite(['resources/js/components/tables/UserFormManager.js', 'resources/js/components/forms/FormValidator.js'])
<script type="application/json" id="user-edit-config">{!! json_encode([
    'user' => array_merge($user->toArray(), ['role' => $user->getRoleNames()->first()]),
    'roles' => $roles->pluck('name')->toArray(),
    'old' => [
        'name' => old('name'),
        'email' => old('email'),
        'role' => old('role'),
        'status' => old('status'),
    ],
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endpush
