@extends('layouts.app')

@section('title','Create User')

@push('css')

@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Create User</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('users.index')}}">Users</a></li>
        <li class="breadcrumb-item active">Create User</li>
    </ol>

    <div class="card text-bg-light">
        <form action="{{ route('users.store') }}" method="post">
            @csrf
            <div class="card-header">
                <p class="">Note: Users are those who can access the system</p>
            </div>
            <div class="card-body">

                <!---Nombre---->
                <div class="row mb-4">
                    <label for="name" class="col-lg-2 col-form-label">First Name(s):</label>
                    <div class="col-lg-4">
                        <input autocomplete="off" type="text" name="name" id="name" class="form-control" value="{{old('name')}}" aria-labelledby="nameHelpBlock">
                    </div>
                    <div class="col-lg-4">
                        <div class="form-text" id="nameHelpBlock">
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
                        <input autocomplete="off" type="email" name="email" id="email" class="form-control" value="{{old('email')}}" aria-labelledby="emailHelpBlock">
                    </div>
                    <div class="col-lg-4">
                        <div class="form-text" id="emailHelpBlock">
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
                        <input type="password" name="password" id="password" class="form-control" aria-labelledby="passwordHelpBlock">
                    </div>
                    <div class="col-lg-4">
                        <div class="form-text" id="passwordHelpBlock">
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
                        <input type="password" name="password_confirm" id="password_confirm" class="form-control" aria-labelledby="passwordConfirmHelpBlock">
                    </div>
                    <div class="col-lg-4">
                        <div class="form-text" id="passwordConfirmHelpBlock">
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
                    <label for="role" class="col-lg-2 col-form-label">Role:</label>
                    <div class="col-lg-4">
                        <select name="role" id="role" class="form-select" aria-labelledby="rolHelpBlock">
                            <option value="" selected disabled>Select:</option>
                            @foreach ($roles as $item)
                            <option value="{{$item->name}}" @selected(old('role')==$item->name)>{{$item->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-text" id="rolHelpBlock">
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
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>


</div>
@endsection

@push('js')

@endpush