@extends('adminlte::page')

@section('title', 'Maintenance Details')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Maintenance Details #{{ $mantenimiento->id }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('panel') }}"><i class="fas fa-home"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('mantenimientos.index') }}">Maintenance</a></li>
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

    <!-- Estado del mantenimiento -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">
                            Current status:
                            @if($mantenimiento->estado == 'recibido')
                                <span class="badge badge-secondary">Received</span>
                            @elseif($mantenimiento->estado == 'en_proceso')
                                <span class="badge badge-primary">In Process</span>
                            @elseif($mantenimiento->estado == 'terminado')
                                <span class="badge badge-warning">Finished</span>
                            @elseif($mantenimiento->estado == 'entregado')
                                <span class="badge badge-success">Delivered</span>
                            @endif
                        </h3>
                        <div>
                            @if($mantenimiento->estado != 'entregado')
                                <div class="btn-group">
                                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Cambiar Estado <span class="caret"></span>
                                    </button>
                                    <div class="dropdown-menu">
                                        @if($mantenimiento->estado != 'recibido')
                                            <form action="{{ route('mantenimientos.cambiarEstado', $mantenimiento->id) }}" method="POST" style="display:inline">
                                                @csrf
                                                <input type="hidden" name="estado" value="recibido">
                                                <button type="submit" class="dropdown-item">Recibido</button>
                                            </form>
                                        @endif
                                        
                                        @if($mantenimiento->estado != 'en_proceso')
                                            <form action="{{ route('mantenimientos.cambiarEstado', $mantenimiento->id) }}" method="POST" style="display:inline">
                                                @csrf
                                                <input type="hidden" name="estado" value="en_proceso">
                                                <button type="submit" class="dropdown-item">En Proceso</button>
                                            </form>
                                        @endif
                                        
                                        @if($mantenimiento->estado != 'terminado')
                                            <form action="{{ route('mantenimientos.cambiarEstado', $mantenimiento->id) }}" method="POST" style="display:inline">
                                                @csrf
                                                <input type="hidden" name="estado" value="terminado">
                                                <button type="submit" class="dropdown-item">Terminado</button>
                                            </form>
                                        @endif
                                        
                                        @if($mantenimiento->estado != 'entregado')
                                            <form action="{{ route('mantenimientos.cambiarEstado', $mantenimiento->id) }}" method="POST" style="display:inline">
                                                @csrf
                                                <input type="hidden" name="estado" value="entregado">
                                                <button type="submit" class="dropdown-item">Entregado</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if(!$mantenimiento->pagado)
                                <button type="button" class="btn btn-success ml-2" data-toggle="modal" data-target="#vincularVentaModal">
                                    <i class="fas fa-money-bill mr-1"></i> Registrar Pago
                                </button>
                            @endif
                            
                            <a href="{{ route('mantenimientos.edit', $mantenimiento->id) }}" class="btn btn-info ml-2">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </a>
                            
                            <a href="{{ route('mantenimientos.index') }}" class="btn btn-secondary ml-2">
                                <i class="fas fa-arrow-left mr-1"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Información del cliente y vehículo -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Client and Vehicle</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <h5>Client Data</h5>
                            <dl class="row">
                                <dt class="col-sm-4">Client:</dt>
                                <dd class="col-sm-8">{{ $mantenimiento->cliente->persona->razon_social }}</dd>
                                
                                <dt class="col-sm-4">{{ $mantenimiento->cliente->persona->documento->tipo_documento }}:</dt>
                                <dd class="col-sm-8">{{ $mantenimiento->cliente->persona->numero_documento }}</dd>
                                
                                <dt class="col-sm-4">Phone:</dt>
                                <dd class="col-sm-8">{{ $mantenimiento->cliente->persona->telefono ?: 'Not available' }}</dd>
                            </dl>
                            
                            <hr>
                            
                            <h5>Vehicle Data</h5>
                            <dl class="row">
                                <dt class="col-sm-4">Plate:</dt>
                                <dd class="col-sm-8"><span class="badge badge-dark">{{ $mantenimiento->placa }}</span></dd>
                                
                                <dt class="col-sm-4">Model:</dt>
                                <dd class="col-sm-8">{{ $mantenimiento->modelo }}</dd>
                                
                                <dt class="col-sm-4">Type:</dt>
                                <dd class="col-sm-8">{{ $mantenimiento->tipo_vehiculo }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Detalles del servicio -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Service Details</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-6">Service Type:</dt>
                        <dd class="col-sm-6">{{ $mantenimiento->tipo_servicio }}</dd>
                        
                        <dt class="col-sm-6">Entry Date:</dt>
                        <dd class="col-sm-6">{{ $mantenimiento->fecha_ingreso->format('d/m/Y') }}</dd>
                        
                        <dt class="col-sm-6">Estimated Delivery Date:</dt>
                        <dd class="col-sm-6">
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
                                Not specified
                            @endif
                        </dd>
                        
                        <dt class="col-sm-6">Actual Delivery Date:</dt>
                        <dd class="col-sm-6">
                            @if($mantenimiento->fecha_entrega_real)
                                {{ \Carbon\Carbon::parse($mantenimiento->fecha_entrega_real)->format('d/m/Y') }}
                            @else
                                Pending
                            @endif
                        </dd>
                        
                        <dt class="col-sm-6">Responsible Mechanic:</dt>
                        <dd class="col-sm-6">{{ $mantenimiento->mecanico_responsable ?: 'Not assigned' }}</dd>
                        
                        <dt class="col-sm-6">Payment Status:</dt>
                        <dd class="col-sm-6">
                            @if($mantenimiento->pagado)
                                <span class="badge badge-success">Pagado</span>
                                @if($mantenimiento->venta_id)
                                    <a href="{{ route('ventas.show', $mantenimiento->venta_id) }}" class="badge badge-info" title="Ver venta">
                                        Ver venta #{{ $mantenimiento->venta_id }}
                                    </a>
                                @endif
                            @else
                                <span class="badge badge-danger">Pending payment</span>
                            @endif
                        </dd>
                        
                        <dt class="col-sm-6">Estimated Cost:</dt>
                        <dd class="col-sm-6">
                            @if($mantenimiento->costo_estimado)
                                S/ {{ number_format($mantenimiento->costo_estimado, 2) }}
                            @else
                                Not specified
                            @endif
                        </dd>
                        
                        <dt class="col-sm-6">Final Cost:</dt>
                        <dd class="col-sm-6">
                            @if($mantenimiento->costo_final)
                                S/ {{ number_format($mantenimiento->costo_final, 2) }}
                            @else
                                Pending
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Descripción del trabajo y observaciones -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Work Description</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <h5>Diagnosis / Work to be Done</h5>
                            <div class="p-3 bg-light border rounded">
                                {!! nl2br(e($mantenimiento->descripcion_trabajo)) !!}
                            </div>
                            
                            @if($mantenimiento->observaciones)
                                <h5 class="mt-4">Observaciones Adicionales</h5>
                                <div class="p-3 bg-light border rounded">
                                    {!! nl2br(e($mantenimiento->observaciones)) !!}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para vincular venta -->
<div class="modal fade" id="vincularVentaModal" tabindex="-1" role="dialog" aria-labelledby="vincularVentaModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="vincularVentaModalLabel">Register Payment</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>To register the payment, enter the sale ID associated with this maintenance service.</p>
                <form action="{{ route('mantenimientos.vincularVenta', $mantenimiento->id) }}" method="POST" id="formVincularVenta">
                    @csrf
                    <div class="form-group">
                        <label for="venta_id">Sale ID</label>
                        <input type="number" name="venta_id" id="venta_id" class="form-control" min="1" required>
                        <small class="form-text text-muted">Enter the sale number that contains the payment for this service.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" form="formVincularVenta" class="btn btn-primary">Link Sale</button>
            </div>
        </div>
    </div>
</div>
@stop