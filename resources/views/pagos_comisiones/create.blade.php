@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Registrar Pago de Comisión</h1>
    @can('crear-pago-comision')
    <div id="pago-comision-form-container"></div>
    @endcan

    @if(session('warning'))
        <div class="alert alert-warning mt-3">
            {{ session('warning') }}
        </div>
    @endif
    @if(session('success'))
        <div class="alert alert-success mt-3">
            {{ session('success') }}
        </div>
    @endif
</div>

@push('js')
@vite(['resources/js/components/forms/PagoComisionFormManager.js'])
<script type="application/json" id="pago-comision-create-config">{!! json_encode([
    'el' => '#pago-comision-form-container',
    'lavadores' => $lavadores,
    'old' => [
        'lavador_id' => old('lavador_id'),
        'monto_pagado' => old('monto_pagado'),
        'desde' => old('desde'),
        'hasta' => old('hasta'),
        'fecha_pago' => old('fecha_pago'),
        'observacion' => old('observacion'),
    ],
    'errors' => $errors->toArray(),
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endpush
@endsection

