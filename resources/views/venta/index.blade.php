@extends('layouts.app')

@section('title','Ventas')

@push('css-datatable')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
@endpush
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
        <h1 class="cw-page-title">Ventas</h1>
        <div class="cw-page-actions">
            @can('crear-venta')
            <a href="{{ route('ventas.create') }}" class="btn btn-primary">Agregar nuevo registro</a>
            @endcan
            <a href="{{ route('ventas.reporte.diario') }}" class="btn btn-secondary">Reporte diario</a>
            <a href="{{ route('ventas.reporte.semanal') }}" class="btn btn-secondary">Reporte semanal</a>
            <a href="{{ route('ventas.reporte.mensual') }}" class="btn btn-secondary">Reporte mensual</a>
        </div>
    </div>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Ventas</li>
    </ol>

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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@vite(['resources/js/components/DynamicTable.js', 'resources/js/modules/VentaManager.js'])
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🔍 Index Page Loaded');
        console.log('window.DynamicTable:', window.DynamicTable);
        
        if (window.DynamicTable) {
            console.log('✅ DynamicTable encontrado');
            const data = @json($ventas);
            console.log('📊 Datos de ventas:', data.length, 'registros');
            
            window.DynamicTable.init({
                el: '#ventas-dynamic-table',
                data: data,
                columns: [
                    { label: 'Comprobante', field: 'comprobante' },
                    { label: 'Cliente', field: 'cliente' },
                    { label: 'Fecha y Hora', field: 'fecha_hora' },
                    { label: 'Vendedor', field: 'vendedor' },
                    { label: 'Total', field: 'total' },
                    { label: 'Método de Pago', field: 'medio_pago' },
                    { label: 'Servicio de Lavado', field: 'servicio_lavado' },
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
            console.log('✅ DynamicTable inicializado');
        } else {
            console.error('❌ DynamicTable NO encontrado');
        }
    });
</script>
@endpush