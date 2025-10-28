@extends('layouts.app')

@section('title', 'Estacionamiento')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Estacionamiento</h1>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-car me-1"></i>
            Vehículos Estacionados
            @can('crear-estacionamiento')
            <a class="btn btn-success btn-sm float-end" href="{{ route('estacionamiento.create') }}">
                <i class="fas fa-plus"></i> Registrar Entrada
            </a>
            @endcan
            <a href="{{ route('estacionamiento.historial') }}" class="btn btn-info btn-sm float-end me-2">
                <i class="fas fa-history"></i> Ver Historial
            </a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div id="dynamic-table-estacionamiento"></div>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (window.DynamicTable) {
                    window.DynamicTable.render({
                        target: document.getElementById('dynamic-table-estacionamiento'),
                        data: @json($estacionamientos->items()),
                        columns: [
                            { data: 'placa', title: 'Placa' },
                            { data: 'cliente', title: 'Cliente', formatter: (value, row) => row.cliente?.persona?.razon_social || '' },
                            { data: 'marca', title: 'Marca' },
                            { data: 'modelo', title: 'Modelo' },
                            { data: 'telefono', title: 'Contacto' },
                            { data: 'hora_entrada', title: 'Hora de Entrada', formatter: 'datetime' },
                            { data: 'tiempo', title: 'Tiempo', formatter: (value, row) => row.hora_entrada_humano || '' },
                            { data: 'tarifa_hora', title: 'Tarifa/Hora', formatter: (value) => `S/. ${parseFloat(value).toFixed(2)}` },
                        ],
                        showActions: true,
                        actionsConfig: {
                            custom: [
                                {
                                    label: 'Registrar Salida',
                                    class: 'btn btn-warning btn-sm',
                                    icon: 'fas fa-sign-out-alt',
                                    callback: (row, data) => {
                                        window.location.href = `/estacionamiento/${data.id}/salida`;
                                    }
                                },
                                {
                                    label: 'Eliminar',
                                    class: 'btn btn-danger btn-sm',
                                    icon: 'fas fa-trash',
                                    callback: (row, data) => {
                                        if (confirm('¿Está seguro de eliminar este registro?')) {
                                            window.location.href = `/estacionamiento/${data.id}/eliminar`;
                                        }
                                    },
                                    show: @can('eliminar-estacionamiento') true @else false @endcan
                                }
                            ]
                        },
                        pagination: true,
                        pageSize: 10,
                        searchable: true,
                        searchPlaceholder: 'Buscar vehículo...'
                    });
                }
            });
            </script>
        </div>
    </div>
</div>
@endsection
@push('js')
@vite(['resources/js/modules/EstacionamientoManager.js'])
@endpush
