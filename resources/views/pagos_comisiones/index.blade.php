@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Commission Payments</h1>
    @can('crear-pago-comision')
        <a href="{{ route('pagos_comisiones.create') }}" class="btn btn-primary mb-3">Register Payment</a>
    @endcan
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Washer</th>
                <th>Amount Paid</th>
                <th>From</th>
                <th>To</th>
                <th>Payment Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pagos as $pago)
                <tr>
                    <td>{{ $pago->lavador->nombre }}</td>
                    <td>{{ $pago->monto_pagado }}</td>
                    <td>{{ $pago->desde }}</td>
                    <td>{{ $pago->hasta }}</td>
                    <td>{{ $pago->fecha_pago }}</td>
                    <td>
                        @can('ver-historial-pago-comision')
                            <a href="{{ route('pagos_comisiones.lavador', $pago->lavador_id) }}" class="btn btn-sm btn-info">History</a>
                        @endcan
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
