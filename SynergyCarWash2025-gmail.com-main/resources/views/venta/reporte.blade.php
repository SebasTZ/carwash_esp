@extends('layouts.app')

@section('title', 'Sales Report ' . ucfirst($reporte))

@push('css-datatable')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
@endpush
@push('css')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .row-not-space {
        width: 110px;
    }
</style>
@endpush

@section('content')

@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Sales Report {{ ucfirst($reporte) }}</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('ventas.index') }}">Sales</a></li>
        <li class="breadcrumb-item active">Report {{ ucfirst($reporte) }}</li>
    </ol>

    @if($reporte === 'personalizado')
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-calendar me-1"></i>
            Select Date Range
        </div>
        <div class="card-body">
            <form action="{{ route('ventas.reporte.personalizado') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-auto">
                    <label for="fecha_inicio" class="col-form-label">Start Date:</label>
                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required value="{{ $fechaInicio ?? '' }}">
                </div>
                <div class="col-auto">
                    <label for="fecha_fin" class="col-form-label">End Date:</label>
                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required value="{{ $fechaFin ?? '' }}">
                </div>
                <div class="col-auto">
                    <label class="col-form-label">&nbsp;</label>
                    <button type="submit" class="form-control btn btn-primary">Filter</button>
                </div>
                @if(isset($fechaInicio) && isset($fechaFin))
                <div class="col-auto">
                    <label class="col-form-label">&nbsp;</label>
                    <a href="{{ route('ventas.export.personalizado', ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin]) }}" class="form-control btn btn-success">
                        Export to Excel
                    </a>
                </div>
                @endif
            </form>
        </div>
    </div>
    @else
    <div class="mb-4">
        <a href="{{ route('ventas.export.' . $reporte) }}">
            <button type="button" class="btn btn-success">Export to Excel</button>
        </a>
    </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Sales Table {{ $reporte }}
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-striped">
                <thead>
                    <tr>
                        <th>Receipt</th>
                        <th>Customer</th>
                        <th>Date and Time</th>
                        <th>Seller</th>
                        <th>Total</th>
                        <th>Comments</th>
                        <th>Payment Method</th>
                        <th>Cash</th>
                        <th>Digital Wallet</th>
                        <th>Gift Card</th>
                        <th>Free Wash</th>
                        <th>Car Wash Service</th>
                        <th>Car Wash End Time</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $total = 0;
                        $paymentMethods = [
                            'efectivo' => 'Cash',
                            'tarjeta_regalo' => 'Gift Card',
                            'lavado_gratis' => 'Free Wash',
                            'tarjeta_credito' => 'Credit Card',
                        ];
                    @endphp
                    @foreach ($ventas as $item)
                    @php
                        // Sumar solo si el método de pago NO es tarjeta_regalo ni lavado_gratis
                        if ($item->medio_pago !== 'tarjeta_regalo' && $item->medio_pago !== 'lavado_gratis') {
                            $total += $item->total;
                        }
                    @endphp
                    <tr>
                        <td>
                            <p class="fw-semibold mb-1">{{$item->comprobante->tipo_comprobante}}</p>
                            <p class="text-muted mb-0">{{$item->numero_comprobante}}</p>
                        </td>
                        <td>
                            <p class="fw-semibold mb-1">{{ ucfirst($item->cliente->persona->tipo_persona) }}</p>
                            <p class="text-muted mb-0">{{$item->cliente->persona->razon_social}}</p>
                        </td>
                        <td>
                            <div class="row-not-space">
                                <p class="fw-semibold mb-1"><span class="m-1"><i class="fa-solid fa-calendar-days"></i></span>{{\Carbon\Carbon::parse($item->fecha_hora)->format('d-m-Y')}}</p>
                                <p class="fw-semibold mb-0"><span class="m-1"><i class="fa-solid fa-clock"></i></span>{{\Carbon\Carbon::parse($item->fecha_hora)->format('H:i')}}</p>
                            </div>
                        </td>
                        <td>{{$item->user->name}}</td>
                        <td>{{$item->total}}</td>
                        <td>{{$item->comentarios}}</td>
                        <td>{{ $paymentMethods[$item->medio_pago] ?? ucfirst(str_replace('_', ' ', $item->medio_pago)) }}</td>
                        <td>{{$item->efectivo}}</td>
                        <td>{{$item->billetera_digital}}</td>
                        <td>{{ $item->tarjeta_regalo_id ? 'Yes' : 'No' }}</td>
                        <td>{{ $item->lavado_gratis ? 'Yes' : 'No' }}</td>
                        <td>{{$item->servicio_lavado ? 'Yes' : 'No'}}</td>
                        <td>{{$item->horario_lavado ? \Carbon\Carbon::parse($item->horario_lavado)->format('d-m-Y H:i') : 'N/A'}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">
                <h4>Total: {{$total}}</h4>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
<script>
    window.addEventListener('DOMContentLoaded', event => {
        const dataTable = new simpleDatatables.DataTable("#datatablesSimple", {})
    });
</script>
@endpush