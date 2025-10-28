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

    <div id="form-validator-estacionamiento-create"></div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.FormValidator) {
            window.FormValidator.render({
                target: document.getElementById('form-validator-estacionamiento-create'),
                action: "{{ route('estacionamiento.store') }}",
                method: "POST",
                fields: [
                    { name: "cliente_id", label: "Cliente", type: "select", required: true, options: [ { value: "", label: "Seleccione un cliente" }, @foreach($clientes as $cliente) { value: "{{ $cliente->id }}", label: "{{ $cliente->persona->razon_social }}" }, @endforeach ] },
                    { name: "placa", label: "Placa del Vehículo", type: "text", required: true, class: "text-uppercase" },
                    { name: "marca", label: "Marca", type: "text", required: true },
                    { name: "modelo", label: "Modelo", type: "text", required: true },
                    { name: "telefono", label: "Teléfono", type: "text", required: true },
                    { name: "tarifa_hora", label: "Tarifa por Hora (S/)", type: "number", step: "0.01", required: true, placeholder: "Ingrese la tarifa por hora" }
                ],
                submit: { label: "Registrar estacionamiento", class: "btn btn-primary" },
                csrf: "{{ csrf_token() }}"
            });
        }
    });
    </script>
    <div class="d-flex justify-content-end mt-3">
        <a href="{{ route('estacionamiento.index') }}" class="btn btn-secondary me-2">Cancelar</a>
    </div>
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