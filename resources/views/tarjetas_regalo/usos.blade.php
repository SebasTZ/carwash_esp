@extends('layouts.app')

@section('title', 'Historial de Uso de Tarjetas de Regalo')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Historial de Uso de Tarjetas de Regalo</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('tarjetas_regalo.reporte.view') }}">Reporte de Tarjetas de Regalo</a></li>
        <li class="breadcrumb-item active">Historial de Uso</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fa-solid fa-gift me-1"></i>
            Usos de Tarjetas de Regalo
        </div>
        <div class="card-body">
            <table id="tarjetaRegaloUsosTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>Código de Tarjeta</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Comprobante</th>
                        <th>Monto Usado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($usos as $venta)
                    <tr>
                        <td>{{ $venta->tarjetaRegalo->codigo ?? '-' }}</td>
                        <td>{{ $venta->cliente->persona->razon_social }}</td>
                        <td>{{ \Carbon\Carbon::parse($venta->fecha_hora)->format('d-m-Y H:i') }}</td>
                        <td>{{ $venta->numero_comprobante }}</td>
                        <td>{{ $venta->total }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
