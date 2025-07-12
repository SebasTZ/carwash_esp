@extends('layouts.app')

@section('title','Edit User')

@push('css')

@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Edit User</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('users.index')}}">Users</a></li>
        <li class="breadcrumb-item active">Edit User</li>
    </ol>

    <div class="card text-bg-light">
        <form action="{{ route('users.update',['user' => $user]) }}" method="post">
            @method('PATCH')
            @csrf
            <div class="card-header">
                <p class="">Note: Users are those who can access the system</p>
            </div>
            <div class="card-body">
                <!---Nombre---->
                <div class="row mb-4">
                    <label for="name" class="col-lg-2 col-form-label">First Name(s):</label>
                    <div class="col-lg-4">
                        <input type="text" name="name" id="name" class="form-control" value="{{old('name',$user->name)}}">
                    </div>
                    <div class="col-lg-4">
                        <div class="form-text">
                            Enter a single name
                        </div>
                    </div>
                    <div class="col-lg-2">
                        @error('name')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>
                </div>

                <!---Email---->
                <div class="row mb-4">
                    <label for="email" class="col-lg-2 col-form-label">Email:</label>
                    <div class="col-lg-4">
                        <input type="email" name="email" id="email" class="form-control" value="{{old('email',$user->email)}}">
                    </div>
                    <div class="col-lg-4">
                        <div class="form-text">
                            Email address
                        </div>
                    </div>
                    <div class="col-lg-2">
                        @error('email')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>
                </div>

                <!---Password---->
                <div class="row mb-4">
                    <label for="password" class="col-lg-2 col-form-label">Password:</label>
                    <div class="col-lg-4">
                        <input type="password" name="password" id="password" class="form-control">
                    </div>
                    <div class="col-lg-4">
                        <div class="form-text">
                            Enter a secure password. It must include numbers.
                        </div>
                    </div>
                    <div class="col-lg-2">
                        @error('password')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>
                </div>

                <!---Confirm_Password---->
                <div class="row mb-4">
                    <label for="password_confirm" class="col-lg-2 col-form-label">Confirm:</label>
                    <div class="col-lg-4">
                        <input type="password" name="password_confirm" id="password_confirm" class="form-control">
                    </div>
                    <div class="col-lg-4">
                        <div class="form-text">
                            Re-enter your password.
                        </div>
                    </div>
                    <div class="col-lg-2">
                        @error('password_confirm')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>
                </div>

                <!---Roles---->
                <div class="row mb-4">
                    <label for="role" class="col-lg-2 col-form-label">Select role:</label>
                    <div class="col-lg-4">
                        <select name="role" id="role" class="form-select">
                            @foreach ($roles as $item)
                            @if ( in_array($item->name,$user->roles->pluck('name')->toArray()) )
                            <option selected value="{{$item->name}}" @selected(old('role')==$item->name)>{{$item->name}}</option>
                            @else
                            <option value="{{$item->name}}" @selected(old('role')==$item->name)>{{$item->name}}</option>
                            @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-text">
                            Choose a role for the user.
                        </div>
                    </div>
                    <div class="col-lg-2">
                        @error('role')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>
                </div>

            </div>
            <div class="card-footer text-center">
                <button type="submit" class="btn btn-primary">Update</button>
                <button type="reset" class="btn btn-secondary">Reset</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')

@endpush