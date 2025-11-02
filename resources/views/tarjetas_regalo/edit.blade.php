@extends('layouts.app')

@section('title', 'Editar Tarjeta de Regalo')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Editar Tarjeta de Regalo</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('tarjetas_regalo.reporte.view') }}">Reporte de Tarjetas de Regalo</a></li>
        <li class="breadcrumb-item active">Editar Tarjeta de Regalo</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fa-solid fa-gift me-1"></i>
            Editar Tarjeta de Regalo
        </div>
        <div class="card-body">
            <form id="tarjetaRegaloForm" action="{{ route('tarjetas_regalo.update', $tarjeta->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Código de Tarjeta</label>
                    <input type="text" class="form-control" value="{{ $tarjeta->codigo }}" disabled>
                </div>
                <div class="mb-3">
                    <label for="valor_inicial" class="form-label">Valor Inicial</label>
                    <input type="number" class="form-control" id="valor_inicial" name="valor_inicial" required min="1" step="0.01" value="{{ $tarjeta->valor_inicial }}">
                </div>
                <div class="mb-3">
                    <label for="fecha_vencimiento" class="form-label">Fecha de Vencimiento (opcional)</label>
                    <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento" value="{{ $tarjeta->fecha_vencimiento }}">
                </div>
                <div class="mb-3">
                    <label for="cliente_id" class="form-label">Cliente (opcional)</label>
                    <select class="form-control" id="cliente_id" name="cliente_id">
                        <option value="">-- Ninguno --</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}" @if($tarjeta->cliente_id == $cliente->id) selected @endif>{{ $cliente->persona->razon_social }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-control" id="estado" name="estado">
                        <option value="activa" @if($tarjeta->estado=='activa') selected @endif>Activa</option>
                        <option value="usada" @if($tarjeta->estado=='usada') selected @endif>Usada</option>
                        <option value="vencida" @if($tarjeta->estado=='vencida') selected @endif>Vencida</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Actualizar</button>
            </form>
            <form action="{{ route('tarjetas_regalo.destroy', $tarjeta->id) }}" method="POST" class="mt-3">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('¿Está seguro de que desea eliminar esta tarjeta de regalo?')">Eliminar</button>
            </form>
        </div>
    </div>
</div>
@endsection
