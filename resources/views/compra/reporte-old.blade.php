@extends('layouts.app')

@section('title', 'Reporte de Compras ' . ucfirst($reporte))

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
    <h1 class="mt-4 text-center">Reporte de Compras {{ ucfirst($reporte) }}</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('compras.index') }}">Compras</a></li>
        <li class="breadcrumb-item active">Reporte {{ ucfirst($reporte) }}</li>
    </ol>

    @if($reporte === 'personalizado')
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-calendar me-1"></i>
            Seleccionar Rango de Fechas
        </div>
        <div class="card-body">
            <form action="{{ route('compras.reporte.personalizado') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-auto">
                    <label for="fecha_inicio" class="col-form-label">Fecha de inicio:</label>
                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required value="{{ $fechaInicio ?? '' }}">
                </div>
                <div class="col-auto">
                    <label for="fecha_fin" class="col-form-label">Fecha de fin:</label>
                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required value="{{ $fechaFin ?? '' }}">
                </div>
                <div class="col-auto">
                    <label class="col-form-label">&nbsp;</label>
                    <button type="submit" class="form-control btn btn-primary">Filtrar</button>
                </div>
                @if(isset($fechaInicio) && isset($fechaFin))
                <div class="col-auto">
                    <label class="col-form-label">&nbsp;</label>
                    <a href="{{ route('compras.export.personalizado', ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin]) }}" class="form-control btn btn-success">
                        Exportar a Excel
                    </a>
                </div>
                @endif
            </form>
        </div>
    </div>
    @else
    <div class="mb-4">
        <a href="{{ route('compras.export.' . $reporte) }}">
            <button type="button" class="btn btn-success">Exportar a Excel</button>
        </a>
    </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Tabla de Compras {{ $reporte }}
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-striped">
                <thead>
                    <tr>
                        <th>Comprobante</th>
                        <th>Proveedor</th>
                        <th>Fecha y Hora</th>
                        <th>IGV</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $total = 0;
                    @endphp
                    @foreach ($compras as $item)
                    @php
                        $total += $item->total;
                    @endphp
                    <tr>
                        <td>
                            <p class="fw-semibold mb-1">{{$item->comprobante->tipo_comprobante}}</p>
                            <p class="text-muted mb-0">{{$item->numero_comprobante}}</p>
                        </td>
                        <td>
                            <p class="fw-semibold mb-1">{{ ucfirst($item->proveedore->persona->tipo_persona) }}</p>
                            <p class="text-muted mb-0">{{$item->proveedore->persona->razon_social}}</p>
                        </td>
                        <td>
                            <div class="row-not-space">
                                <p class="fw-semibold mb-1"><span class="m-1"><i class="fa-solid fa-calendar-days"></i></span>{{\Carbon\Carbon::parse($item->fecha_hora)->format('d-m-Y')}}</p>
                                <p class="fw-semibold mb-0"><span class="m-1"><i class="fa-solid fa-clock"></i></span>{{\Carbon\Carbon::parse($item->fecha_hora)->format('H:i')}}</p>
                            </div>
                        </td>
                        <td>{{$item->impuesto}}</td>
                        <td>{{$item->total}}</td>
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