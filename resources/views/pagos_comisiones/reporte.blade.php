@extends('layouts.app')

@section('content')
<div class="container">
    <div id="pago-comision-reporte-table-container"></div>
</div>
@php
    $historial = [];

    foreach (\App\Models\Lavador::where('estado', 'activo')->get() as $lavador) {
        foreach ($lavador->pagosComisiones()
            ->where('desde', '<=', $fechaFin)
            ->where('hasta', '>=', $fechaInicio)
            ->orderBy('fecha_pago', 'desc')
            ->get() as $pago) {
            $historial[] = [
                'lavador_nombre' => $lavador->nombre,
                'monto_pagado' => $pago->monto_pagado,
                'desde' => $pago->desde,
                'hasta' => $pago->hasta,
                'fecha_pago' => $pago->fecha_pago,
                'observacion' => $pago->observacion,
            ];
        }
    }
@endphp
@push('js')
@vite(['resources/js/components/tables/PagoComisionReporteTableManager.js'])
<script type="application/json" id="pago-comision-reporte-config">{!! json_encode([
    'el' => '#pago-comision-reporte-table-container',
    'reporte' => $data,
    'historial' => $historial,
    'fechaInicio' => $fechaInicio,
    'fechaFin' => $fechaFin,
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endpush
@endsection

