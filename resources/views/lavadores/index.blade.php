@extends('layouts.app')

@section('title','Lavadores')

@section('content')
<div class="container-fluid px-4">
    <div class="cw-page-header mt-4">
        <h1 class="cw-page-title">Lavadores</h1>
        @can('crear-lavador')
        <div class="cw-page-actions">
            <a href="{{ route('lavadores.create') }}" class="btn btn-primary">Agregar lavador</a>
        </div>
        @endcan
    </div>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Lavadores</li>
    </ol>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Tabla de Lavadores
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered" id="lavadoresTable"></table>
            <x-pagination-info :paginator="$lavadores" entity="lavadores" />
        </div>
    </div>
</div>
<script type="application/json" id="lavadores-index-config">{!! json_encode([
    'data' => $lavadores->items(),
    'canEdit' => auth()->user()->can('editar-lavador'),
    'canDelete' => auth()->user()->can('eliminar-lavador'),
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endsection

