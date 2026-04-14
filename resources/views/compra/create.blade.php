
@extends('layouts.app')

@section('title','Registrar Compra')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Registrar Compra</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('compras.index')}}">Compras</a></li>
        <li class="breadcrumb-item active">Registrar Compra</li>
    </ol>
</div>

<div id="formCompraContainer"></div>

<script type="application/json" id="compra-form-data">
{!! json_encode([
    'productos' => $productos,
    'proveedores' => $proveedores,
    'comprobantes' => $comprobantes,
    'impuesto' => $impuesto ?? 18,
    'old' => old(),
    'errors' => $errors->all(),
    'action' => route('compras.store'),
    'method' => 'POST',
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
</script>
@endsection

@push('js')
@vite(['resources/js/modules/CompraCreateManager.js'])
@endpush

