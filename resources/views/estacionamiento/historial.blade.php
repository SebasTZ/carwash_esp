@extends('layouts.app')

@section('title', 'Historial de Estacionamiento')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Historial de Estacionamiento</h1>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-history me-1"></i>
            Registros Finalizados
            <a href="{{ route('estacionamiento.index') }}" class="btn btn-primary btn-sm float-end">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
        <div class="card-body">
            <div id="dynamic-table-estacionamiento-historial"></div>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (window.DynamicTable) {
                    window.DynamicTable.render({
                        target: document.getElementById('dynamic-table-estacionamiento-historial'),
                        data: @json($estacionamientos),
                        columns: [
                            { data: 'placa', title: 'Placa' },
                            { data: 'cliente', title: 'Cliente', formatter: (value, row) => row.cliente?.persona?.razon_social || '' },
                            { data: 'marca', title: 'Marca' },
                            { data: 'modelo', title: 'Modelo' },
                            { data: 'hora_entrada', title: 'Entrada', formatter: 'datetime' },
                            { data: 'hora_salida', title: 'Salida', formatter: 'datetime' },
                            { data: 'tiempo_total', title: 'Tiempo Total', formatter: (value, row) => row.hora_entrada_humano || '' },
                            { data: 'tarifa_hora', title: 'Tarifa/Hora', formatter: (value) => `S/. ${parseFloat(value).toFixed(2)}` },
                            { data: 'monto_total', title: 'Monto Total', formatter: (value) => `S/. ${parseFloat(value).toFixed(2)}` }
                        ],
                        pagination: true,
                        pageSize: 10,
                        searchable: true,
                        searchPlaceholder: 'Buscar historial...'
                    });
                }
            });
            </script>
        </div>
    </div>
</div>
@endsection