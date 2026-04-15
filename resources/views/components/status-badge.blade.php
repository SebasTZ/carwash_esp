{{--
    Componente: x-status-badge
    Badge de estado reutilizable para citas, ventas, compras, lavados, etc.

    Props:
        status  — clave del estado (string)
        map     — array asociativo ['estado' => ['label' => '...', 'class' => 'badge bg-...', 'icon' => 'fas fa-...']]
                  Si no se pasa, usa el mapa por defecto del sistema.

    Uso:
        <x-status-badge :status="$cita->estado" />
        <x-status-badge status="completada" :map="$customMap" />
--}}

@props([
    'status',
    'map' => null,
])

@php
    $defaultMap = [
        // Citas
        'pendiente'   => ['label' => 'Pendiente',   'class' => 'badge bg-warning text-white',  'icon' => 'fas fa-clock'],
        'en_proceso'  => ['label' => 'En Proceso',  'class' => 'badge bg-info text-white',     'icon' => 'fas fa-spinner fa-spin'],
        'completada'  => ['label' => 'Completada',  'class' => 'badge bg-success text-white',  'icon' => 'fas fa-check-circle'],
        'cancelada'   => ['label' => 'Cancelada',   'class' => 'badge bg-danger text-white',   'icon' => 'fas fa-ban'],
        // Ventas / Compras
        'activo'      => ['label' => 'Activo',      'class' => 'badge bg-success text-white',  'icon' => 'fas fa-check'],
        'inactivo'    => ['label' => 'Inactivo',    'class' => 'badge bg-secondary text-white','icon' => 'fas fa-minus-circle'],
        'anulado'     => ['label' => 'Anulado',     'class' => 'badge bg-danger text-white',   'icon' => 'fas fa-times'],
        'pagado'      => ['label' => 'Pagado',      'class' => 'badge bg-success text-white',  'icon' => 'fas fa-dollar-sign'],
        'pendiente_pago' => ['label' => 'Pend. Pago', 'class' => 'badge bg-warning text-dark', 'icon' => 'fas fa-hourglass-half'],
        // Lavados
        'en_espera'   => ['label' => 'En Espera',   'class' => 'badge bg-secondary text-white','icon' => 'fas fa-pause-circle'],
        'en_lavado'   => ['label' => 'En Lavado',   'class' => 'badge bg-info text-white',     'icon' => 'fas fa-tint'],
        'listo'       => ['label' => 'Listo',        'class' => 'badge bg-success text-white', 'icon' => 'fas fa-flag-checkered'],
        // Genérico booleano
        'true'        => ['label' => 'Sí',          'class' => 'badge bg-success text-white',  'icon' => 'fas fa-check'],
        'false'       => ['label' => 'No',           'class' => 'badge bg-secondary text-white','icon' => 'fas fa-times'],
        '1'           => ['label' => 'Sí',          'class' => 'badge bg-success text-white',  'icon' => 'fas fa-check'],
        '0'           => ['label' => 'No',           'class' => 'badge bg-secondary text-white','icon' => 'fas fa-times'],
    ];

    $resolvedMap = $map ?? $defaultMap;
    $key = (string) $status;
    $entry = $resolvedMap[$key] ?? ['label' => ucfirst($key), 'class' => 'badge bg-secondary text-white', 'icon' => 'fas fa-circle'];
@endphp

<span class="{{ $entry['class'] }} d-inline-flex align-items-center gap-1">
    <i class="{{ $entry['icon'] }}"></i>
    {{ $entry['label'] }}
</span>
