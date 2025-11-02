@extends('layouts.app')

@section('title','Ver Producto')

@push('css')
@endpush

@section('content')
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
