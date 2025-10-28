@extends('layouts.app')

@section('title','Ver Producto')

@push('css')
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Ver Producto</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('productos.index')}}">Productos</a></li>
        <li class="breadcrumb-item active">Ver Producto</li>
    </ol>
</div>

<div id="productoShowContainer"></div>
<script type="module">
    import ProductoShow from '/js/modules/ProductoShow.js';
    document.addEventListener('DOMContentLoaded', function() {
        new ProductoShow({
            elementId: 'productoShowContainer',
            producto: @json($producto),
        });
    });
</script>
@endsection
