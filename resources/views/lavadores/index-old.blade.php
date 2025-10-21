@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Lavadores</h1>
    @can('crear-lavador')
        <a href="{{ route('lavadores.create') }}" class="btn btn-primary mb-3">Agregar Lavador</a>
    @endcan
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>DNI</th>
                <th>Teléfono</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lavadores as $lavador)
                <tr>
                    <td>{{ $lavador->nombre }}</td>
                    <td>{{ $lavador->dni }}</td>
                    <td>{{ $lavador->telefono }}</td>
                    <td>{{ ucfirst($lavador->estado) }}</td>
                    <td>
                        @can('editar-lavador')
                            <a href="{{ route('lavadores.edit', ['lavadore' => $lavador->id]) }}" class="btn btn-sm btn-info">Editar</a>
                        @endcan
                        @can('eliminar-lavador')
                            <form action="{{ route('lavadores.destroy', ['lavadore' => $lavador->id]) }}" method="POST" style="display:inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro?')">Desactivar</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Paginación usando componente -->
    <x-pagination-info :paginator="$lavadores" entity="lavadores" />
</div>
@endsection
