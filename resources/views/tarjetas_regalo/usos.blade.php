@extends('layouts.app')

@section('title', 'Gift Card Usage History')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Gift Card Usage History</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('tarjetas_regalo.reporte.view') }}">Gift Card Report</a></li>
        <li class="breadcrumb-item active">Usage History</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fa-solid fa-gift me-1"></i>
            Gift Card Usages
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Gift Card Code</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Receipt</th>
                        <th>Amount Used</th>
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
