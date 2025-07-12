@extends('layouts.app')

@section('title', 'Wash Details')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Wash Process Details</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('control.lavados') }}">Wash Control</a></li>
        <li class="breadcrumb-item active">Details</li>
    </ol>
</div>

<div class="container-lg mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <strong>Process Information</strong>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Receipt</dt>
                        <dd class="col-sm-7">{{ $lavado->venta->numero_comprobante ?? '-' }}</dd>

                        <dt class="col-sm-5">Customer</dt>
                        <dd class="col-sm-7">{{ $lavado->cliente->persona->razon_social ?? '-' }}</dd>

                        <dt class="col-sm-5">Washer</dt>
                        <dd class="col-sm-7">{{ $lavado->lavador ? $lavado->lavador->nombre : '-' }}</dd>

                        <dt class="col-sm-5">Vehicle Type</dt>
                        <dd class="col-sm-7">{{ $lavado->tipoVehiculo ? $lavado->tipoVehiculo->nombre : '-' }}</dd>

                        <dt class="col-sm-5">Arrival Time</dt>
                        <dd class="col-sm-7">
                            {{ $lavado->hora_llegada ? \Carbon\Carbon::parse($lavado->hora_llegada)->format('d/m/Y H:i') : '-' }}
                        </dd>

                        <dt class="col-sm-5">Wash Start</dt>
                        <dd class="col-sm-7">
                            {{ $lavado->inicio_lavado ? \Carbon\Carbon::parse($lavado->inicio_lavado)->format('d/m/Y H:i') : '-' }}
                        </dd>

                        <dt class="col-sm-5">Wash End</dt>
                        <dd class="col-sm-7">
                            {{ $lavado->fin_lavado ? \Carbon\Carbon::parse($lavado->fin_lavado)->format('d/m/Y H:i') : '-' }}
                        </dd>

                        <dt class="col-sm-5">Interior Start</dt>
                        <dd class="col-sm-7">
                            {{ $lavado->inicio_interior ? \Carbon\Carbon::parse($lavado->inicio_interior)->format('d/m/Y H:i') : '-' }}
                        </dd>

                        <dt class="col-sm-5">Interior End</dt>
                        <dd class="col-sm-7">
                            {{ $lavado->fin_interior ? \Carbon\Carbon::parse($lavado->fin_interior)->format('d/m/Y H:i') : '-' }}
                        </dd>

                        <dt class="col-sm-5">Final Time</dt>
                        <dd class="col-sm-7">
                            {{ $lavado->hora_final ? \Carbon\Carbon::parse($lavado->hora_final)->format('d/m/Y H:i') : '-' }}
                        </dd>

                        <dt class="col-sm-5">Total Time</dt>
                        <dd class="col-sm-7">
                            @if($lavado->hora_final && $lavado->hora_llegada)
                                {{ \Carbon\Carbon::parse($lavado->hora_llegada)->diffInMinutes(\Carbon\Carbon::parse($lavado->hora_final)) }} min
                            @else
                                -
                            @endif
                        </dd>

                        <dt class="col-sm-5">Status</dt>
                        <dd class="col-sm-7">{{ $lavado->estado }}</dd>
                    </dl>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('control.lavados') }}" class="btn btn-secondary">Back to list</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
