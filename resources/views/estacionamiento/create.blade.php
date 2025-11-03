@extends('layouts.app')

@section('title', 'Registrar Entrada de Vehículo')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mt-4 text-primary">
            <i class="fas fa-car-side me-2"></i>Registrar Entrada de Vehículo
        </h1>
        <a href="{{ route('estacionamiento.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-list me-2"></i>Ver Lista
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Errores:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm mx-auto" style="max-width: 800px;">
        <form action="{{ route('estacionamiento.store') }}" method="POST">
            @csrf
            
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Información del Cliente y Vehículo</h5>
            </div>

            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="cliente_id" class="form-label">Cliente *</label>
                        <select class="form-select" id="cliente_id" name="cliente_id" required>
                            <option value="">Seleccione un cliente</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}">
                                    {{ $cliente->persona->razon_social }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="placa" class="form-label">Placa del Vehículo *</label>
                        <input type="text" class="form-control text-uppercase" id="placa" name="placa" required>
                    </div>

                    <div class="col-md-6">
                        <label for="marca" class="form-label">Marca *</label>
                        <input type="text" class="form-control" id="marca" name="marca" required>
                    </div>

                    <div class="col-md-6">
                        <label for="modelo" class="form-label">Modelo *</label>
                        <input type="text" class="form-control" id="modelo" name="modelo" required>
                    </div>

                    <div class="col-md-6">
                        <label for="telefono" class="form-label">Teléfono *</label>
                        <input type="text" class="form-control" id="telefono" name="telefono" required>
                    </div>

                    <div class="col-md-6">
                        <label for="tarifa_hora" class="form-label">Tarifa por Hora (S/) *</label>
                        <input type="number" step="0.01" class="form-control" id="tarifa_hora" name="tarifa_hora" required>
                    </div>

                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="pagado_adelantado" name="pagado_adelantado" value="1">
                                    <label class="form-check-label fw-bold" for="pagado_adelantado">
                                        <i class="fas fa-money-bill-wave text-success"></i> ¿Pago adelantado?
                                    </label>
                                </div>

                                <div id="monto-div" style="display: none;">
                                    <label for="monto_pagado_adelantado" class="form-label">Monto (S/)</label>
                                    <input type="number" step="0.01" class="form-control" id="monto_pagado_adelantado" name="monto_pagado_adelantado" min="0" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end">
                <a href="{{ route('estacionamiento.index') }}" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary">Registrar</button>
            </div>
        </form>
    </div>
</div>

<script>
// Script INLINE - se ejecuta inmediatamente
console.log('✅ Script inline ejecutándose');

// Esperar a que el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initForm);
} else {
    initForm();
}

function initForm() {
    console.log('✅ initForm ejecutándose');
    
    const checkbox = document.getElementById('pagado_adelantado');
    const montoDiv = document.getElementById('monto-div');
    
    if (checkbox && montoDiv) {
        checkbox.addEventListener('change', function() {
            montoDiv.style.display = this.checked ? 'block' : 'none';
        });
        console.log('✅ Event listener agregado');
    } else {
        console.error('❌ No se encontraron elementos');
    }
}
</script>
@endsection
