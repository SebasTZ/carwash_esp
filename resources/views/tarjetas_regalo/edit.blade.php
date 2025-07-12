@extends('layouts.app')

@section('title', 'Edit Gift Card')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Edit Gift Card</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('tarjetas_regalo.reporte.view') }}">Gift Card Report</a></li>
        <li class="breadcrumb-item active">Edit Gift Card</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fa-solid fa-gift me-1"></i>
            Edit Gift Card
        </div>
        <div class="card-body">
            <form action="{{ route('tarjetas_regalo.update', $tarjeta->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Gift Card Code</label>
                    <input type="text" class="form-control" value="{{ $tarjeta->codigo }}" disabled>
                </div>
                <div class="mb-3">
                    <label for="valor_inicial" class="form-label">Initial Value</label>
                    <input type="number" class="form-control" id="valor_inicial" name="valor_inicial" required min="1" step="0.01" value="{{ $tarjeta->valor_inicial }}">
                </div>
                <div class="mb-3">
                    <label for="fecha_vencimiento" class="form-label">Expiration Date (optional)</label>
                    <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento" value="{{ $tarjeta->fecha_vencimiento }}">
                </div>
                <div class="mb-3">
                    <label for="cliente_id" class="form-label">Customer (optional)</label>
                    <select class="form-control" id="cliente_id" name="cliente_id">
                        <option value="">-- None --</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}" @if($tarjeta->cliente_id == $cliente->id) selected @endif>{{ $cliente->persona->razon_social }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="estado" class="form-label">Status</label>
                    <select class="form-control" id="estado" name="estado">
                        <option value="activa" @if($tarjeta->estado=='activa') selected @endif>Active</option>
                        <option value="usada" @if($tarjeta->estado=='usada') selected @endif>Used</option>
                        <option value="vencida" @if($tarjeta->estado=='vencida') selected @endif>Expired</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Update</button>
            </form>
            <form action="{{ route('tarjetas_regalo.destroy', $tarjeta->id) }}" method="POST" class="mt-3">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this gift card?')">Delete</button>
            </form>
        </div>
    </div>
</div>
@endsection
