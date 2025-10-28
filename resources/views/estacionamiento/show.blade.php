@extends('layouts.app')

@section('title', 'Detalle de Estacionamiento')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mt-4 text-primary">
            <i class="fas fa-car-side me-2"></i>Detalle de Estacionamiento
        </h1>
        <a href="{{ route('estacionamiento.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-list me-2"></i>Volver a la Lista
        </a>
    </div>
    <div id="estacionamiento-detalle"></div>
</div>
@push('js')
@vite(['resources/js/components/EstacionamientoDetalle.js'])
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.EstacionamientoDetalle) {
            window.EstacionamientoDetalle.init({
                el: '#estacionamiento-detalle',
                estacionamiento: @json($estacionamiento)
            });
        }
    });
</script>
@endpush
@endsection