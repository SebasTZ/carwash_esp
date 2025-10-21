@extends('layouts.app')

@section('title', 'Estacionamiento')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Estacionamiento</h1>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-car me-1"></i>
            Vehículos Estacionados
            @can('crear-estacionamiento')
            <a class="btn btn-success btn-sm float-end" href="{{ route('estacionamiento.create') }}">
                <i class="fas fa-plus"></i> Registrar Entrada
            </a>
            @endcan
            <a href="{{ route('estacionamiento.historial') }}" class="btn btn-info btn-sm float-end me-2">
                <i class="fas fa-history"></i> Ver Historial
            </a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Placa</th>
                        <th>Cliente</th>
                        <th>Marca/Modelo</th>
                        <th>Contacto</th>
                        <th>Hora de Entrada</th>
                        <th>Tiempo</th>
                        <th>Tarifa/Hora</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($estacionamientos as $estacionamiento)
                    <tr>
                        <td>{{ $estacionamiento->placa }}</td>
                        <td>{{ $estacionamiento->cliente->persona->razon_social }}</td>
                        <td>{{ $estacionamiento->marca }} / {{ $estacionamiento->modelo }}</td>
                        <td>{{ $estacionamiento->telefono }}</td>
                        <td>{{ $estacionamiento->hora_entrada->format('d/m/Y H:i') }}</td>
                        <td>{{ $estacionamiento->hora_entrada->diffForHumans(null, true) }}</td>
                        <td>S/. {{ number_format($estacionamiento->tarifa_hora, 2) }}</td>
                        <td>
                            <form action="{{ route('estacionamiento.registrar-salida', $estacionamiento) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('¿Está seguro de registrar la salida?')">
                                    <i class="fas fa-sign-out-alt"></i> Registrar Salida
                                </button>
                            </form>
                            @can('eliminar-estacionamiento')
                            <form action="{{ route('estacionamiento.destroy', $estacionamiento) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de eliminar este registro?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Paginación usando componente -->
            <x-pagination-info :paginator="$estacionamientos" entity="vehículos" />
        </div>
    </div>
</div>
@endsection
@push('js')
@vite(['resources/js/modules/EstacionamientoManager.js'])
@endpush
