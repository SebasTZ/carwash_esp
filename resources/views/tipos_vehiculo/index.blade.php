@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Tipos de Vehículo</h1>
    @can('crear-tipo-vehiculo')
        <a href="{{ route('tipos_vehiculo.create') }}" class="btn btn-primary mb-3">Agregar tipo de vehículo</a>
    @endcan
    
    <table class="table table-striped table-bordered" id="tiposVehiculoTable">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Comisión</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tipos as $tipo)
                <tr>
                    <td>{{ $tipo->nombre }}</td>
                    <td>{{ number_format($tipo->comision, 2) }}</td>
                    <td>{{ ucfirst($tipo->estado) }}</td>
                    <td>
                        @can('editar-tipo-vehiculo')
                            <a href="{{ route('tipos_vehiculo.edit', ['tipos_vehiculo' => $tipo->id]) }}" class="btn btn-sm btn-info">Editar</a>
                        @endcan
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <x-pagination-info :paginator="$tipos" entity="tipos de vehículo" />
</div>

<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof window.CarWash?.DynamicTable !== 'function') {
            console.error('DynamicTable no está disponible en window.CarWash');
            return;
        }

        const tableElement = document.getElementById('tiposVehiculoTable');
        if (!tableElement) {
            console.error('Tabla no encontrada');
            return;
        }

        const config = {
            searchable: true,
            searchPlaceholder: 'Buscar tipo de vehículo...',
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
                    key: 'comision',
                    label: 'Comisión',
                    sortable: true,
                    searchable: true,
                    formatter: (value) => {
                        const num = parseFloat(value);
                        return isNaN(num) ? value : `S/ ${num.toFixed(2)}`;
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
            console.log('✅ DynamicTable inicializado correctamente para TipoVehiculo');
        } catch (error) {
            console.error('❌ Error al inicializar DynamicTable:', error);
        }
    });
</script>
@endsection
