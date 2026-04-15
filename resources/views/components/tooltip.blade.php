{{--
    Componente: x-tooltip
    Inicializa automáticamente un tooltip de Bootstrap 5 en el elemento slot.
    Elimina la necesidad de llamar a initTooltips() manualmente para cada elemento.

    Props:
        text      — texto del tooltip (requerido)
        placement — 'top' | 'bottom' | 'left' | 'right' (default: 'top')
        trigger   — 'hover focus' | 'hover' | 'focus' | 'click' (default: 'hover focus')

    Uso:
        <x-tooltip text="Ver detalles">
            <a href="..."><i class="fas fa-eye"></i></a>
        </x-tooltip>

        <x-tooltip text="Exportar a Excel" placement="bottom">
            <button class="btn btn-sm btn-outline-success">Excel</button>
        </x-tooltip>
--}}

@props([
    'text',
    'placement' => 'top',
    'trigger'   => 'hover focus',
])

<span
    x-data="{ tooltipInstance: null }"
    x-init="
        $nextTick(() => {
            if (window.bootstrap?.Tooltip) {
                tooltipInstance = new bootstrap.Tooltip($el.firstElementChild, {
                    title: $el.dataset.tooltipText,
                    placement: '{{ $placement }}',
                    trigger: '{{ $trigger }}',
                });
            }
        });

        return () => {
            tooltipInstance?.dispose();
            tooltipInstance = null;
        }
    "
    data-tooltip-text="{{ e($text) }}"
    class="d-inline-block"
>
    {{ $slot }}
</span>
