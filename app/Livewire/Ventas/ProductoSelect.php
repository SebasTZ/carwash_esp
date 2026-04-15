<?php

namespace App\Livewire\Ventas;

use App\Models\Producto;
use Livewire\Component;

class ProductoSelect extends Component
{
    public string $name = 'producto_id';
    public string $inputId = 'producto_id';
    public string $placeholder = 'Buscar un producto aqui';
    public ?string $selected = null;
    public string $selectedLabel = '';
    public string $search = '';
    public int $limit = 20;

    public function mount(
        ?string $value = null,
        string $name = 'producto_id',
        ?string $inputId = null,
        string $placeholder = 'Buscar un producto aqui'
    ): void {
        abort_unless(auth()->check(), 401);
        $this->name = $name;
        $this->inputId = $inputId ?: $name;
        $this->placeholder = $placeholder;
        $this->selected = ($value !== null && $value !== '') ? (string) $value : null;

        $this->syncSelectedLabel();
    }

    public function getResultsProperty(): array
    {
        abort_unless(auth()->user()?->hasAnyRole(['admin', 'superadmin', 'cajero', 'vendedor']), 403);
        $search = trim($this->search);

        $productos = Producto::query()
            ->where('estado', 1)
            ->where(function ($query) {
                $query->where('es_servicio_lavado', true)
                    ->orWhere('stock', '>', 0);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('nombre', 'like', "%{$search}%")
                        ->orWhere('codigo', 'like', "%{$search}%");
                });
            })
            ->orderByRaw('CASE WHEN es_servicio_lavado THEN 0 ELSE 1 END')
            ->orderBy('nombre')
            ->limit($this->limit)
            ->get(['id', 'codigo', 'nombre', 'stock', 'precio_venta', 'es_servicio_lavado']);

        return $productos->map(function (Producto $producto) {
            return [
                'value' => (string) $producto->id,
                'label' => $producto->codigo . ' - ' . $producto->nombre,
                'stock' => (float) $producto->stock,
                'precio_venta' => (float) $producto->precio_venta,
                'es_servicio_lavado' => (bool) $producto->es_servicio_lavado,
            ];
        })->all();
    }

    public function selectOption($value): void
    {
        $value = trim((string) $value);

        if ($value === '') {
            return;
        }

        $option = collect($this->results)->firstWhere('value', $value);

        if (!is_array($option)) {
            return;
        }

        $this->selected = $value;
        $this->selectedLabel = (string) ($option['label'] ?? '');
        $this->search = '';

        $this->dispatchSelectionUpdated($option);
    }

    public function clearSelection(): void
    {
        $this->selected = null;
        $this->selectedLabel = '';
        $this->search = '';

        $this->dispatchSelectionUpdated();
    }

    public function syncFromExternal($field = null, $value = null, $label = null): void
    {
        abort_unless(auth()->check(), 401);

        $field = trim((string) $field);
        if ($field !== $this->inputId) {
            return;
        }

        $normalizedValue = trim((string) ($value ?? ''));
        if ($normalizedValue === '') {
            $this->selected = null;
            $this->selectedLabel = '';
            $this->search = '';
            return;
        }

        $this->selected = $normalizedValue;
        $this->selectedLabel = trim((string) ($label ?? ''));

        if ($this->selectedLabel === '') {
            $this->syncSelectedLabel();
        }
    }

    protected function syncSelectedLabel(): void
    {
        if (!$this->selected) {
            $this->selectedLabel = '';
            return;
        }

        $producto = Producto::query()
            ->whereKey($this->selected)
            ->first(['id', 'codigo', 'nombre']);

        if (!$producto) {
            $this->selected = null;
            $this->selectedLabel = '';
            return;
        }

        $this->selectedLabel = $producto->codigo . ' - ' . $producto->nombre;
    }

    protected function dispatchSelectionUpdated(array $option = []): void
    {
        $config = [];

        if (!empty($option) && $this->selected) {
            $config = [
                'stock' => (float) ($option['stock'] ?? 0),
                'precio_venta' => (float) ($option['precio_venta'] ?? 0),
                'es_servicio_lavado' => (bool) ($option['es_servicio_lavado'] ?? false),
                'label' => (string) ($option['label'] ?? ''),
            ];
        }

        $this->dispatch(
            'venta-select-updated',
            field: $this->inputId,
            value: (string) ($this->selected ?? ''),
            label: $this->selectedLabel,
            config: $config,
        );
    }

    public function render()
    {
        return view('livewire.ventas.producto-select');
    }
}
