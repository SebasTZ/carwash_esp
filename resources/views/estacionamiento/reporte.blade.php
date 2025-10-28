@extends('layouts.app')

@section('title', 'Reporte de Estacionamiento')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">
        Reporte de Estacionamiento
        @if($reporte == 'diario')
            Diario
        @elseif($reporte == 'semanal')
            Semanal
        @elseif($reporte == 'mensual')
            Mensual
        @else
            Personalizado
        @endif
    </h1>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-chart-bar me-1"></i>
            Resumen de Registros
            <div class="float-end">
                @if($reporte == 'personalizado')
                    <a href="{{ route('estacionamiento.export.personalizado', ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin]) }}" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel"></i> Exportar Excel
                    </a>
                @else
                    <a href="{{ route('estacionamiento.export.' . $reporte) }}" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel"></i> Exportar Excel
                    </a>
                @endif
                <a href="{{ route('estacionamiento.index') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
        <div class="card-body">
            @if($reporte == 'personalizado')
                <div class="alert alert-info">
                    Mostrando registros desde {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} 
                    hasta {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
                </div>
            @endif

            <div class="row mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-primary text-white mb-4">
                        <div class="card-body">
                            <h4>{{ $estacionamientos->count() }}</h4>
                            <div>Total de Registros</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-warning text-white mb-4">
                        <div class="card-body">
                            <h4>{{ $estacionamientos->where('estado', 'ocupado')->count() }}</h4>
                            <div>En Uso</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-success text-white mb-4">
                        <div class="card-body">
                            <h4>{{ $estacionamientos->where('estado', 'finalizado')->count() }}</h4>
                            <div>Finalizados</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-info text-white mb-4">
                        <div class="card-body">
                            <h4>S/. {{ number_format($estacionamientos->sum('monto_total'), 2) }}</h4>
                            <div>Total Recaudado</div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="dynamic-table-estacionamiento-reporte"></div>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (window.DynamicTable) {
                    window.DynamicTable.render({
                        target: document.getElementById('dynamic-table-estacionamiento-reporte'),
                        data: @json($estacionamientos),
                        columns: [
                            { data: 'placa', title: 'Placa' },
                            { data: 'cliente', title: 'Cliente', formatter: (value, row) => row.cliente?.persona?.razon_social || '' },
                            { data: 'marca', title: 'Marca' },
                            { data: 'modelo', title: 'Modelo' },
                            { data: 'hora_entrada', title: 'Entrada', formatter: 'datetime' },
                            { data: 'hora_salida', title: 'Salida', formatter: 'datetime' },
                            { data: 'tiempo', title: 'Tiempo', formatter: (value, row) => row.hora_entrada_humano || '' },
                            { data: 'tarifa_hora', title: 'Tarifa/Hora', formatter: (value) => `S/. ${parseFloat(value).toFixed(2)}` },
                            { data: 'monto_total', title: 'Monto Total', formatter: (value) => `S/. ${parseFloat(value).toFixed(2)}` },
                            { data: 'estado', title: 'Estado', formatter: (value) => `<span class='badge rounded-pill bg-${value === 'ocupado' ? 'warning' : 'success'}'>${value}</span>` }
                        ],
                        pagination: true,
                        pageSize: 10,
                        searchable: true,
                        searchPlaceholder: 'Buscar en reporte...'
                    });
                }
            });
            </script>
        </div>
    </div>
</div>
@endsection