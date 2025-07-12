@extends('layouts.app')

@section('title', 'Create Gift Card')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Create Gift Card</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('tarjetas_regalo.reporte.view') }}">Gift Card Report</a></li>
        <li class="breadcrumb-item active">Create Gift Card</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fa-solid fa-gift me-1"></i>
            New Gift Card
        </div>
        <div class="card-body">
            <form action="{{ route('tarjetas_regalo.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="codigo" class="form-label">Gift Card Code</label>
                    <input type="text" class="form-control" id="codigo" name="codigo" required maxlength="30">
                </div>
                <div class="mb-3">
                    <label for="valor_inicial" class="form-label">Initial Value</label>
                    <input type="number" class="form-control" id="valor_inicial" name="valor_inicial" required min="1" step="0.01">
                </div>
                <div class="mb-3">
                    <label for="fecha_venta" class="form-label">Sale Date</label>
                    <input type="date" class="form-control" id="fecha_venta" name="fecha_venta" required value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <label for="fecha_vencimiento" class="form-label">Expiration Date (optional)</label>
                    <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento">
                </div>
                <div class="mb-3">
                    <label for="cliente_id" class="form-label">Customer (optional)</label>
                    <select class="form-control" id="cliente_id" name="cliente_id">
                        <option value="">-- None --</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}">{{ $cliente->persona->razon_social }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Create</button>
            </form>
        </div>
    </div>
</div>
@endsection
