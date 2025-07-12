@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Vehicle Types</h1>
    @can('crear-tipo-vehiculo')
        <a href="{{ route('tipos_vehiculo.create') }}" class="btn btn-primary mb-3">Add Vehicle Type</a>
    @endcan
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Commission</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tipos as $tipo)
                <tr>
                    <td>{{ $tipo->nombre }}</td>
                    <td>{{ $tipo->comision }}</td>
                    <td>{{ ucfirst($tipo->estado) }}</td>
                    <td>
                        @can('editar-tipo-vehiculo')
                            <a href="{{ route('tipos_vehiculo.edit', ['tipos_vehiculo' => $tipo->id]) }}" class="btn btn-sm btn-info">Edit</a>
                        @endcan
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
