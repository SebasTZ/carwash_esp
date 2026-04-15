@props([
    'name',
    'id'        => null,
    'value'     => null,
    'placeholder' => 'Seleccione una opción',
    'required'  => false,
    'disabled'  => false,
    'class'     => '',
    'options'   => [],   // array de ['value' => ..., 'label' => ..., 'tokens' => '...']
])

@php
    $inputId = $id ?? $name;
    $selectedValue = old($name, $value);
@endphp

<div
    x-data="{
        open: false,
        search: '',
        selected: {{ $selectedValue !== null ? "'" . e($selectedValue) . "'" : 'null' }},
        selectedLabel: '',
        hasError: false,
        options: {{ Js::from($options) }},
        get filtered() {
            if (!this.search) return this.options;
            const q = this.search.toLowerCase();
            return this.options.filter(o =>
                o.label.toLowerCase().includes(q) ||
                (o.tokens ?? '').toLowerCase().includes(q)
            );
        },
        validate() {
            if (!{{ $required ? 'true' : 'false' }}) return true;
            if (this.selected !== null && this.selected !== '' && this.selected !== undefined) return true;
            this.hasError = true;
            return false;
        },
        selectOption(opt) {
            this.selected = opt.value;
            this.selectedLabel = opt.label;
            this.hasError = false;
            this.open = false;
            this.search = '';
            this.$refs.hidden.dataset.selectedLabel = this.selectedLabel || '';
            this.$nextTick(() => {
                this.$refs.hidden.dispatchEvent(new Event('change', { bubbles: true }));
            });
        },
        init() {
            const found = this.options.find(o => String(o.value) === String(this.selected));
            this.selectedLabel = found ? found.label : '';
            this.$refs.hidden.dataset.selectedLabel = this.selectedLabel || '';
            const form = this.$el.closest('form');
            if (form && {{ $required ? 'true' : 'false' }}) {
                form.addEventListener('submit', (e) => {
                    if (!this.selected) {
                        e.preventDefault();
                        this.hasError = true;
                        this.$el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                });
            }
        }
    }"
    x-init="init()"
    class="position-relative {{ $class }}"
>
    {{-- Hidden real input para el form submit --}}
    <input
        type="hidden"
        name="{{ $name }}"
        id="{{ $inputId }}"
        x-ref="hidden"
        :value="selected"
        :data-selected-label="selectedLabel || ''"
        @if($required) required @endif
    >

    {{-- Trigger visible --}}
    <button
        type="button"
        class="form-control d-flex justify-content-between align-items-center text-start"
        :class="{ 'border-primary': open, 'is-invalid border-danger': hasError }"
        @click="open = !open"
        @keydown.escape="open = false"
        @if($disabled) disabled @endif
        aria-haspopup="listbox"
        :aria-expanded="open"
    >
        <span x-text="selectedLabel || '{{ $placeholder }}'" :class="{ 'text-muted': !selectedLabel }"></span>
        <i class="fas fa-chevron-down ms-2 small" :class="{ 'fa-chevron-up': open }"></i>
    </button>
    <div x-show="hasError" class="invalid-feedback d-block" x-text="'Debe seleccionar una opción'"></div>

    {{-- Dropdown --}}
    <div
        x-show="open"
        x-transition.opacity.scale.duration.150ms
        @click.outside="open = false"
        class="position-absolute w-100 bg-white border rounded shadow-sm mt-1"
        style="max-height: 280px; overflow-y: auto; z-index: 1050;"
        role="listbox"
    >
        {{-- Búsqueda --}}
        <div class="px-2 py-2 border-bottom sticky-top bg-white">
            <input
                type="text"
                class="form-control form-control-sm"
                placeholder="Buscar..."
                x-model="search"
                x-ref="searchInput"
                @keydown.escape="open = false"
                x-init="$watch('open', v => v && $nextTick(() => $refs.searchInput?.focus()))"
            >
        </div>

        {{-- Opciones --}}
        <template x-for="opt in filtered" :key="opt.value">
            <div
                class="px-3 py-2 cursor-pointer"
                :class="{ 'bg-primary text-white': String(opt.value) === String(selected), 'select-search-option': true }"
                @click="selectOption(opt)"
                role="option"
                :aria-selected="String(opt.value) === String(selected)"
                x-text="opt.label"
            ></div>
        </template>

        <template x-if="filtered.length === 0">
            <div class="px-3 py-2 text-muted small">No hay resultados para "<span x-text="search"></span>"</div>
        </template>
    </div>
</div>

@once
@push('css')
<style>
.select-search-option:hover {
    background-color: #f0f4ff;
    cursor: pointer;
}
</style>
@endpush
@endonce
