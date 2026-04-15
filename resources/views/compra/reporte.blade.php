
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

@endsection

@push('js')
<!-- DynamicTable maneja la paginación y acciones -->
@endpush