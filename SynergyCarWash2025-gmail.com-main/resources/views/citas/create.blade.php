@extends('layouts.app')

@section('title', 'Create New Appointment')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Create New Appointment</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group mr-2">
            <a href="{{ route('citas.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
</div>

<!-- Success or error messages -->
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Client Information</h5>
            <a href="{{ route('clientes.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-user-plus"></i> Create New Client
            </a>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('citas.store') }}" method="POST" id="citaForm">
            @csrf
            
            <div class="mb-4">
                <label for="cliente_id" class="form-label">Client <span class="text-danger">*</span></label>
                <select class="form-control @error('cliente_id') is-invalid @enderror" id="cliente_id" name="cliente_id" required>
                    <option value="">Select a client</option>
                    @foreach($clientes as $cliente)
                    <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                        {{ $cliente->persona->razon_social }} - {{ $cliente->persona->numero_documento }}
                    </option>
                    @endforeach
                </select>
                @error('cliente_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">If the client is not in the list, use the "Create New Client" button.</small>
            </div>
            
            <hr>
            
            <h5 class="mt-4 mb-3">Appointment Details</h5>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="fecha" class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('fecha') is-invalid @enderror" id="fecha" name="fecha" value="{{ old('fecha', date('Y-m-d')) }}" required min="{{ date('Y-m-d') }}">
                        @error('fecha')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="hora" class="form-label">Time <span class="text-danger">*</span></label>
                        <input type="time" class="form-control @error('hora') is-invalid @enderror" id="hora" name="hora" value="{{ old('hora') }}" required>
                        @error('hora')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="notas" class="form-label">Notes</label>
                <textarea class="form-control @error('notas') is-invalid @enderror" id="notas" name="notas" rows="3">{{ old('notas') }}</textarea>
                @error('notas')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Enter any additional information about the appointment.</small>
            </div>

            <div class="alert alert-info" role="alert">
                <i class="fas fa-info-circle"></i> The queue position will be assigned automatically based on the selected date.
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Appointment
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
