<?php

namespace Tests\Feature\Livewire\Ventas;

use App\Livewire\Ventas\ProductoSelect;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ProductoSelectTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->user = User::factory()->create();
        $this->user->assignRole(\Spatie\Permission\Models\Role::findOrCreate('cajero'));
    }

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

        Livewire::actingAs($this->user)
            ->test(ProductoSelect::class)
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

        Livewire::actingAs($this->user)
            ->test(ProductoSelect::class)
            ->call('selectOption', (string) $producto->id)
            ->assertSet('selected', (string) $producto->id)
            ->assertSet('selectedLabel', 'PROD777 - Shampoo Espuma')
            ->assertDispatched('venta-select-updated');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function select_option_emite_evento_con_config(): void
    {
        $producto = Producto::factory()->create([
            'codigo' => 'SRV001',
            'nombre' => 'Lavado Express',
            'estado' => 1,
            'stock' => 0,
            'precio_venta' => 35.0,
            'es_servicio_lavado' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(ProductoSelect::class)
            ->call('selectOption', (string) $producto->id)
            ->assertDispatched('venta-select-updated', function ($event, $params) use ($producto) {
                return isset($params['config']['stock'])
                    && isset($params['config']['precio_venta'])
                    && isset($params['config']['es_servicio_lavado'])
                    && $params['config']['es_servicio_lavado'] === true;
            });
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function clear_selection_limpia_estado(): void
    {
        $producto = Producto::factory()->create([
            'codigo' => 'CLR001',
            'nombre' => 'Producto Limpiar',
            'estado' => 1,
            'stock' => 10,
            'es_servicio_lavado' => false,
        ]);

        Livewire::actingAs($this->user)
            ->test(ProductoSelect::class, ['value' => (string) $producto->id])
            ->call('clearSelection')
            ->assertSet('selected', null)
            ->assertSet('selectedLabel', '')
            ->assertDispatched('venta-select-updated');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function productos_sin_stock_no_aparecen_salvo_servicios(): void
    {
        Producto::factory()->create([
            'codigo' => 'NOSTOCK',
            'nombre' => 'Producto Sin Stock',
            'estado' => 1,
            'stock' => 0,
            'es_servicio_lavado' => false,
        ]);

        Producto::factory()->create([
            'codigo' => 'SRVSLOCK',
            'nombre' => 'Servicio Sin Stock',
            'estado' => 1,
            'stock' => 0,
            'es_servicio_lavado' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(ProductoSelect::class)
            ->assertDontSee('Producto Sin Stock')
            ->assertSee('Servicio Sin Stock');
    }
}
