@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div id="pago-comision-historial-table-container"></div>
        </div>
    </div>
</div>
@push('js')
@vite(['resources/js/components/tables/PagoComisionHistorialTableManager.js'])
<script type="application/json" id="pago-comision-show-config">{!! json_encode([
    'el' => '#pago-comision-historial-table-container',
    'pagos' => $pagos,
    'lavador' => $lavador,
    'reporteUrl' => $reporteUrl,
    'fechaInicio' => $fechaInicio ?? null,
    'fechaFin' => $fechaFin ?? null,
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endpush
@endsection
