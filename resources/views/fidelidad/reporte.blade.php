@extends('layouts.app')

@section('title', 'Reporte de Fidelidad')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Reporte de Fidelidad</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Reporte de Fidelidad</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-users me-1"></i>
            Clientes Frecuentes
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Lavados Acumulados</th>
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

            <!-- Paginación Clientes Frecuentes usando componente -->
            <x-pagination-multi 
                :paginator="$clientes_frecuentes" 
                entity="clientes" 
                page-name="clientes_page" 
            />
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-star me-1"></i>
            Lavados Gratis Otorgados
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Comprobante</th>
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

            <!-- Paginación Lavados Gratis usando componente -->
            <x-pagination-multi 
                :paginator="$lavados_gratis" 
                entity="lavados gratis" 
                page-name="lavados_page" 
            />
        </div>
    </div>
    <a href="{{ route('fidelidad.export.excel') }}" class="btn btn-success mb-3">Exportar a Excel</a>
</div>
@endsection
