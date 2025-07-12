@extends('layouts.app')

@section('title', 'Reporte de Tarjetas de Regalo')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Reporte de Tarjetas de Regalo</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Reporte de Tarjetas de Regalo</li>
    </ol>
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Tarjetas de Regalo
        </div>
        <div class="card-body">
            <a href="{{ route('tarjetas_regalo.export.excel') }}" class="btn btn-success mb-3">Exportar a Excel</a>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Valor Inicial</th>
                        <th>Saldo Actual</th>
                        <th>Estado</th>
                        <th>Fecha de Venta</th>
                        <th>Fecha de Vencimiento</th>
                        <th>Cliente</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tarjetas as $tarjeta)
                    <tr>
                        <td>{{ $tarjeta->codigo }}</td>
                        <td>{{ $tarjeta->valor_inicial }}</td>
                        <td>{{ $tarjeta->saldo_actual }}</td>
                        <td>{{ ucfirst($tarjeta->estado) }}</td>
                        <td>{{ $tarjeta->fecha_venta }}</td>
                        <td>{{ $tarjeta->fecha_vencimiento ?? '-' }}</td>
                        <td>{{ $tarjeta->cliente ? $tarjeta->cliente->persona->razon_social : '-' }}</td>
                        <td>
                            <a href="{{ route('tarjetas_regalo.edit', $tarjeta->id) }}" class="btn btn-warning btn-sm">Editar</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
