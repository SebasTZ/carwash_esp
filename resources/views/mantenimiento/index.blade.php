@extends('adminlte::page')

@section('title', 'Mantenimiento de Vehículos')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Mantenimiento de Vehículos</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('panel') }}"><i class="fas fa-home"></i> Inicio</a></li>
                <li class="breadcrumb-item active">Mantenimiento</li>
            </ol>
        </div>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Servicios de Mantenimiento</h3>
                        <div>
                            <a href="{{ route('mantenimientos.create') }}" class="btn btn-success">
                                <i class="fas fa-plus-circle"></i> Nuevo Mantenimiento
                            </a>
                            <a href="{{ route('mantenimientos.reportes') }}" class="btn btn-info ml-2">
                                <i class="fas fa-chart-bar"></i> Reportes
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <form action="{{ route('mantenimientos.index') }}" method="GET" class="form-inline">
                                <div class="form-group">
                                    <label class="mr-2">Filtrar por estado: </label>
                                    <select name="estado" class="form-control" onchange="this.form.submit()">
                                        <option value="recibido" {{ request('estado') == 'recibido' ? 'selected' : '' }}>Recibido</option>
                                        <option value="en_proceso" {{ request('estado') == 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                                        <option value="terminado" {{ request('estado') == 'terminado' ? 'selected' : '' }}>Terminado</option>
                                        <option value="entregado" {{ request('estado') == 'entregado' ? 'selected' : '' }}>Entregado</option>
                                        <option value="todos" {{ request('estado') == 'todos' ? 'selected' : '' }}>Todos</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Placa</th>
                                    <th>Cliente</th>
                                    <th>Vehículo</th>
                                    <th>Tipo de Servicio</th>
                                    <th>Ingreso</th>
                                    <th>Entrega Est.</th>
                                    <th>Estado</th>
                                    <th>Pagado</th>
                                    <th>Costo</th>
                                    <th width="120px">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($mantenimientos as $mantenimiento)
                                <tr>
                                    <td>{{ $mantenimiento->id }}</td>
                                    <td>
                                        <span class="badge badge-dark">{{ $mantenimiento->placa }}</span>
                                    </td>
                                    <td>{{ $mantenimiento->cliente->persona->razon_social }}</td>
                                    <td>{{ $mantenimiento->modelo }} ({{ $mantenimiento->tipo_vehiculo }})</td>
                                    <td>{{ $mantenimiento->tipo_servicio }}</td>
                                    <td>{{ $mantenimiento->fecha_ingreso->format('d/m/Y') }}</td>
                                    <td>
                                        @if($mantenimiento->fecha_entrega_estimada)
                                            {{ \Carbon\Carbon::parse($mantenimiento->fecha_entrega_estimada)->format('d/m/Y') }}
                                            
                                            @php
                                                $diasRestantes = now()->diffInDays(\Carbon\Carbon::parse($mantenimiento->fecha_entrega_estimada), false);
                                            @endphp
                                            
                                            @if($diasRestantes < 0 && $mantenimiento->estado != 'entregado')
                                                <span class="badge badge-danger">Atrasado {{ abs($diasRestantes) }} días</span>
                                            @elseif($diasRestantes == 0 && $mantenimiento->estado != 'entregado')
                                                <span class="badge badge-warning">Hoy</span>
                                            @elseif($diasRestantes > 0 && $mantenimiento->estado != 'entregado')
                                                <span class="badge badge-info">Faltan {{ $diasRestantes }} días</span>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <div id="dynamicTableMantenimiento"></div>
                                        <script type="module">
                                            import DynamicTable from '/js/components/DynamicTable.js';
                                            document.addEventListener('DOMContentLoaded', function() {
                                                new DynamicTable({
                                                    elementId: 'dynamicTableMantenimiento',
                                                    columns: [
                                                        { key: 'id', label: 'ID' },
                                                        { key: 'placa', label: 'Placa', render: row => `<span class='badge badge-dark'>${row.placa}</span>` },
                                                        { key: 'cliente', label: 'Cliente' },
                                                        { key: 'vehiculo', label: 'Vehículo' },
                                                        { key: 'tipo_servicio', label: 'Tipo de Servicio' },
                                                        { key: 'fecha_ingreso', label: 'Ingreso' },
                                                        { key: 'fecha_entrega_estimada', label: 'Entrega Est.' },
                                                        { key: 'estado', label: 'Estado', render: row => row.estado_badge },
                                                        { key: 'pagado', label: 'Pagado', render: row => row.pagado_badge },
                                                        { key: 'costo', label: 'Costo' },
                                                        { key: 'acciones', label: 'Acciones', render: row => row.acciones, width: 120 }
                                                    ],
                                                    dataUrl: '/api/mantenimientos',
                                                    rowClass: row => row.estado === 'atrasado' ? 'table-danger' : '',
                                                    pagination: true,
                                                    preserveQuery: true
                                                });
                                            });
                                        </script>