@extends('layouts.app')

@section('title', 'Appointment Details')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Appointment Details #{{ $cita->id }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('citas.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            @if($cita->estado != 'completada' && $cita->estado != 'cancelada')
            <a href="{{ route('citas.edit', $cita) }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            @endif
            <a href="{{ route('citas.dashboard') }}" class="btn btn-sm btn-outline-info">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5>Appointment Information</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Client:</strong> {{ $cita->cliente->persona->razon_social }}</p>
                        <p><strong>Document:</strong> {{ $cita->cliente->persona->documento->tipo_documento ?? 'N/A' }} {{ $cita->cliente->persona->numero_documento }}</p>
                        <p><strong>Phone:</strong> {{ $cita->cliente->persona->telefono ?? 'Not registered' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Date:</strong> {{ $cita->fecha->format('d/m/Y') }}</p>
                        <p><strong>Time:</strong> {{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}</p>
                        <p><strong>Status:</strong> 
                            @switch($cita->estado)
                                @case('pendiente')
                                    Pending
                                    @break
                                @case('en_proceso')
                                    In Process
                                    @break
                                @case('completada')
                                    Completed
                                    @break
                                @case('cancelada')
                                    Cancelled
                                    @break
                            @endswitch
                        </p>
                        <p><strong>Queue Position:</strong> {{ $cita->posicion_cola }}</p>
                    </div>
                </div>
                <div class="mb-3">
                    <strong>Notes:</strong>
                    <div class="border rounded p-2 bg-light">{{ $cita->notas ?? 'No notes' }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5>Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-flex flex-column gap-2">
                    <a href="{{ route('citas.index') }}" class="btn btn-secondary">Back to List</a>
                    @if($cita->estado != 'completada' && $cita->estado != 'cancelada')
                    <a href="{{ route('citas.edit', $cita) }}" class="btn btn-primary">Edit Appointment</a>
                    @endif
                    <a href="{{ route('citas.dashboard') }}" class="btn btn-info">Go to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection