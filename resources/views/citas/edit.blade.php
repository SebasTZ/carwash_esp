@extends('layouts.app')

@section('title', 'Edit Appointment')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Appointment #{{ $cita->id }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group mr-2">
            <a href="{{ route('citas.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
</div>

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="card">
    <div class="card-header">Edit Form</div>
    <div class="card-body">
        <form action="{{ route('citas.update', $cita) }}" method="POST">
            @csrf
            @method('PUT')
            
            <!-- Client (not editable) -->
            <div class="mb-4">
                <label class="form-label">Client</label>
                <input type="text" class="form-control" value="{{ $cita->cliente->persona->razon_social }} - {{ $cita->cliente->persona->numero_documento }}" disabled>
                <input type="hidden" name="cliente_id" value="{{ $cita->cliente_id }}">
                <small class="form-text text-muted">The client cannot be changed.</small>
            </div>
            
            <!-- Editable fields: date, time, and notes -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="fecha" class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('fecha') is-invalid @enderror" id="fecha" name="fecha" value="{{ old('fecha', $cita->fecha->format('Y-m-d')) }}" required>
                    @error('fecha')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6">
                    <label for="hora" class="form-label">Time <span class="text-danger">*</span></label>
                    <input type="time" class="form-control @error('hora') is-invalid @enderror" id="hora" name="hora" value="{{ old('hora', $cita->hora) }}" required>
                    @error('hora')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="notas" class="form-label">Notes</label>
                <textarea class="form-control @error('notas') is-invalid @enderror" id="notas" name="notas" rows="3">{{ old('notas', $cita->notas) }}</textarea>
                @error('notas')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Appointment
                </button>
            </div>
        </form>
    </div>
</div>
@endsection