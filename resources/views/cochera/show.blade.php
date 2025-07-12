@extends('adminlte::page')

@section('title', 'Detalle de Vehículo en Cochera')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Detalle de Vehículo en Cochera</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('panel') }}"><i class="fas fa-home"></i> Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('cocheras.index') }}">Cochera</a></li>
                <li class="breadcrumb-item active">Detalles</li>
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
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        Información del Vehículo
                        @if($cochera->estado == 'activo')
                        <span class="badge badge-success ml-2">Activo</span>
                        @elseif($cochera->estado == 'finalizado')
                        <span class="badge badge-secondary ml-2">Finalizado</span>
                        @elseif($cochera->estado == 'cancelado')
                        <span class="badge badge-danger ml-2">Cancelado</span>
                        @endif
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl>
                                <dt>Placa</dt>
                                <dd><span class="badge badge-dark">{{ $cochera->placa }}</span></dd>

                                <dt>Modelo</dt>
                                <dd>{{ $cochera->modelo }}</dd>

                                <dt>Color</dt>
                                <dd>{{ $cochera->color }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl>
                                <dt>Tipo de Vehículo</dt>
                                <dd>{{ $cochera->tipo_vehiculo }}</dd>

                                <dt>Ubicación</dt>
                                <dd>{{ $cochera->ubicacion ?: 'No especificada' }}</dd>
                            </dl>
                        </div>
                    </div>

                    <div class="alert alert-light border">
                        <dt>Observaciones</dt>
                        <dd>{{ $cochera->observaciones ?: 'Sin observaciones' }}</dd>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Cliente</h3>
                </div>
                <div class="card-body">
                    <dl>
                        <dt>Nombre / Razón Social</dt>
                        <dd>{{ $cochera->cliente->persona->razon_social }}</dd>

                        <dt>{{ $cochera->cliente->persona->documento->tipo_documento }}</dt>
                        <dd>{{ $cochera->cliente->persona->numero_documento }}</dd>

                        <dt>Teléfono</dt>
                        <dd>{{ $cochera->cliente->persona->telefono ?: 'No disponible' }}</dd>

                        <dt>Dirección</dt>
                        <dd>{{ $cochera->cliente->persona->direccion ?: 'No disponible' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Detalles de Estacionamiento</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl>
                                <dt>Fecha y Hora de Ingreso</dt>
                                <dd>{{ $cochera->fecha_ingreso->format('d/m/Y H:i') }}</dd>

                                <dt>Fecha y Hora de Salida</dt>
                                <dd>
                                    @if($cochera->fecha_salida)
                                    {{ \Carbon\Carbon::parse($cochera->fecha_salida)->format('d/m/Y H:i') }}
                                    @else
                                    <em>No registrada aún</em>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl>
                                <dt>Tarifa por Hora</dt>
                                <dd>S/ {{ number_format($cochera->tarifa_hora, 2) }}</dd>

                                <dt>Tarifa por Día</dt>
                                <dd>
                                    @if($cochera->tarifa_dia)
                                    S/ {{ number_format($cochera->tarifa_dia, 2) }}
                                    @else
                                    <em>No aplica</em>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>

                    @php
                        if ($cochera->estado == 'activo') {
                            $fechaInicio = \Carbon\Carbon::parse($cochera->fecha_ingreso);
                            $fechaFin = now();
                            $diff = $fechaInicio->diff($fechaFin);
                            
                            $dias = $diff->days;
                            $horas = $diff->h;
                            $minutos = $diff->i;
                            
                            $tiempoEstadia = '';
                            if ($dias > 0) {
                            $tiempoEstadia .= $dias . ' día(s) ';
                            }
                            $tiempoEstadia .= $horas . ' hora(s) ' . $minutos . ' minuto(s)';
                        } else if ($cochera->fecha_salida) {
                            $fechaInicio = \Carbon\Carbon::parse($cochera->fecha_ingreso);
                            $fechaFin = \Carbon\Carbon::parse($cochera->fecha_salida);
                            $diff = $fechaInicio->diff($fechaFin);
                            
                            $dias = $diff->days;
                            $horas = $diff->h;
                            $minutos = $diff->i;
                            
                            $tiempoEstadia = '';
                            if ($dias > 0) {
                            $tiempoEstadia .= $dias . ' día(s) ';
                            }
                            $tiempoEstadia .= $horas . ' hora(s) ' . $minutos . ' minuto(s)';
                        } else {
                            $tiempoEstadia = 'No disponible';
                        }
                    @endphp

                    <div class="alert {{ $cochera->estado == 'activo' ? 'alert-primary' : 'alert-secondary' }}">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Tiempo de Estacionamiento</h5>
                                <p class="h4">{{ $tiempoEstadia }}</p>
                            </div>
                            <div class="col-md-6">
                                <h5>Monto a Pagar</h5>
                                <p class="h3">S/ {{ number_format($montoActualizado, 2) }}</p>
                            </div>
                        </div>
                    </div>

                    @if($cochera->estado == 'activo')
                    <button type="button" class="btn btn-lg btn-success btn-block mt-3" data-toggle="modal" data-target="#finalizarModal">
                        <i class="fas fa-check mr-2"></i> Finalizar Estacionamiento
                    </button>
                    @endif
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('cocheras.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Volver
                        </a>
                        <div>
                            @if($cochera->estado == 'activo')
                            <a href="{{ route('cocheras.edit', $cochera->id) }}" class="btn btn-primary">
                                <i class="fas fa-edit mr-1"></i> Editar
                            </a>
                            <form action="{{ route('cocheras.destroy', $cochera->id) }}" method="POST" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('¿Está seguro que desea eliminar este registro?')">
                                    <i class="fas fa-trash mr-1"></i> Eliminar
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para finalizar estacionamiento -->
@if($cochera->estado == 'activo')
<div class="modal fade" id="finalizarModal" tabindex="-1" role="dialog" aria-labelledby="finalizarModalLabel" aria-hidden="true">
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
                        <p class="mb-1">Tiempo: <strong>{{ $tiempoEstadia }}</strong></p>
                        <p class="mb-1">Monto a pagar: <strong>S/ {{ number_format($montoActualizado, 2) }}</strong></p>
                    </div>

                    <p class="text-muted">Al confirmar, se registrará la fecha y hora actual como momento de salida y se calculará el monto final.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Finalizar y Cobrar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@stop