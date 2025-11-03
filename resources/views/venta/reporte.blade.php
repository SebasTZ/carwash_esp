@extends('layouts.app')

@section('title', 'Reporte de Ventas ' . ucfirst($reporte))

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
    <h1 class="mt-4 text-center">Reporte de Ventas {{ ucfirst($reporte) }}</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('ventas.index') }}">Ventas</a></li>
        <li class="breadcrumb-item active">Reporte {{ ucfirst($reporte) }}</li>
    </ol>

    @if($reporte === 'personalizado')
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-calendar me-1"></i>
            Seleccionar Rango de Fechas
        </div>
        <div class="card-body">
            <form action="{{ route('ventas.reporte.personalizado') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-auto">
                    <label for="fecha_inicio" class="col-form-label">Fecha de Inicio:</label>
                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required value="{{ $fechaInicio ?? '' }}">
                </div>
                <div class="col-auto">
                    <label for="fecha_fin" class="col-form-label">Fecha de Fin:</label>
                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required value="{{ $fechaFin ?? '' }}">
                </div>
                <div class="col-auto">
                    <label class="col-form-label">&nbsp;</label>
                    <button type="submit" class="form-control btn btn-primary">Filtrar</button>
                </div>
                @if(isset($fechaInicio) && isset($fechaFin))
                <div class="col-auto">
                    <label class="col-form-label">&nbsp;</label>
                    <a href="{{ route('ventas.export.personalizado', ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin]) }}" class="form-control btn btn-success">
                        Exportar a Excel
                    </a>
                </div>
                @endif
            </form>
        </div>
    </div>
    @else
    <div class="mb-4">
        <a href="{{ route('ventas.export.' . $reporte) }}">
            <button type="button" class="btn btn-success">Exportar a Excel</button>
        </a>
    </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Tabla de Ventas {{ $reporte }}
        </div>
        <div class="card-body">
            <div id="ventas-reporte-dynamic-table"></div>
            <div class="mt-4">
                <h4 id="ventas-reporte-total"></h4>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
@vite(['resources/js/components/DynamicTable.js', 'resources/js/modules/VentaManager.js'])
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🔍 Reporte Page Loaded');
        
        if (window.DynamicTable) {
            console.log('✅ DynamicTable encontrado');
            const data = @json($ventas);
            console.log('📊 Datos de ventas:', data.length, 'registros');
            
            window.DynamicTable.init({
                el: '#ventas-reporte-dynamic-table',
                data: data,
                columns: [
                    { label: 'Comprobante', field: 'comprobante' },
                    { label: 'Cliente', field: 'cliente' },
                    { label: 'Fecha y Hora', field: 'fecha_hora' },
                    { label: 'Vendedor', field: 'vendedor' },
                    { label: 'Total', field: 'total' },
                    { label: 'Comentarios', field: 'comentarios' },
                    { label: 'Medio de Pago', field: 'medio_pago' },
                    { label: 'Efectivo', field: 'efectivo' },
                    { label: 'Tarjeta Crédito', field: 'tarjeta_credito' },
                    { label: 'Tarjeta Regalo', field: 'tarjeta_regalo_id' },
                    { label: 'Lavado Gratis', field: 'lavado_gratis' },
                    { label: 'Servicio Lavado', field: 'servicio_lavado' },
                    { label: 'Hora Fin de Lavado', field: 'horario_lavado' },
                ],
                pagination: true,
                onRender: function(rows) {
                    // Calcular el total solo para métodos de pago permitidos
                    let total = 0;
                    rows.forEach(function(row) {
                        const medioPago = row.medio_pago ? row.medio_pago.toLowerCase() : '';
                        // Excluir ventas de tarjeta regalo o lavado gratis
                        if (medioPago !== 'tarjeta regalo' && medioPago !== 'lavado gratis (fidelidad)') {
                            total += parseFloat(row.total) || 0;
                        }
                    });
                    document.getElementById('ventas-reporte-total').innerText = 'Total Ventas: S/. ' + total.toFixed(2);
                }
            });
            console.log('✅ DynamicTable inicializado para reporte');
        } else {
            console.error('❌ DynamicTable NO encontrado');
        }
    });
</script>
@endpush