
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
    <h1 class="mt-4 text-center">Compras</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Compras</li>
    </ol>

    @can('crear-compra')
    <div class="mb-4">
        <a href="{{route('compras.create')}}">
            <button type="button" class="btn btn-primary">Agregar nueva compra</button>
        </a>
    </div>
    @endcan

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Tabla de Compras
        </div>
        <div class="card-body">
            <table id="dynamicTableCompras"></table>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const config = {
                        columns: [
                            { key: 'comprobante', label: 'Comprobante', render: row => `<p class='fw-semibold mb-1'>${row.comprobante}</p><p class='text-muted mb-0'>${row.numero_comprobante}</p>` },
                            { key: 'proveedor', label: 'Proveedor', render: row => `<p class='fw-semibold mb-1'>${row.tipo_persona}</p><p class='text-muted mb-0'>${row.razon_social}</p>` },
                            { key: 'fecha_hora', label: 'Fecha y Hora', render: row => `<div class='row-not-space'><p class='fw-semibold mb-1'><span class='m-1'><i class='fa-solid fa-calendar-days'></i></span>${row.fecha}</p><p class='fw-semibold mb-0'><span class='m-1'><i class='fa-solid fa-clock'></i></span>${row.hora}</p></div>` },
                            { key: 'total', label: 'Total', render: row => row.total },
                            { key: 'acciones', label: 'Acciones', render: row => row.acciones, width: 180 }
                        ],
                        dataUrl: '/api/compras',
                        pagination: true,
                        preserveQuery: true
                    };
                    new window.CarWash.DynamicTable('#dynamicTableCompras', config);
                    console.log('✅ DynamicTable inicializado correctamente para Compras');
                });
            </script>
        </div>
    </div>
</div>
@endsection

@push('js')
<!-- DynamicTable maneja la paginación y acciones -->
@endpush