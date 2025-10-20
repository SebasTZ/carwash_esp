@extends('adminlte::page')

@section('title', 'Cochera | Estacionamiento')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Estacionamiento / Cochera</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('panel') }}"><i class="fas fa-home"></i> Inicio</a></li>
                <li class="breadcrumb-item active">Cochera</li>
            </ol>
        </div>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Vehículos en Estacionamiento</h3>
                        <div>
                            <a href="{{ route('cocheras.create') }}" class="btn btn-success">
                                <i class="fas fa-plus-circle"></i> Registrar Vehículo
                            </a>
                            <a href="{{ route('cocheras.reportes') }}" class="btn btn-info ml-2">
                                <i class="fas fa-chart-bar"></i> Reportes
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <form action="{{ route('cocheras.index') }}" method="GET" class="form-inline">
                                <div class="form-group">
                                    <label class="mr-2">Filtrar por estado: </label>
                                    <select name="estado" class="form-control" onchange="this.form.submit()">
                                        <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                                        <option value="finalizado" {{ request('estado') == 'finalizado' ? 'selected' : '' }}>Finalizado</option>
                                        <option value="cancelado" {{ request('estado') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                                        <option value="todos" {{ request('estado') == 'todos' ? 'selected' : '' }}>Todos</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-info p-2 mb-0">
                                <small>
                                    <i class="fas fa-info-circle"></i> Los vehículos con más de 5 días en la cochera se marcan en amarillo.
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Placa</th>
                                    <th>Cliente</th>
                                    <th>Modelo</th>
                                    <th>Ingreso</th>
                                    <th>Tiempo</th>
                                    <th>Ubicación</th>
                                    <th>Estado</th>
                                    <th>Monto Actual</th>
                                    <th width="160px">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($cocheras as $cochera)
                                @php
                                    $tiempoEstadia = now()->diffInDays($cochera->fecha_ingreso);
                                    $estadiaProlongada = $tiempoEstadia >= 5;
                                    
                                    if ($cochera->estado == 'activo') {
                                        $montoActual = $cochera->calcularMonto();
                                    } else {
                                        $montoActual = $cochera->monto_total;
                                    }
                                    
                                    // Formato del tiempo transcurrido
                                    if ($cochera->estado == 'activo') {
                                        $fechaInicio = \Carbon\Carbon::parse($cochera->fecha_ingreso);
                                        $fechaFin = now();
                                        $diff = $fechaInicio->diff($fechaFin);
                                        
                                        if ($diff->days > 0) {
                                            $tiempoFormateado = $diff->days . 'd ' . $diff->h . 'h';
                                        } else {
                                            $tiempoFormateado = $diff->h . 'h ' . $diff->i . 'm';
                                        }
                                    } else if ($cochera->fecha_salida) {
                                        $fechaInicio = \Carbon\Carbon::parse($cochera->fecha_ingreso);
                                        $fechaFin = \Carbon\Carbon::parse($cochera->fecha_salida);
                                        $diff = $fechaInicio->diff($fechaFin);
                                        
                                        if ($diff->days > 0) {
                                            $tiempoFormateado = $diff->days . 'd ' . $diff->h . 'h';
                                        } else {
                                            $tiempoFormateado = $diff->h . 'h ' . $diff->i . 'm';
                                        }
                                    } else {
                                        $tiempoFormateado = "-";
                                    }
                                @endphp
                                <tr class="{{ $estadiaProlongada && $cochera->estado == 'activo' ? 'table-warning' : '' }}">
                                    <td>{{ $cochera->id }}</td>
                                    <td>
                                        <span class="badge badge-dark">{{ $cochera->placa }}</span>
                                    </td>
                                    <td>{{ $cochera->cliente->persona->razon_social }}</td>
                                    <td>{{ $cochera->modelo }} ({{ $cochera->color }})</td>
                                    <td>{{ $cochera->fecha_ingreso->format('d/m/Y H:i') }}</td>
                                    <td>{{ $tiempoFormateado }}</td>
                                    <td>{{ $cochera->ubicacion ?: 'No especificada' }}</td>
                                    <td>
                                        @if($cochera->estado == 'activo')
                                            <span class="badge badge-success">Activo</span>
                                        @elseif($cochera->estado == 'finalizado')
                                            <span class="badge badge-secondary">Finalizado</span>
                                        @elseif($cochera->estado == 'cancelado')
                                            <span class="badge badge-danger">Cancelado</span>
                                        @endif
                                    </td>
                                    <td>S/ {{ number_format($montoActual, 2) }}</td>
                                    <td>
                                        <a href="{{ route('cocheras.show', $cochera->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if($cochera->estado == 'activo')
                                            <a href="{{ route('cocheras.edit', $cochera->id) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#finalizarModal{{ $cochera->id }}">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            
                                            <form action="{{ route('cocheras.destroy', $cochera->id) }}" method="POST" style="display:inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro que desea eliminar este registro?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <a href="{{ route('cocheras.edit', $cochera->id) }}" class="btn btn-sm btn-secondary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <form action="{{ route('cocheras.destroy', $cochera->id) }}" method="POST" style="display:inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro de que desea eliminar este registro?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                
                                <!-- Modal para finalizar estacionamiento -->
                                <div class="modal fade" id="finalizarModal{{ $cochera->id }}" tabindex="-1" role="dialog" aria-labelledby="finalizarModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="finalizarModalLabel">Finalizar Estacionamiento</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form action="{{ route('cocheras.finalizar', $cochera->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <p>¿Desea finalizar el estacionamiento para el vehículo <strong>{{ $cochera->placa }}</strong>?</p>
                                                    
                                                    <div class="alert alert-info">
                                                        <p class="mb-1">Tiempo: <strong>{{ $tiempoFormateado }}</strong></p>
                                                        <p class="mb-1">Monto actual a pagar: <strong>S/ {{ number_format($montoActual, 2) }}</strong></p>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-success">Finalizar</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center">No hay registros disponibles</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación usando componente con preservación de filtros -->
                    <x-pagination-info 
                        :paginator="$cocheras" 
                        entity="registros de cochera" 
                        :preserve-query="true" 
                    />
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<!-- DataTables removido para usar paginación de Laravel -->
@stop

@section('js')
<!-- DataTables removido para usar paginación de Laravel -->
@stop