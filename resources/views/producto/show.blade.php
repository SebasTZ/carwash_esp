@extends('layouts.app')

@section('title','Ver Producto')

@push('css')
@endpush

@section('content')
<div id="productoShowContainer"></div>
<script type="application/json" id="producto-show-config">{!! json_encode([
    'producto' => $producto,
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endsection

