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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.PagoComisionHistorialTableManager) {
            window.PagoComisionHistorialTableManager.init({
                el: '#pago-comision-historial-table-container',
                pagos: @json($pagos),
                lavador: @json($lavador),
                reporteUrl: @json($reporteUrl),
                fechaInicio: @json($fechaInicio ?? null),
                fechaFin: @json($fechaFin ?? null)
            });
        }
    });
</script>
@endpush
@endsection