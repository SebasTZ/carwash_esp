
@extends('layouts.app')

@section('title','Ver Compra')

@push('css')
<style>
    @media (max-width:575px) { #hide-group { display: none; } }
    @media (min-width:576px) { #icon-form { display: none; } }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Ver Compra</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('compras.index')}}">Compras</a></li>
        <li class="breadcrumb-item active">Ver Compra</li>
    </ol>
</div>

<div id="compraShowContainer"></div>
<script type="module">
    import CompraShow from '/js/modules/CompraShow.js';
    document.addEventListener('DOMContentLoaded', function() {
        new CompraShow({
            elementId: 'compraShowContainer',
            compra: @json($compra),
            productos: @json($compra->productos),
        });
    });
</script>
@endsection