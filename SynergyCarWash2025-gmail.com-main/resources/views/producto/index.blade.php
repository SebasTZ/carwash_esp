@extends('layouts.app')

@section('title','Products')

@push('css-datatable')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
@endpush

@push('css')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')

@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Products</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Home</a></li>
        <li class="breadcrumb-item active">Products</li>
    </ol>

    @can('crear-producto')
    <div class="mb-4">
        <a href="{{route('productos.create')}}">
            <button type="button" class="btn btn-primary">Add new record</button>
        </a>
    </div>
    @endcan

    <div class="card">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Products Table
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-striped fs-6">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Brand</th>
                        <th>Stock</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productos as $producto)
                    <tr>
                        <td>{{ $producto->codigo }}</td>
                        <td>{{ $producto->nombre }}</td>
                        <td>{{ $producto->categoria->caracteristica->nombre ?? '-' }}</td>
                        <td>{{ $producto->marca->nombre ?? '-' }}</td>
                        <td>{{ $producto->stock }}</td>
                        <td>{{ $producto->precio_venta }}</td>
                        <td>
                            @if ($producto->estado == 1)
                            <span class="badge rounded-pill text-bg-success">active</span>
                            @else
                            <span class="badge rounded-pill text-bg-danger">deleted</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-around">
                                <a class="btn btn-info btn-sm" href="{{route('productos.edit',['producto'=>$producto])}}" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('productos.destroy',['producto'=>$producto->id]) }}" method="post">
                                    @method('DELETE')
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete" onclick="return confirm('Are you sure you want to delete this product?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
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
