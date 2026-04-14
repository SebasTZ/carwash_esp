
@extends('layouts.app')

@section('title','Compras')

@push('css')
<style>
    .row-not-space {
        width: 110px;
    }
</style>
@endpush

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
@endsection

@push('js')
<!-- DynamicTable maneja la paginación y acciones -->
@endpush