<?php

namespace Tests\Feature\Controllers;

use App\Models\Comprobante;
use App\Models\Compra;
use App\Models\Documento;
use App\Models\Persona;
use App\Models\Producto;
use App\Models\Proveedore;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class CompraControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Proveedore $proveedor;
    protected Comprobante $comprobante;

    protected function setUp(): void
    {
        parent::setUp();

        \Spatie\Permission\Models\Permission::create(['name' => 'ver-compra']);
        \Spatie\Permission\Models\Permission::create(['name' => 'crear-compra']);
        \Spatie\Permission\Models\Permission::create(['name' => 'mostrar-compra']);
        \Spatie\Permission\Models\Permission::create(['name' => 'eliminar-compra']);
        \Spatie\Permission\Models\Permission::create(['name' => 'reporte-personalizado-compra']);

        $role = \Spatie\Permission\Models\Role::create(['name' => 'admin-compra-test']);
        $role->givePermissionTo(['ver-compra', 'crear-compra', 'mostrar-compra', 'eliminar-compra', 'reporte-personalizado-compra']);

        $this->user = User::factory()->create();
        $this->user->assignRole('admin-compra-test');
        $this->actingAs($this->user);

        $documento = Documento::factory()->create();
        $personaProveedor = Persona::factory()->create([
            'documento_id' => $documento->id,
            'estado' => 1,
            'tipo_persona' => 'Proveedor',
        ]);

        $this->proveedor = Proveedore::factory()->create([
            'persona_id' => $personaProveedor->id,
        ]);

        $this->comprobante = Comprobante::factory()->create();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function store_crea_compra_y_actualiza_stock_de_productos()
    {
        $producto = Producto::factory()->create([
            'stock' => 5,
            'es_servicio_lavado' => false,
            'estado' => 1,
        ]);

        $response = $this->post(route('compras.store'), [
            'proveedore_id' => $this->proveedor->id,
            'comprobante_id' => $this->comprobante->id,
            'numero_comprobante' => 'C-1001',
            'impuesto' => 18,
            'fecha_hora' => now()->format('Y-m-d H:i:s'),
            'total' => 45,
            'arrayidproducto' => [$producto->id],
            'arraycantidad' => [3],
            'arraypreciocompra' => [10],
            'arrayprecioventa' => [15],
        ]);

        $response->assertRedirect(route('compras.index'));
        $response->assertSessionHas('success', 'Compra exitosa');

        $compra = Compra::where('numero_comprobante', 'C-1001')->first();
        $this->assertNotNull($compra);

        $this->assertDatabaseHas('compra_producto', [
            'compra_id' => $compra->id,
            'producto_id' => $producto->id,
            'cantidad' => 3,
        ]);

        $producto->refresh();
        $this->assertSame(8, $producto->stock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function store_si_stock_service_falla_hace_rollback_y_muestra_error()
    {
        $producto = Producto::factory()->create([
            'stock' => 10,
            'es_servicio_lavado' => false,
            'estado' => 1,
        ]);

        $stockMock = Mockery::mock(StockService::class);
        $stockMock
            ->shouldReceive('incrementarStock')
            ->once()
            ->andThrow(new \Exception('Fallo en stock'));

        $this->app->instance(StockService::class, $stockMock);

        $response = $this->from(route('compras.create'))->post(route('compras.store'), [
            'proveedore_id' => $this->proveedor->id,
            'comprobante_id' => $this->comprobante->id,
            'numero_comprobante' => 'C-1002',
            'impuesto' => 18,
            'fecha_hora' => now()->format('Y-m-d H:i:s'),
            'total' => 60,
            'arrayidproducto' => [$producto->id],
            'arraycantidad' => [2],
            'arraypreciocompra' => [20],
            'arrayprecioventa' => [30],
        ]);

        $response->assertRedirect(route('compras.create'));
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('compras', ['numero_comprobante' => 'C-1002']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function destroy_marca_compra_como_inactiva()
    {
        $compra = Compra::factory()->create([
            'proveedore_id' => $this->proveedor->id,
            'comprobante_id' => $this->comprobante->id,
            'estado' => 1,
        ]);

        $response = $this->delete(route('compras.destroy', $compra));

        $response->assertRedirect(route('compras.index'));
        $response->assertSessionHas('success', 'Compra eliminada');

        $this->assertDatabaseHas('compras', [
            'id' => $compra->id,
            'estado' => 0,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function reporte_personalizado_filtra_compras_por_rango()
    {
        $compraEnRango = Compra::factory()->create([
            'proveedore_id' => $this->proveedor->id,
            'comprobante_id' => $this->comprobante->id,
            'fecha_hora' => '2026-04-10 10:00:00',
        ]);

        Compra::factory()->create([
            'proveedore_id' => $this->proveedor->id,
            'comprobante_id' => $this->comprobante->id,
            'fecha_hora' => '2026-03-05 10:00:00',
        ]);

        $response = $this->get(route('compras.reporte.personalizado', [
            'fecha_inicio' => '2026-04-01',
            'fecha_fin' => '2026-04-30',
        ]));

        $response->assertOk();
        $response->assertViewIs('compra.reporte');
        $response->assertViewHas('compras', function ($compras) use ($compraEnRango) {
            return $compras->count() === 1 && $compras->first()->id === $compraEnRango->id;
        });
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function index_retorna_403_si_usuario_no_tiene_permiso(): void
    {
        $sinPermisos = User::factory()->create();
        $this->actingAs($sinPermisos);

        $response = $this->get(route('compras.index'));

        $response->assertStatus(403);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function store_retorna_403_si_usuario_no_tiene_permiso(): void
    {
        $sinPermisos = User::factory()->create();
        $this->actingAs($sinPermisos);

        $response = $this->post(route('compras.store'), []);

        $response->assertStatus(403);
    }
}
