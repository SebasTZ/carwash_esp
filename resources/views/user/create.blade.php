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
        <form id="user-create-form" class="cw-form" action="{{ route('users.store') }}" method="post">
            @csrf
            <div class="card-header">
                <p class="">Nota: Los usuarios son quienes pueden acceder al sistema</p>
            </div>
            <div class="card-body">

                <div id="user-create-form-fields"></div>
            </div>
            <div class="card-footer">
                <div class="cw-form-actions">
                    <button type="submit" class="btn btn-primary">Registrar usuario</button>
                </div>
            </div>
        </form>
    </div>


</div>
@endsection

@push('js')
@vite(['resources/js/components/tables/UserFormManager.js'])
<script type="application/json" id="user-create-config">{!! json_encode([
    'roles' => $roles->pluck('name')->toArray(),
    'old' => [
        'name' => old('name'),
        'email' => old('email'),
        'role' => old('role'),
        'status' => old('status'),
    ],
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endpush
