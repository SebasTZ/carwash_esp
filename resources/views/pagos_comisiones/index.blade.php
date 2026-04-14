@extends('layouts.app')

@section('title','Pagos de Comisiones')

@section('content')
<div class="container-fluid px-4">
    <div class="cw-page-header mt-4">
        <h1 class="cw-page-title">Pagos de Comisiones</h1>
        @can('crear-pago-comision')
        <div class="cw-page-actions">
            <a href="{{ route('pagos_comisiones.create') }}" class="btn btn-primary">Registrar pago</a>
        </div>
        @endcan
    </div>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Pagos de Comisiones</li>
    </ol>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Tabla de Pagos de Comisiones
        </div>
        <div class="card-body">
            <table id="pagosTable" class="table table-bordered"></table>
            <x-pagination-info :paginator="$pagos" entity="pagos" />
        </div>
    </div>
</div>

<script type="application/json" id="pagos-comisiones-index-config">{!! json_encode([
    'data' => $pagos->items(),
    'canHistorial' => auth()->user()->can('ver-historial-pago-comision'),
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endsection

