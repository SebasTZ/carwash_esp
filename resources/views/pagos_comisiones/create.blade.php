@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Registrar Pago de Comisión</h1>
    @can('crear-pago-comision')
    <div id="pago-comision-form-container"></div>
    @endcan

    <x-flash-alert />
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

