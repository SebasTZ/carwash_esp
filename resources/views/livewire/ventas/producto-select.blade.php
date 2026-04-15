<div
    x-data="{}"
    x-on:venta-livewire-select-sync.window="if ($event.detail && $event.detail.field === @js($inputId)) { $wire.syncFromExternal($event.detail.field, $event.detail.value, $event.detail.label) }"
>
    <div class="input-group">
        <span class="input-group-text"><i class="fas fa-search"></i></span>
        <input
            type="text"
            class="form-control"
            wire:model.live.debounce.250ms="search"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
        >
        @if($selected)
            <button type="button" class="btn btn-outline-secondary" wire:click="clearSelection">
                Limpiar
            </button>
        @endif
    </div>

    @if($selectedLabel)
        <small class="text-success d-block mt-1">Seleccionado: {{ $selectedLabel }}</small>
    @endif

    <div class="list-group mt-2" style="max-height: 220px; overflow-y: auto;">
        @forelse($this->results as $option)
            <button
                type="button"
                wire:key="venta-producto-{{ $option['value'] }}"
                wire:click="selectOption('{{ $option['value'] }}')"
                class="list-group-item list-group-item-action {{ (string) $selected === (string) $option['value'] ? 'active' : '' }}"
            >
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <span>{{ $option['label'] }}</span>
                    @if($option['es_servicio_lavado'])
                        <span class="badge text-bg-info">Servicio</span>
                    @else
                        <small class="text-muted">Stock: {{ (int) $option['stock'] }}</small>
                    @endif
                </div>
            </button>
        @empty
            <div class="list-group-item text-muted small">No se encontraron productos.</div>
        @endforelse
    </div>
</div>
