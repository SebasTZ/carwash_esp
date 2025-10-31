
@extends('layouts.app')

@section('title','Registrar Compra')

@push('css')
<link rel="stylesheet" href="/css/bootstrap-select.min.css">
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Registrar Compra</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('compras.index')}}">Compras</a></li>
        <li class="breadcrumb-item active">Registrar Compra</li>
    </ol>
</div>

<div id="formCompraContainer"></div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        new window.CarWash.CompraForm({
            elementId: 'formCompraContainer',
            productos: @json($productos),
            proveedores: @json($proveedores),
            comprobantes: @json($comprobantes),
            impuesto: {{ $impuesto ?? 18 }},
            old: @json(old()),
            errors: @json($errors->all()),
            action: '{{ route('compras.store') }}',
            method: 'POST',
            onFormReady: function(form) {
                new window.CarWash.FormValidator(form, {
                    validateOnInput: false
                });
            }
        });
    });
</script>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
@endpush
