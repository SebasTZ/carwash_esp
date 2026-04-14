<?php

namespace Tests\Feature\Controllers;

use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Presentacione;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductoControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            \Illuminate\Auth\Middleware\Authenticate::class,
            \Illuminate\Auth\Middleware\Authorize::class,
            \Spatie\Permission\Middleware\PermissionMiddleware::class,
            \Spatie\Permission\Middleware\RoleMiddleware::class,
            \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function store_crea_producto_y_asocia_categorias()
    {
        $marca = Marca::factory()->create();
        $presentacion = Presentacione::factory()->create();
        $categoria = Categoria::factory()->create();

        $payload = [
            'codigo' => 'PRD-1001',
            'nombre' => 'Shampoo Premium',
            'descripcion' => 'Producto de prueba',
            'fecha_vencimiento' => now()->addYear()->toDateString(),
            'marca_id' => $marca->id,
            'presentacione_id' => $presentacion->id,
            'categorias' => [$categoria->id],
        ];

        $response = $this->post(route('productos.store'), $payload);

        $response->assertRedirect(route('productos.index'));
        $response->assertSessionHas('success', 'Producto registrado');

        $producto = Producto::where('codigo', 'PRD-1001')->first();
        $this->assertNotNull($producto);
        $this->assertSame('Shampoo Premium', $producto->nombre);
        $this->assertFalse((bool) $producto->es_servicio_lavado);

        $this->assertDatabaseHas('categoria_producto', [
            'producto_id' => $producto->id,
            'categoria_id' => $categoria->id,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function update_de_servicio_lavado_actualiza_precio_y_sincroniza_categorias()
    {
        $marca = Marca::factory()->create();
        $presentacion = Presentacione::factory()->create();

        $categoriaInicial = Categoria::factory()->create();
        $categoriaNueva = Categoria::factory()->create();

        $producto = Producto::factory()->create([
            'marca_id' => $marca->id,
            'presentacione_id' => $presentacion->id,
            'es_servicio_lavado' => false,
            'precio_venta' => null,
        ]);

        $producto->categorias()->attach($categoriaInicial->id);

        $response = $this->put(route('productos.update', $producto), [
            'codigo' => $producto->codigo,
            'nombre' => $producto->nombre,
            'descripcion' => 'Servicio actualizado',
            'fecha_vencimiento' => now()->addMonths(6)->toDateString(),
            'marca_id' => $marca->id,
            'presentacione_id' => $presentacion->id,
            'categorias' => [$categoriaNueva->id],
            'es_servicio_lavado' => 1,
            'precio_venta' => 45.5,
        ]);

        $response->assertRedirect(route('productos.index'));
        $response->assertSessionHas('success', 'Producto editado');

        $producto->refresh();
        $this->assertTrue((bool) $producto->es_servicio_lavado);
        $this->assertSame('45.50', number_format((float) $producto->precio_venta, 2, '.', ''));

        $this->assertDatabaseHas('categoria_producto', [
            'producto_id' => $producto->id,
            'categoria_id' => $categoriaNueva->id,
        ]);

        $this->assertDatabaseMissing('categoria_producto', [
            'producto_id' => $producto->id,
            'categoria_id' => $categoriaInicial->id,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function destroy_alterna_estado_entre_activo_e_inactivo()
    {
        $producto = Producto::factory()->create(['estado' => 1]);

        $responseEliminar = $this->delete(route('productos.destroy', $producto));
        $responseEliminar->assertRedirect(route('productos.index'));
        $responseEliminar->assertSessionHas('success', 'Producto eliminado');

        $this->assertDatabaseHas('productos', [
            'id' => $producto->id,
            'estado' => 0,
        ]);

        $responseRestaurar = $this->delete(route('productos.destroy', $producto));
        $responseRestaurar->assertRedirect(route('productos.index'));
        $responseRestaurar->assertSessionHas('success', 'Producto restaurado');

        $this->assertDatabaseHas('productos', [
            'id' => $producto->id,
            'estado' => 1,
        ]);
    }
}
