@extends('layouts.app')

@section('title','Ventas')

@section('content')

@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <div class="cw-page-header mt-4">
        <h1 class="cw-page-title">Ventas</h1>
        <div class="cw-page-actions">
            @can('crear-venta')
            <a href="{{ route('ventas.create') }}" class="btn btn-primary">Agregar nuevo registro</a>
            @endcan
            <a href="{{ route('ventas.reporte.diario') }}" class="btn btn-secondary">Reporte diario</a>
            <a href="{{ route('ventas.reporte.semanal') }}" class="btn btn-secondary">Reporte semanal</a>
            <a href="{{ route('ventas.reporte.mensual') }}" class="btn btn-secondary">Reporte mensual</a>
            <a href="{{ route('ventas.reporte.personalizado') }}" class="btn btn-secondary">Reporte personalizado</a>
        </div>
    </div>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Ventas</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Tabla de Ventas
        </div>
        <div class="card-body">
            <div id="ventas-dynamic-table"></div>
        </div>
    </div>

</div>
@endsection

@push('js')
<script type="application/json" id="ventas-index-config">{!! json_encode([
    'data' => $ventas->items(),
    'canShow' => auth()->user()->can('mostrar-venta'),
    'canDelete' => auth()->user()->can('eliminar-venta'),
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endpush
