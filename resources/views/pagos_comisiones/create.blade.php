@push('js')
<script>
    window.Laravel = window.Laravel || {};
    window.Laravel.csrfToken = '{{ csrf_token() }}';
</script>
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.PagoComisionFormManager) {
            window.PagoComisionFormManager.init({
                el: '#pago-comision-form-container',
                lavadores: @json($lavadores),
                old: {
                    lavador_id: @json(old('lavador_id')),
                    monto_pagado: @json(old('monto_pagado')),
                    desde: @json(old('desde')),
                    hasta: @json(old('hasta')),
                    fecha_pago: @json(old('fecha_pago')),
                    observacion: @json(old('observacion'))
                },
                errors: @json($errors->toArray())
            });
        }
    });
</script>
@endpush
@endsection
