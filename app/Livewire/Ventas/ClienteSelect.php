<?php

namespace App\Livewire\Ventas;

use App\Livewire\Concerns\AuthorizesLivewirePermissions;
use App\Models\Cliente;
use Livewire\Component;

class ClienteSelect extends Component
{
    use AuthorizesLivewirePermissions;

    public string $name = 'cliente_id';
    public string $inputId = 'cliente_id';
    public string $placeholder = 'Buscar cliente';
    public ?string $selected = null;
    public string $selectedLabel = '';
    public string $search = '';
    public int $limit = 20;

    public function mount(
        ?string $value = null,
        string $name = 'cliente_id',
        ?string $inputId = null,
        string $placeholder = 'Buscar cliente'
    ): void {
        $this->ensureAuthenticated();
        $this->name = $name;
        $this->inputId = $inputId ?: $name;
        $this->placeholder = $placeholder;
        $this->selected = ($value !== null && $value !== '') ? (string) $value : null;

        $this->syncSelectedLabel();
    }

    public function getResultsProperty(): array
    {
        $this->ensurePermissionOrRole('ver-cliente', ['admin', 'superadmin', 'cajero', 'vendedor']);

        $search = trim($this->search);

        $clientes = Cliente::query()
            ->join('personas', 'personas.id', '=', 'clientes.persona_id')
            ->where('personas.estado', 1)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('personas.razon_social', 'like', "%{$search}%")
                        ->orWhere('personas.numero_documento', 'like', "%{$search}%");
                });
            })
            ->orderBy('personas.razon_social')
            ->limit($this->limit)
            ->get([
                'clientes.id as id',
                'personas.razon_social as razon_social',
                'personas.numero_documento as numero_documento',
            ]);

        return $clientes->map(function ($cliente) {
            return [
                'value' => (string) $cliente->id,
                'label' => $cliente->razon_social . ' - ' . $cliente->numero_documento,
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

        $this->dispatchSelectionUpdated();
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
        $this->ensureAuthenticated();

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

        $cliente = Cliente::query()
            ->join('personas', 'personas.id', '=', 'clientes.persona_id')
            ->where('clientes.id', $this->selected)
            ->first([
                'clientes.id as id',
                'personas.razon_social as razon_social',
                'personas.numero_documento as numero_documento',
            ]);

        if (!$cliente) {
            $this->selected = null;
            $this->selectedLabel = '';
            return;
        }

        $this->selectedLabel = $cliente->razon_social . ' - ' . $cliente->numero_documento;
    }

    protected function dispatchSelectionUpdated(): void
    {
        $this->dispatch(
            'venta-select-updated',
            field: $this->inputId,
            value: (string) ($this->selected ?? ''),
            label: $this->selectedLabel,
            config: [],
        );
    }

    public function render()
    {
        return view('livewire.ventas.cliente-select');
    }
}
