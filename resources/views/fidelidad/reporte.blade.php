@extends('layouts.app')

@section('title', 'Loyalty Report')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Loyalty Report</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Home</a></li>
        <li class="breadcrumb-item active">Loyalty Report</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-users me-1"></i>
            Frequent Customers
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Accumulated Washes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($clientes_frecuentes as $cliente)
                    <tr>
                        <td>{{ $cliente->persona->razon_social }}</td>
                        <td>{{ $cliente->lavados_acumulados }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-star me-1"></i>
            Free Washes Granted
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Receipt</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lavados_gratis as $lavado)
                    <tr>
                        <td>{{ $lavado->cliente->persona->razon_social }}</td>
                        <td>{{ \Carbon\Carbon::parse($lavado->fecha_hora)->format('d-m-Y H:i') }}</td>
                        <td>{{ $lavado->numero_comprobante }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <a href="{{ route('fidelidad.export.excel') }}" class="btn btn-success mb-3">Export to Excel</a>
</div>
@endsection
