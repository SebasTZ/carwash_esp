@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Commission Report by Washer</h1>
    <form method="GET" action="{{ route('pagos_comisiones.reporte') }}" class="row g-3 mb-3">
        <div class="col-md-4">
            <label for="fecha_inicio" class="form-label">From</label>
            <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="{{ $fechaInicio }}">
        </div>
        <div class="col-md-4">
            <label for="fecha_fin" class="form-label">To</label>
            <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" value="{{ $fechaFin }}">
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('pagos_comisiones.reporte.export', ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin]) }}" class="btn btn-success ms-2">Export to Excel</a>
        </div>
    </form>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Washer</th>
                <th>Number of Washes</th>
                <th>Total Commission</th>
                <th>Total Paid</th>
                <th>Pending</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    <td>{{ $row['lavador']->nombre }}</td>
                    <td>{{ $row['cantidad'] }}</td>
                    <td>{{ number_format($row['comision_total'], 2) }}</td>
                    <td>{{ number_format($row['pagado'], 2) }}</td>
                    <td>{{ number_format($row['saldo'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2 class="mt-5">Commission Payment History</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Washer</th>
                <th>Amount Paid</th>
                <th>From</th>
                <th>To</th>
                <th>Payment Date</th>
                <th>Observation</th>
            </tr>
        </thead>
        <tbody>
            @foreach(\App\Models\Lavador::where('estado', 'activo')->get() as $lavador)
                @foreach($lavador->pagosComisiones()->orderBy('fecha_pago', 'desc')->get() as $pago)
                    <tr>
                        <td>{{ $lavador->nombre }}</td>
                        <td>{{ number_format($pago->monto_pagado, 2) }}</td>
                        <td>{{ \Carbon\Carbon::parse($pago->desde)->format('d/m/Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($pago->hasta)->format('d/m/Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}</td>
                        <td>{{ $pago->observacion ?? '-' }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</div>
@endsection
