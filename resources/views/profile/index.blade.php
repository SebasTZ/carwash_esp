@extends('layouts.app')

@section('title','Perfil')

@section('content')

@include('layouts.partials.alert')


<div class="container-fluid">
    <h1 class="mt-4 mb-4 text-center">Configuración de Perfil</h1>

    <div class="card">
        <div class="card-header">
            <p class="lead">Configura y personaliza tu perfil</p>
        </div>
        <div class="card-body">
            <div class="">
                @if ($errors->any())
                @foreach ($errors->all() as $item)
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{$item}}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endforeach
                @endif
            </div>

            <form id="profileForm" action="{{route('profile.update',['profile' => $user ])}}" method="POST">
                @method('PATCH')
                @csrf
                <!-- Nombre -->
                <div class="row mb-4">
                    <div class="col-sm-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-square-check"></i></span>
                            <input type="text" class="form-control" value="Nombre(s)" disabled>
                        </div>
                    </div>
                    <div class="col-sm-8">
                        <input autocomplete="off" type="text" name="name" id="name" class="form-control" value="{{old('name',$user->name)}}" disabled>
                    </div>
                </div>
                <!-- Email -->
                <div class="row mb-4">
                    <div class="col-sm-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-square-check"></i></span>
                            <input type="text" class="form-control" value="Correo electrónico" disabled>
                        </div>
                    </div>
                    <div class="col-sm-8">
                        <input autocomplete="off" type="email" name="email" id="email" class="form-control" value="{{old('email',$user->email)}}" disabled>
                    </div>
                </div>
                <!-- Password -->
                <div class="row mb-4">
                    <div class="col-sm-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-square-check"></i></span>
                            <input type="text" class="form-control" value="Contraseña" disabled>
                        </div>
                    </div>
                    <div class="col-sm-8">
                        <input type="password" name="password" id="password" class="form-control" disabled>
                    </div>
                </div>
                <div class="col text-center">
                    <input class="btn btn-success" type="submit" value="Guardar cambios" disabled>
                    <button type="button" id="editProfileBtn" class="btn btn-primary ms-2">Editar</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('js')

@endpush