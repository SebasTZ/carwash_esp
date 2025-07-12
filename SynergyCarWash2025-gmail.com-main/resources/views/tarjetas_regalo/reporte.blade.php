@extends('layouts.app')

@section('title', 'Gift Card Report')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Gift Card Report</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Home</a></li>
        <li class="breadcrumb-item active">Gift Card Report</li>
    </ol>
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Gift Cards
        </div>
        <div class="card-body">
            <a href="{{ route('tarjetas_regalo.export.excel') }}" class="btn btn-success mb-3">Export to Excel</a>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Initial Value</th>
                        <th>Current Balance</th>
                        <th>Status</th>
                        <th>Sale Date</th>
                        <th>Expiration Date</th>
                        <th>Customer</th>
                        <th>Actions</th>
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
                            <a href="{{ route('tarjetas_regalo.edit', $tarjeta->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
