@extends('layouts.app')

@section('title','Edit Role')

@push('css')

@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Edit Role</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('roles.index')}}">Roles</a></li>
        <li class="breadcrumb-item active">Edit Role</li>
    </ol>

    <div class="card">
        <div class="card-header">
            <p>Note: Roles are a set of permissions</p>
        </div>
        <div class="card-body">
            <form action="{{ route('roles.update',['role'=>$role]) }}" method="post">
                @method('PATCH')
                @csrf
                <!---Nombre de rol---->
                <div class="row mb-4">
                    <label for="name" class="col-md-auto col-form-label">Role name:</label>
                    <div class="col-md-4">
                        <input type="text" name="name" id="name" class="form-control" value="{{old('name',$role->name)}}">
                    </div>
                    <div class="col-md-4">
                        @error('name')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>
                </div>

                <!---Permisos---->
                <div class="col-12">
                    <p class="text-muted">Permissions for the role:</p>
                    @foreach ($permisos as $item)
                    @if ( in_array($item->id, $role->permissions->pluck('id')->toArray() ) )
                    <div class="form-check mb-2">
                        <input checked type="checkbox" name="permission[]" id="{{$item->id}}" class="form-check-input" value="{{$item->id}}">
                        <label for="{{$item->id}}" class="form-check-label">{{$item->label_en}}</label>
                    </div>
                    @else
                    <div class="form-check mb-2">
                        <input type="checkbox" name="permission[]" id="{{$item->id}}" class="form-check-input" value="{{$item->id}}">
                        <label for="{{$item->id}}" class="form-check-label">{{$item->label_en}}</label>
                    </div>
                    @endif
                    @endforeach
                </div>
                @error('permission')
                <small class="text-danger">{{'*'.$message}}</small>
                @enderror


                <div class="col-12 text-center">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <button type="reset" class="btn btn-secondary">Reset</button>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection

@push('js')

@endpush