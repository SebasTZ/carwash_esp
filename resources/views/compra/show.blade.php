
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
<script type="application/json" id="compra-show-config">{!! json_encode([
    'compra' => $compra,
    'productos' => $compra->productos,
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endsection
