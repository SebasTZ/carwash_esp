@extends('layouts.app')

@section('title','Create Role')

@push('css')

@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Create Role</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('roles.index')}}">Roles</a></li>
        <li class="breadcrumb-item active">Create Role</li>
    </ol>

    <div class="card">
        <div class="card-header">
            <p>Note: Roles are a set of permissions</p>
        </div>
        <div class="card-body">
            <form action="{{ route('roles.store') }}" method="post">
                @csrf
                <!---Role name---->
                <div class="row mb-4">
                    <label for="name" class="col-md-auto col-form-label">Role name:</label>
                    <div class="col-md-4">
                        <input autocomplete="off" type="text" name="name" id="name" class="form-control" value="{{old('name')}}">
                    </div>
                    <div class="col-md-4">
                        @error('name')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>
                </div>

                <!---Permissions---->
                <div class="col-12">
                    <p class="text-muted">Permissions for the role:</p>
                    @foreach ($permisos as $item)
                    <div class="form-check mb-2">
                        <input type="checkbox" name="permission[]" id="{{$item->id}}" class="form-check-input" value="{{$item->id}}">
                        <label for="{{$item->id}}" class="form-check-label">{{$item->label_en}}</label>
                    </div>
                    @endforeach
                </div>
                @error('permission')
                <small class="text-danger">{{'*'.$message}}</small>
                @enderror

                <div class="col-12 text-center">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>

            </form>
        </div>
    </div>


</div>
@endsection

@push('js')

@endpush