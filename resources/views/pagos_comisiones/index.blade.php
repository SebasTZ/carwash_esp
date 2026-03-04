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

<script type="module">
window.addEventListener('load', () => {
    const { DynamicTable } = window.CarWash;

    const columns = [
        { key: 'lavador.nombre', label: 'Lavador' },
        {
            key: 'monto_pagado',
            label: 'Monto Pagado',
            formatter: (value) => `<span class='badge bg-success'>S/ ${parseFloat(value).toFixed(2)}</span>`
        },
        {
            key: 'desde',
            label: 'Desde',
            formatter: (value) => value ? new Date(value).toLocaleDateString('es-PE') : ''
        },
        {
            key: 'hasta',
            label: 'Hasta',
            formatter: (value) => value ? new Date(value).toLocaleDateString('es-PE') : ''
        },
        {
            key: 'fecha_pago',
            label: 'Fecha de Pago',
            formatter: (value) => value ? new Date(value).toLocaleDateString('es-PE') : ''
        },
        {
            key: 'actions',
            label: 'Acciones',
            formatter: (value, row) => {
                @can('ver-historial-pago-comision')
                    return `<a href="/pagos_comisiones/lavador/${row.lavador_id}" class="btn btn-sm btn-info">Historial</a>`;
                @else
                    return '-';
                @endcan
            }
        }
    ];

    const data = @json($pagos->items());

    new DynamicTable('#pagosTable', {
        columns,
        data,
        searchPlaceholder: 'Buscar pagos...',
        emptyMessage: 'No hay pagos registrados'
    });
});
</script>
@endsection
