@extends('layouts.app')

@section('content')
<div class="container">
    <div id="pago-comision-reporte-table-container"></div>
</div>
@push('js')
@vite(['resources/js/components/tables/PagoComisionReporteTableManager.js'])
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.PagoComisionReporteTableManager) {
            // Preparamos los datos para JS
            const reporte = @json($data);
            // Historial plano para JS
            const historial = [];
            @foreach(\App\Models\Lavador::where('estado', 'activo')->get() as $lavador)
                @foreach($lavador->pagosComisiones()
                    ->where('desde', '<=', $fechaFin)
                    ->where('hasta', '>=', $fechaInicio)
                    ->orderBy('fecha_pago', 'desc')
                    ->get() as $pago)
                    historial.push({
                        lavador_nombre: @json($lavador->nombre),
                        monto_pagado: @json($pago->monto_pagado),
                        desde: @json($pago->desde),
                        hasta: @json($pago->hasta),
                        fecha_pago: @json($pago->fecha_pago),
                        observacion: @json($pago->observacion)
                    });
                @endforeach
            @endforeach
            window.PagoComisionReporteTableManager.init({
                el: '#pago-comision-reporte-table-container',
                reporte,
                historial,
                fechaInicio: @json($fechaInicio),
                fechaFin: @json($fechaFin)
            });
        }
    });
</script>
@endpush
@endsection
