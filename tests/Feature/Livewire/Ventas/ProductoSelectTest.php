<?php

namespace Tests\Feature\Livewire\Ventas;

use App\Livewire\Ventas\ProductoSelect;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductoSelectTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function filtra_productos_por_codigo_o_nombre(): void
    {
        Producto::factory()->create([
            'codigo' => 'LAV001',
            'nombre' => 'Lavado Premium',
            'estado' => 1,
            'stock' => 5,
            'es_servicio_lavado' => true,
        ]);

        Producto::factory()->create([
            'codigo' => 'ACE999',
            'nombre' => 'Aceite Motor',
            'estado' => 1,
            'stock' => 20,
            'es_servicio_lavado' => false,
        ]);

        Livewire::test(ProductoSelect::class)
            ->set('search', 'LAV')
            ->assertSee('LAV001 - Lavado Premium')
            ->assertDontSee('ACE999 - Aceite Motor');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function selecciona_producto_y_actualiza_estado(): void
    {
        $producto = Producto::factory()->create([
            'codigo' => 'PROD777',
            'nombre' => 'Shampoo Espuma',
            'estado' => 1,
            'stock' => 12,
            'precio_venta' => 25.5,
            'es_servicio_lavado' => false,
        ]);

        Livewire::test(ProductoSelect::class)
            ->call('selectOption', (string) $producto->id)
            ->assertSet('selected', (string) $producto->id)
            ->assertSet('selectedLabel', 'PROD777 - Shampoo Espuma')
            ->assertDispatched('venta-select-updated');
    }
}
