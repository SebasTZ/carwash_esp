@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Lavadores</h1>
    @can('crear-lavador')
        <a href="{{ route('lavadores.create') }}" class="btn btn-primary mb-3">Agregar Lavador</a>
    @endcan
    
    <table class="table table-striped table-bordered" id="lavadoresTable">
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

    <x-pagination-info :paginator="$lavadores" entity="lavadores" />
</div>

<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof window.CarWash?.DynamicTable !== 'function') {
            console.error('DynamicTable no está disponible en window.CarWash');
            return;
        }

        const tableElement = document.getElementById('lavadoresTable');
        if (!tableElement) {
            console.error('Tabla no encontrada');
            return;
        }

        const config = {
            searchable: true,
            searchPlaceholder: 'Buscar lavador...',
            sortable: true,
            perPage: 15,
            columns: [
                {
                    key: 'nombre',
                    label: 'Nombre',
                    sortable: true,
                    searchable: true
                },
                {
                    key: 'dni',
                    label: 'DNI',
                    sortable: true,
                    searchable: true
                },
                {
                    key: 'telefono',
                    label: 'Teléfono',
                    sortable: true,
                    searchable: true,
                    formatter: (value) => {
                        return value || '<span class="text-muted">-</span>';
                    }
                },
                {
                    key: 'estado',
                    label: 'Estado',
                    sortable: true,
                    searchable: true,
                    formatter: (value) => {
                        const estado = String(value).toLowerCase();
                        const badgeClass = estado === 'activo' ? 'bg-success' : 'bg-secondary';
                        return `<span class="badge ${badgeClass}">${estado.charAt(0).toUpperCase() + estado.slice(1)}</span>`;
                    }
                },
                {
                    key: 'acciones',
                    label: 'Acciones',
                    sortable: false,
                    searchable: false
                }
            ]
        };

        try {
            new window.CarWash.DynamicTable(tableElement, config);
            console.log('✅ DynamicTable inicializado correctamente para Lavadores');
        } catch (error) {
            console.error('❌ Error al inicializar DynamicTable:', error);
        }
    });
</script>
@endsection
