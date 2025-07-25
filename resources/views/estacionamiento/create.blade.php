@extends('layouts.app')

@section('title', 'Registrar Entrada de Vehículo')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mt-4 text-primary" style="margin-bottom: 1rem;">
            <i class="fas fa-car-side me-2"></i>Registrar Entrada de Vehículo
        </h1>
        <a href="{{ route('estacionamiento.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-list me-2"></i>Ver Lista
        </a>
    </div>

    <form action="{{ route('estacionamiento.store') }}" method="POST" class="card shadow-sm border-0 mx-auto" style="max-width: 800px;">
        @csrf
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Información del Cliente y Vehículo</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="cliente_id" class="form-label">Cliente</label>
                    <select class="form-select select2" id="cliente_id" name="cliente_id" required>
                        <option value="">Seleccione un cliente</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}">
                                {{ $cliente->persona->razon_social }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="placa" class="form-label">Placa del Vehículo</label>
                    <input type="text" class="form-control text-uppercase" id="placa" name="placa" required>
                </div>

                <div class="col-md-6">
                    <label for="marca" class="form-label">Marca</label>
                    <input type="text" class="form-control" id="marca" name="marca" required>
                </div>
                <div class="col-md-6">
                    <label for="modelo" class="form-label">Modelo</label>
                    <input type="text" class="form-control" id="modelo" name="modelo" required>
                </div>

                <div class="col-md-6">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" class="form-control" id="telefono" name="telefono" required>
                </div>
                <div class="col-md-6">
                    <label for="tarifa_hora" class="form-label">Tarifa por Hora (S/)</label>
                    <input type="number" step="0.01" class="form-control" id="tarifa_hora" name="tarifa_hora" required placeholder="Ingrese la tarifa por hora">
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end">
            <a href="{{ route('estacionamiento.index') }}" class="btn btn-secondary me-2">Cancelar</a>
            <button type="submit" class="btn btn-primary">Registrar estacionamiento</button>
        </div>
    </form>
</div>

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Seleccione una opción",
            allowClear: true
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const clienteSelect = document.getElementById('cliente_id');

        // Eliminar lógica relacionada con el número de documento
        clienteSelect.addEventListener('change', function() {
            // No se realiza ninguna acción adicional
        });
    });
</script>
@endpush
@endsection