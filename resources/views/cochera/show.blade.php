@extends('adminlte::page')

@section('title', 'Garage Vehicle Details')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Garage Vehicle Details</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('panel') }}"><i class="fas fa-home"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('cocheras.index') }}">Garage</a></li>
                <li class="breadcrumb-item active">Details</li>
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
                        Vehicle Information
                        @if($cochera->estado == 'activo')
                        <span class="badge badge-success ml-2">Active</span>
                        @elseif($cochera->estado == 'finalizado')
                        <span class="badge badge-secondary ml-2">Finished</span>
                        @elseif($cochera->estado == 'cancelado')
                        <span class="badge badge-danger ml-2">Cancelled</span>
                        @endif
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl>
                                <dt>Plate</dt>
                                <dd><span class="badge badge-dark">{{ $cochera->placa }}</span></dd>

                                <dt>Model</dt>
                                <dd>{{ $cochera->modelo }}</dd>

                                <dt>Color</dt>
                                <dd>{{ $cochera->color }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl>
                                <dt>Vehicle Type</dt>
                                <dd>{{ $cochera->tipo_vehiculo }}</dd>

                                <dt>Location</dt>
                                <dd>{{ $cochera->ubicacion ?: 'Not specified' }}</dd>
                            </dl>
                        </div>
                    </div>

                    <div class="alert alert-light border">
                        <dt>Observations</dt>
                        <dd>{{ $cochera->observaciones ?: 'No observations' }}</dd>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Client</h3>
                </div>
                <div class="card-body">
                    <dl>
                        <dt>Name / Business Name</dt>
                        <dd>{{ $cochera->cliente->persona->razon_social }}</dd>

                        <dt>{{ $cochera->cliente->persona->documento->tipo_documento }}</dt>
                        <dd>{{ $cochera->cliente->persona->numero_documento }}</dd>

                        <dt>Phone</dt>
                        <dd>{{ $cochera->cliente->persona->telefono ?: 'Not available' }}</dd>

                        <dt>Address</dt>
                        <dd>{{ $cochera->cliente->persona->direccion ?: 'Not available' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Parking Details</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl>
                                <dt>Entry Date and Time</dt>
                                <dd>{{ $cochera->fecha_ingreso->format('d/m/Y H:i') }}</dd>

                                <dt>Exit Date and Time</dt>
                                <dd>
                                    @if($cochera->fecha_salida)
                                    {{ \Carbon\Carbon::parse($cochera->fecha_salida)->format('d/m/Y H:i') }}
                                    @else
                                    <em>Not registered yet</em>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl>
                                <dt>Rate per Hour</dt>
                                <dd>S/ {{ number_format($cochera->tarifa_hora, 2) }}</dd>

                                <dt>Rate per Day</dt>
                                <dd>
                                    @if($cochera->tarifa_dia)
                                    S/ {{ number_format($cochera->tarifa_dia, 2) }}
                                    @else
                                    <em>Not applicable</em>
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
                                $tiempoEstadia .= $dias . ' day(s) ';
                            }
                            $tiempoEstadia .= $horas . ' hour(s) ' . $minutos . ' minute(s)';
                        } else if ($cochera->fecha_salida) {
                            $fechaInicio = \Carbon\Carbon::parse($cochera->fecha_ingreso);
                            $fechaFin = \Carbon\Carbon::parse($cochera->fecha_salida);
                            $diff = $fechaInicio->diff($fechaFin);
                            
                            $dias = $diff->days;
                            $horas = $diff->h;
                            $minutos = $diff->i;
                            
                            $tiempoEstadia = '';
                            if ($dias > 0) {
                                $tiempoEstadia .= $dias . ' day(s) ';
                            }
                            $tiempoEstadia .= $horas . ' hour(s) ' . $minutos . ' minute(s)';
                        } else {
                            $tiempoEstadia = 'Not available';
                        }
                    @endphp

                    <div class="alert {{ $cochera->estado == 'activo' ? 'alert-primary' : 'alert-secondary' }}">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Parking Time</h5>
                                <p class="h4">{{ $tiempoEstadia }}</p>
                            </div>
                            <div class="col-md-6">
                                <h5>Amount to Pay</h5>
                                <p class="h3">S/ {{ number_format($montoActualizado, 2) }}</p>
                            </div>
                        </div>
                    </div>

                    @if($cochera->estado == 'activo')
                    <button type="button" class="btn btn-lg btn-success btn-block mt-3" data-toggle="modal" data-target="#finalizarModal">
                        <i class="fas fa-check mr-2"></i> Finish Parking
                    </button>
                    @endif
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('cocheras.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Back
                        </a>
                        <div>
                            @if($cochera->estado == 'activo')
                            <a href="{{ route('cocheras.edit', $cochera->id) }}" class="btn btn-primary">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </a>
                            <form action="{{ route('cocheras.destroy', $cochera->id) }}" method="POST" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this record?')">
                                    <i class="fas fa-trash mr-1"></i> Delete
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
                <h5 class="modal-title" id="finalizarModalLabel">Finish Parking</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('cocheras.finalizar', $cochera->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Do you want to finish the parking for the vehicle <strong>{{ $cochera->placa }}</strong>?</p>
                    
                    <div class="alert alert-info">
                        <p class="mb-1">Time: <strong>{{ $tiempoEstadia }}</strong></p>
                        <p class="mb-1">Amount to pay: <strong>S/ {{ number_format($montoActualizado, 2) }}</strong></p>
                    </div>

                    <p class="text-muted">By confirming, the current date and time will be registered as the exit moment and the final amount will be calculated.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Finish and Charge</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@stop