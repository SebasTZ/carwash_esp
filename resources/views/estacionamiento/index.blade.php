@extends('layouts.app')

@section('title', 'Parking')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Parking</h1>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-car me-1"></i>
            Parked Vehicles
            @can('crear-estacionamiento')
            <a class="btn btn-success btn-sm float-end" href="{{ route('estacionamiento.create') }}">
                <i class="fas fa-plus"></i> Register Entry
            </a>
            @endcan
            <a href="{{ route('estacionamiento.historial') }}" class="btn btn-info btn-sm float-end me-2">
                <i class="fas fa-history"></i> View History
            </a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <table id="datatablesSimple" class="table table-striped">
                <thead>
                    <tr>
                        <th>License Plate</th>
                        <th>Customer</th>
                        <th>Brand/Model</th>
                        <th>Contact</th>
                        <th>Entry Time</th>
                        <th>Time</th>
                        <th>Rate/Hour</th>
                        <th>Actions</th>
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
                                <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Are you sure you want to register the exit?')">
                                    <i class="fas fa-sign-out-alt"></i> Register Exit
                                </button>
                            </form>
                            @can('eliminar-estacionamiento')
                            <form action="{{ route('estacionamiento.destroy', $estacionamiento) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this record?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection