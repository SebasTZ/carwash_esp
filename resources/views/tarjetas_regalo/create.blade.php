@extends('layouts.app')

@section('title', 'Crear Tarjeta de Regalo')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Crear Tarjeta de Regalo</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('tarjetas_regalo.reporte.view') }}">Reporte de Tarjetas de Regalo</a></li>
        <li class="breadcrumb-item active">Crear Tarjeta de Regalo</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fa-solid fa-gift me-1"></i>
            Nueva Tarjeta de Regalo
        </div>
        <div class="card-body">
            <form action="{{ route('tarjetas_regalo.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="codigo" class="form-label">Código de Tarjeta</label>
                    <input type="text" class="form-control" id="codigo" name="codigo" required maxlength="30">
                </div>
                <div class="mb-3">
                    <label for="valor_inicial" class="form-label">Valor Inicial</label>
                    <input type="number" class="form-control" id="valor_inicial" name="valor_inicial" required min="1" step="0.01">
                </div>
                <div class="mb-3">
                    <label for="fecha_venta" class="form-label">Fecha de Venta</label>
                    <input type="date" class="form-control" id="fecha_venta" name="fecha_venta" required value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <label for="fecha_vencimiento" class="form-label">Fecha de Vencimiento (opcional)</label>
                    <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento">
                </div>
                <div class="mb-3">
                    <label for="cliente_id" class="form-label">Cliente (opcional)</label>
                    <select class="form-control" id="cliente_id" name="cliente_id">
                        <option value="">-- Ninguno --</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}">{{ $cliente->persona->razon_social }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Crear</button>
            </form>
        </div>
    </div>
</div>
@endsection
