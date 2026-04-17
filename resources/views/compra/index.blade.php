
@extends('layouts.app')

@section('title','Compras')

@section('content')

@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <div class="cw-page-header mt-4">
        <h1 class="cw-page-title">Compras</h1>
        @can('crear-compra')
        <div class="cw-page-actions">
            <a href="{{ route('compras.create') }}" class="btn btn-primary">Agregar nueva compra</a>
        </div>
        @endcan
    </div>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Compras</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Tabla de Compras
        </div>
        <div class="card-body">
            <table id="dynamicTableCompras"></table>
        </div>
    </div>
</div>

@php
    $comprasTableData = $compras->getCollection()->map(function ($compra) {
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
            'total' => (float) $compra->total,
        ];
    })->values();
@endphp
@endsection

@push('js')
<script type="application/json" id="compras-index-config">{!! json_encode([
    'data' => $comprasTableData,
    'canShow' => auth()->user()->can('mostrar-compra'),
    'canDelete' => auth()->user()->can('eliminar-compra'),
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endpush