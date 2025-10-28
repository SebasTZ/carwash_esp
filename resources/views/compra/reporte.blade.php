
@extends('layouts.app')

@section('title', 'Reporte de Compras ' . ucfirst($reporte))

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
            <div id="dynamicTableComprasReporte"></div>
            <script type="module">
                import DynamicTable from '/js/components/DynamicTable.js';
                document.addEventListener('DOMContentLoaded', function() {
                    new DynamicTable({
                        elementId: 'dynamicTableComprasReporte',
                        columns: [
                            { key: 'comprobante', label: 'Comprobante', render: row => `<p class='fw-semibold mb-1'>${row.comprobante}</p><p class='text-muted mb-0'>${row.numero_comprobante}</p>` },
                            { key: 'proveedor', label: 'Proveedor', render: row => `<p class='fw-semibold mb-1'>${row.tipo_persona}</p><p class='text-muted mb-0'>${row.razon_social}</p>` },
                            { key: 'fecha_hora', label: 'Fecha y Hora', render: row => `<div class='row-not-space'><p class='fw-semibold mb-1'><span class='m-1'><i class='fa-solid fa-calendar-days'></i></span>${row.fecha}</p><p class='fw-semibold mb-0'><span class='m-1'><i class='fa-solid fa-clock'></i></span>${row.hora}</p></div>` },
                            { key: 'impuesto', label: 'IGV', render: row => row.impuesto },
                            { key: 'total', label: 'Total', render: row => row.total }
                        ],
                        dataUrl: '/api/compras/reporte?tipo={{ $reporte }}',
                        pagination: true,
                        preserveQuery: true
                    });
                });
            </script>
        </div>
    </div>
</div>

@endsection

@push('js')
<!-- DynamicTable maneja la paginación y acciones -->
@endpush