
@extends('layouts.app')

@section('title', 'Reporte de Compras ' . ucfirst($reporte))

@section('content')

@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Reporte de Compras {{ ucfirst($reporte) }}</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('compras.index') }}">Compras</a></li>
        <li class="breadcrumb-item active">Reporte {{ ucfirst($reporte) }}</li>
    </ol>

    <div class="mb-4">
        <a href="{{ route('compras.export.' . $reporte) }}">
            <button type="button" class="btn btn-success">Exportar a Excel</button>
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Tabla de Compras {{ $reporte }}
        </div>
        <div class="card-body">
            <table id="dynamicTableComprasReporte" data-report-type="{{ $reporte }}"></table>
        </div>
    </div>
</div>

@php
    $comprasReporteData = $compras->map(function ($compra) {
        $persona = $compra->proveedore?->persona;
        $fechaHora = $compra->fecha_hora ? \Carbon\Carbon::parse($compra->fecha_hora) : null;

        return [
            'id' => $compra->id,
            'comprobante' => $compra->comprobante?->tipo_comprobante,
            'numero_comprobante' => $compra->numero_comprobante,
            'tipo_persona' => $persona?->tipo_persona,
            'razon_social' => $persona?->razon_social,
            'fecha' => $fechaHora?->format('d/m/Y'),
            'hora' => $fechaHora?->format('H:i'),
            'impuesto' => (float) $compra->impuesto,
            'total' => (float) $compra->total,
        ];
    })->values();
@endphp

@endsection

@push('js')
<script type="application/json" id="compras-reporte-config">{!! json_encode([
    'data' => $comprasReporteData,
    'reporte' => $reporte,
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endpush