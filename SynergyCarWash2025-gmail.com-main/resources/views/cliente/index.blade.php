@extends('layouts.app')

@section('title','Clients')

@push('css-datatable')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
@endpush

@push('css')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')

@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Clients</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Home</a></li>
        <li class="breadcrumb-item active">Clients</li>
    </ol>

    @can('crear-cliente')
    <div class="mb-4">
        <a href="{{route('clientes.create')}}">
            <button type="button" class="btn btn-primary">Add new record</button>
        </a>
    </div>
    @endcan

    <div class="card">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Clients Table
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-striped fs-6">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Document</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($clientes as $cliente)
                    <tr>
                        <td>{{ $cliente->persona->razon_social }}</td>
                        <td>{{ $cliente->persona->numero_documento }}</td>
                        <td>{{ $cliente->persona->telefono }}</td>
                        <td>{{ $cliente->persona->email }}</td>
                        <td>
                            @if ($cliente->persona->estado == 1)
                            <span class="badge rounded-pill text-bg-success">active</span>
                            @else
                            <span class="badge rounded-pill text-bg-danger">deleted</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-around">
                                <div>
                                    <a class="btn btn-info btn-sm" href="{{route('clientes.edit',['cliente'=>$cliente])}}" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                                <div>
                                    <form action="{{ route('clientes.destroy',['cliente'=>$cliente->id]) }}" method="post">
                                        @method('DELETE')
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm" title="Delete" onclick="return confirm('Are you sure you want to delete this client?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
<script src="{{ asset('js/datatables-simple-demo.js') }}"></script>
@endpush