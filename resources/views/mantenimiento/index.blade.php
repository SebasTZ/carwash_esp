@extends('adminlte::page')

@section('title', 'Vehicle Maintenance')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Vehicle Maintenance</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('panel') }}"><i class="fas fa-home"></i> Home</a></li>
                <li class="breadcrumb-item active">Maintenance</li>
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
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Maintenance Services</h3>
                        <div>
                            <a href="{{ route('mantenimientos.create') }}" class="btn btn-success">
                                <i class="fas fa-plus-circle"></i> New Maintenance
                            </a>
                            <a href="{{ route('mantenimientos.reportes') }}" class="btn btn-info ml-2">
                                <i class="fas fa-chart-bar"></i> Reports
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <form action="{{ route('mantenimientos.index') }}" method="GET" class="form-inline">
                                <div class="form-group">
                                    <label class="mr-2">Filter by status: </label>
                                    <select name="estado" class="form-control" onchange="this.form.submit()">
                                        <option value="recibido" {{ request('estado') == 'recibido' ? 'selected' : '' }}>Received</option>
                                        <option value="en_proceso" {{ request('estado') == 'en_proceso' ? 'selected' : '' }}>In Process</option>
                                        <option value="terminado" {{ request('estado') == 'terminado' ? 'selected' : '' }}>Finished</option>
                                        <option value="entregado" {{ request('estado') == 'entregado' ? 'selected' : '' }}>Delivered</option>
                                        <option value="todos" {{ request('estado') == 'todos' ? 'selected' : '' }}>All</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table id="tabla-mantenimientos" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Plate</th>
                                    <th>Client</th>
                                    <th>Vehicle</th>
                                    <th>Service Type</th>
                                    <th>Entry</th>
                                    <th>Est. Delivery</th>
                                    <th>Status</th>
                                    <th>Paid</th>
                                    <th>Cost</th>
                                    <th width="120px">Actions</th>
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
                                                <span class="badge badge-danger">Late {{ abs($diasRestantes) }} days</span>
                                            @elseif($diasRestantes == 0 && $mantenimiento->estado != 'entregado')
                                                <span class="badge badge-warning">Today</span>
                                            @elseif($diasRestantes > 0 && $mantenimiento->estado != 'entregado')
                                                <span class="badge badge-info">{{ $diasRestantes }} days</span>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($mantenimiento->estado == 'recibido')
                                            <span class="badge badge-secondary">Received</span>
                                        @elseif($mantenimiento->estado == 'en_proceso')
                                            <span class="badge badge-primary">In Process</span>
                                        @elseif($mantenimiento->estado == 'terminado')
                                            <span class="badge badge-warning">Finished</span>
                                        @elseif($mantenimiento->estado == 'entregado')
                                            <span class="badge badge-success">Delivered</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($mantenimiento->pagado)
                                            <span class="badge badge-success">Paid</span>
                                            @if($mantenimiento->venta_id)
                                                <a href="{{ route('ventas.show', $mantenimiento->venta_id) }}" class="badge badge-info" title="View sale">
                                                    #{{ $mantenimiento->venta_id }}
                                                </a>
                                            @endif
                                        @else
                                            <span class="badge badge-danger">Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($mantenimiento->costo_final)
                                            S/ {{ number_format($mantenimiento->costo_final, 2) }}
                                        @elseif($mantenimiento->costo_estimado)
                                            S/ {{ number_format($mantenimiento->costo_estimado, 2) }} <small class="text-muted">(Est.)</small>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('mantenimientos.show', $mantenimiento->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if($mantenimiento->estado != 'entregado')
                                            <a href="{{ route('mantenimientos.edit', $mantenimiento->id) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <form action="{{ route('mantenimientos.destroy', $mantenimiento->id) }}" method="POST" style="display:inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this record?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" class="text-center">No records available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/dataTables.bootstrap4.min.css">
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/dataTables.bootstrap4.min.js"></script>
<script>
    $(document).ready(function() {
        $('#tabla-mantenimientos').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/English.json"
            },
            "order": [[0, "desc"]],
            "pageLength": 10
        });
    });
</script>
@stop