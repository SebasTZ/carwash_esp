{{--
    Componente: x-confirm-delete
    Botón de eliminar con confirmación inline vía Alpine.js (sin modal Bootstrap).
    Usa un micro-popover de confirmación que aparece junto al botón.

    Props:
        action       — URL de la acción DELETE (requerido)
        label        — texto del botón (default: "Eliminar")
        message      — mensaje de confirmación
        confirmText  — texto del botón de confirmar
        size         — 'sm' | 'md' (default: 'sm')
        iconOnly     — mostrar solo icono (default: false)

    Uso:
        <x-confirm-delete :action="route('clientes.destroy', $cliente)" />
        <x-confirm-delete :action="route('ventas.destroy', $venta)" message="Se eliminará la venta y sus detalles." />
--}}

@props([
    'action',
    'label'       => 'Eliminar',
    'message'     => '¿Está seguro de que desea eliminar este registro? Esta acción no se puede deshacer.',
    'confirmText' => 'Sí, eliminar',
    'size'        => 'sm',
    'iconOnly'    => false,
])

<div
    x-data="{ confirming: false }"
    x-effect="if (confirming) { $nextTick(() => $refs.confirmBox?.focus()) }"
    class="d-inline-block position-relative"
>
    {{-- Botón inicial --}}
    <button
        type="button"
        class="btn btn-outline-danger btn-{{ $size }}"
        @click="confirming = true"
        x-show="!confirming"
        title="{{ $label }}"
    >
        <i class="fas fa-trash-alt"></i>
        @if(!$iconOnly)
        <span class="ms-1">{{ $label }}</span>
        @endif
    </button>

    {{-- Mini-confirmación inline --}}
    <div
        x-show="confirming"
        x-transition.opacity.scale.duration.150ms
        class="d-inline-flex align-items-center bg-white border border-danger rounded px-2 py-1 shadow-sm"
        style="white-space: nowrap; gap: 0.5rem;"
        @keydown.escape.stop.prevent="confirming = false"
        @click.outside="confirming = false"
        x-ref="confirmBox"
        tabindex="-1"
    >
        <span class="small text-danger fw-semibold">
            <i class="fas fa-exclamation-triangle me-1"></i>¿Confirmar?
        </span>
        <form action="{{ $action }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm px-2 py-0">
                {{ $confirmText }}
            </button>
        </form>
        <button
            type="button"
            class="btn btn-outline-secondary btn-sm px-2 py-0"
            @click="confirming = false"
        >
            No
        </button>
    </div>
</div>
