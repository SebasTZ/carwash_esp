@extends('layouts.app')

@section('title', 'Reporte de Ventas ' . ucfirst($reporte))

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
    <h1 class="mt-4 text-center">Reporte de Ventas {{ ucfirst($reporte) }}</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('ventas.index') }}">Ventas</a></li>
        <li class="breadcrumb-item active">Reporte {{ ucfirst($reporte) }}</li>
    </ol>

    @if($reporte === 'personalizado')
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-calendar me-1"></i>
            Seleccionar Rango de Fechas
        </div>
        <div class="card-body">
            <form action="{{ route('ventas.reporte.personalizado') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-auto">
                    <label for="fecha_inicio" class="col-form-label">Fecha de Inicio:</label>
                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required value="{{ $fechaInicio ?? '' }}">
                </div>
                <div class="col-auto">
                    <label for="fecha_fin" class="col-form-label">Fecha de Fin:</label>
                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required value="{{ $fechaFin ?? '' }}">
                </div>
                <div class="col-auto">
                    <label class="col-form-label">&nbsp;</label>
                    <button type="submit" class="form-control btn btn-primary">Filtrar</button>
                </div>
                @if(isset($fechaInicio) && isset($fechaFin))
                <div class="col-auto">
                    <label class="col-form-label">&nbsp;</label>
                    <a href="{{ route('ventas.export.personalizado', ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin]) }}" class="form-control btn btn-success">
                        Exportar a Excel
                    </a>
                </div>
                @endif
            </form>
        </div>
    </div>
    @else
    <div class="mb-4">
        <a href="{{ route('ventas.export.' . $reporte) }}">
            <button type="button" class="btn btn-success">Exportar a Excel</button>
        </a>
    </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Tabla de Ventas {{ $reporte }}
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-striped">
                <thead>
                    <tr>
                        <th>Comprobante</th>
                        <th>Cliente</th>
                        <th>Fecha y Hora</th>
                        <th>Vendedor</th>
                        <th>Total</th>
                        <th>Comentarios</th>
                        <th>Método de Pago</th>
                        <th>Efectivo</th>
                        <th>Billetera Digital</th>
                        <th>Tarjeta de Regalo</th>
                        <th>Lavado Gratis</th>
                        <th>Servicio de Lavado</th>
                        <th>Hora Fin de Lavado</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $total = 0;
                        $paymentMethods = [
                            'efectivo' => 'Efectivo',
                            'tarjeta_regalo' => 'Tarjeta de Regalo',
                            'lavado_gratis' => 'Lavado Gratis',
                            'tarjeta_credito' => 'Tarjeta de Crédito',
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
                        <td>{{ $item->tarjeta_regalo_id ? 'Sí' : 'No' }}</td>
                        <td>{{ $item->lavado_gratis ? 'Sí' : 'No' }}</td>
                        <td>{{$item->servicio_lavado ? 'Sí' : 'No'}}</td>
                        <td>{{$item->horario_lavado ? \Carbon\Carbon::parse($item->horario_lavado)->format('d-m-Y H:i') : 'N/D'}}</td>
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