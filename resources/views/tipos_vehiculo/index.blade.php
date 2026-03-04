@extends('layouts.app')

@section('title','Tipos de Vehículo')

@section('content')
<div class="container-fluid px-4">
    <div class="cw-page-header mt-4">
        <h1 class="cw-page-title">Tipos de Vehículo</h1>
        @can('crear-tipo-vehiculo')
        <div class="cw-page-actions">
            <a href="{{ route('tipos_vehiculo.create') }}" class="btn btn-primary">Agregar tipo de vehículo</a>
        </div>
        @endcan
    </div>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Tipos de Vehículo</li>
    </ol>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Tabla de Tipos de Vehículo
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered" id="tiposVehiculoTable"></table>
            <div class="mt-3">
                <x-pagination-info :paginator="$tipos" entity="tipos de vehículo" />
            </div>
        </div>
    </div>
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

        const tiposData = @json($tipos->items());
        const canEdit = String(@json((bool)auth()->user()->can('editar-tipo-vehiculo'))) === 'true';

        const actions = [];
        if (canEdit) {
            actions.push({
                label: 'Editar',
                class: 'btn-info',
                icon: '',
                callback: (row) => {
                    window.location.href = `/tipos_vehiculo/${row.id}/edit`;
                }
            });
        }

        const config = {
            searchable: true,
            searchPlaceholder: 'Buscar tipo de vehículo...',
            sortable: true,
            perPage: 15,
            data: tiposData,
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
                }
            ],
            actions: actions
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
