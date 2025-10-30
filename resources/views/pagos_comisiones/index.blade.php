@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Pagos de Comisiones</h1>
    @can('crear-pago-comision')
        <a href="{{ route('pagos_comisiones.create') }}" class="btn btn-primary mb-3">Registrar Pago</a>
    @endcan
    
    <table id="pagosTable" class="table table-bordered"></table>

    <!-- Paginación usando componente -->
    <x-pagination-info :paginator="$pagos" entity="pagos" />
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
