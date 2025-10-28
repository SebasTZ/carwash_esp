@extends('layouts.app')

@section('title','Ventas')

@push('css-datatable')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
@endpush
@push('css')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .row-not-space {
        width: 110px;
    }
</style>
@endpush

@section('content')

@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Ventas</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Ventas</li>
    </ol>

    @can('crear-venta')
    <div class="mb-4">
        <a href="{{route('ventas.create')}}">
            <button type="button" class="btn btn-primary">Agregar nuevo registro</button>
        </a>
    </div>
    @endcan

    <div class="mb-4">
    <a href="{{ route('ventas.reporte.diario') }}">
        <button type="button" class="btn btn-secondary">Reporte diario</button>
    </a>
    <a href="{{ route('ventas.reporte.semanal') }}">
        <button type="button" class="btn btn-secondary">Reporte semanal</button>
    </a>
    <a href="{{ route('ventas.reporte.mensual') }}">
        <button type="button" class="btn btn-secondary">Reporte mensual</button>
    </a>
    </div>

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
@vite(['resources/js/components/DynamicTable.js', 'resources/js/modules/VentaManager.js'])
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.DynamicTable) {
            window.DynamicTable.init({
                el: '#ventas-dynamic-table',
                data: @json($ventas),
                columns: [
                    { label: 'Comprobante', field: 'comprobante' },
                    { label: 'Cliente', field: 'cliente' },
                    { label: 'Fecha y Hora', field: 'fecha_hora' },
                    { label: 'Vendedor', field: 'vendedor' },
                    { label: 'Total', field: 'total' },
                    { label: 'Comentarios', field: 'comentarios' },
                    { label: 'Método de Pago', field: 'medio_pago' },
                    { label: 'Efectivo', field: 'efectivo' },
                    { label: 'Yape', field: 'yape' },
                    { label: 'Servicio de Lavado', field: 'servicio_lavado' },
                    { label: 'Hora Fin de Lavado', field: 'horario_lavado' },
                    { label: 'Acciones', field: 'acciones' },
                ],
                actions: {
                    show: {
                        label: 'Ver',
                        url: function(row) { return `/ventas/${row.id}`; },
                        can: @json(auth()->user()->can('mostrar-venta'))
                    },
                    delete: {
                        label: 'Eliminar',
                        url: function(row) { return `/ventas/${row.id}`; },
                        can: @json(auth()->user()->can('eliminar-venta'))
                    }
                },
                pagination: true
            });
        }
    });
</script>
@endpush