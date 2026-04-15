@extends('layouts.app')

@section('title', 'Editar Cita')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Editar Cita #{{ $cita->id }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group mr-2">
            <a href="{{ route('citas.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
</div>

<x-flash-alert />

<div class="card">
    <div class="card-header">Formulario de Edición</div>
    <div class="card-body">
        <form action="{{ route('citas.update', $cita) }}" method="POST" id="citaEditForm" novalidate>
            @csrf
            @method('PUT')
            
            <!-- Client (not editable) -->
            <div class="mb-4">
                <label class="form-label">Cliente</label>
                <input type="text" class="form-control" value="{{ $cita->cliente->persona->razon_social }} - {{ $cita->cliente->persona->numero_documento }}" disabled>
                <input type="hidden" name="cliente_id" value="{{ $cita->cliente_id }}">
                <small class="form-text text-muted">El cliente no puede ser cambiado.</small>
            </div>
            
            <!-- Editable fields: date, time, and notes -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="fecha" class="form-label">Fecha <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('fecha') is-invalid @enderror" id="fecha" name="fecha" value="{{ old('fecha', $cita->fecha->format('Y-m-d')) }}" required>
                    <div class="invalid-feedback"></div>
                    @error('fecha')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6">
                    <label for="hora" class="form-label">Hora <span class="text-danger">*</span></label>
                    <input type="time" class="form-control @error('hora') is-invalid @enderror" id="hora" name="hora" value="{{ old('hora', $cita->hora) }}" required>
                    <div class="invalid-feedback"></div>
                    @error('hora')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="notas" class="form-label">Notas</label>
                <textarea class="form-control @error('notas') is-invalid @enderror" id="notas" name="notas" rows="3">{{ old('notas', $cita->notas) }}</textarea>
                <div class="invalid-feedback"></div>
                @error('notas')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('citas.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Actualizar Cita
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
@vite(['resources/js/modules/CitasFormManager.js'])
@endpush