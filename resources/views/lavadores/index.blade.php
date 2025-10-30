@extends('layouts.app')

@vite('resources/js/app.js')
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
        <tbody></tbody>
    </table>

    <x-pagination-info :paginator="$lavadores" entity="lavadores" />
    <script>
    window.lavadoresData = @json($lavadores->items());
    window.lavadoresCanEdit = String(@json((bool)auth()->user()->can('editar-lavador'))) === 'true';
    window.lavadoresCanDelete = String(@json((bool)auth()->user()->can('eliminar-lavador'))) === 'true';
    </script>
</div>

<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
    const tableElement = document.getElementById('lavadoresTable');
    if (!tableElement) {
        console.error('Tabla no encontrada');
        return;
    }
    const lavadores = window.lavadoresData || [];
    const canEdit = window.lavadoresCanEdit;
    const canDelete = window.lavadoresCanDelete;
    console.log('Permisos para botones:', { canEdit, canDelete });
    console.log('Datos lavadores:', lavadores);

    // Construir acciones en JS puro
    const actions = [];
    if (canEdit) {
        actions.push({
            label: 'Editar',
            class: 'btn-info',
            icon: '',
            callback: (row) => {
                window.location.href = `/lavadores/${row.id}/edit`;
            }
        });
    }
    if (canDelete) {
        actions.push({
            label: 'Desactivar',
            class: 'btn-danger',
            icon: '',
            callback: (row) => {
                const form = document.createElement('form');
                form.action = `/lavadores/${row.id}`;
                form.method = 'POST';
                form.style.display = 'none';
                form.innerHTML = `
                    <input type='hidden' name='_token' value='${document.querySelector('meta[name="csrf-token"]').content}'>
                    <input type='hidden' name='_method' value='DELETE'>
                `;
                document.body.appendChild(form);
                if (confirm('¿Estás seguro?')) {
                    form.submit();
                } else {
                    form.remove();
                }
            }
        });
    }

    const config = {
        searchable: true,
        searchPlaceholder: 'Buscar lavador...',
        sortable: true,
        perPage: 15,
        data: lavadores,
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
                    return `<span class='badge ${badgeClass}'>${estado.charAt(0).toUpperCase() + estado.slice(1)}</span>`;
                }
            }
        ],
        actions: actions
    };
    new window.CarWash.DynamicTable(tableElement, config);
    console.log('✅ DynamicTable inicializado correctamente para Lavadores');
});
</script>
@endsection
