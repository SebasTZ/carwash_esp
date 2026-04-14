@extends('layouts.app')

@section('title','Tipos de Vehículo')

@section('content')
<div class="container-fluid px-4">
    <div class="cw-page-header mt-4">
        <h1 class="cw-page-title">Tipos de Vehículo</h1>
        @can('crear-tipo-vehiculo')
        <div class="cw-page-actions">
            <a href="{{ route('tipos_vehiculo.create') }}" class="btn btn-primary">Agregar tipo de vehículo</a>
        </div>
        @endcan
    </div>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Tipos de Vehículo</li>
    </ol>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Tabla de Tipos de Vehículo
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered" id="tiposVehiculoTable"></table>
            <div class="mt-3">
                <x-pagination-info :paginator="$tipos" entity="tipos de vehículo" />
            </div>
        </div>
    </div>
</div>

<script type="application/json" id="tipos-vehiculo-index-config">{!! json_encode([
    'data' => $tipos->items(),
    'canEdit' => auth()->user()->can('editar-tipo-vehiculo'),
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endsection

