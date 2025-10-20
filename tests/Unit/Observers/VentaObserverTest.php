<?php

namespace Tests\Unit\Observers;

use Tests\TestCase;
use App\Models\Venta;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Comprobante;
use App\Models\User;
use App\Models\Caracteristica;
use App\Models\Marca;
use App\Models\Presentacione;
use App\Models\Documento;
use App\Models\Persona;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class VentaObserverTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function puede_crear_venta_correctamente()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $documento = Documento::factory()->create();
        $persona = Persona::factory()->create(['documento_id' => $documento->id]);
        $cliente = Cliente::factory()->create(['persona_id' => $persona->id]);
        $comprobante = Comprobante::factory()->create();

        $venta = Venta::create([
            'cliente_id' => $cliente->id,
            'user_id' => $user->id,
            'comprobante_id' => $comprobante->id,
            'numero_comprobante' => 'B001-0001',
            'fecha_hora' => now(),
            'impuesto' => 0,
            'total' => 100.00,
            'estado' => 1,
            'medio_pago' => 'efectivo',
            'servicio_lavado' => false,
        ]);

        $this->assertNotNull($venta->id);
        $this->assertEquals(100.00, $venta->total);
        
        // Verificar que se guardó correctamente
        $this->assertDatabaseHas('ventas', [
            'id' => $venta->id,
            'total' => 100.00,
            'estado' => 1,
        ]);
    }

    /** @test */
    public function venta_tiene_relaciones_correctas()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $caracteristica = Caracteristica::factory()->create();
        $marca = Marca::factory()->create(['caracteristica_id' => $caracteristica->id]);
        $presentacion = Presentacione::factory()->create(['caracteristica_id' => $caracteristica->id]);
        
        $producto = Producto::factory()->create([
            'precio_venta' => 50.00,
            'stock' => 10,
            'marca_id' => $marca->id,
            'presentacione_id' => $presentacion->id,
        ]);

        $documento = Documento::factory()->create();
        $persona = Persona::factory()->create(['documento_id' => $documento->id]);
        $cliente = Cliente::factory()->create(['persona_id' => $persona->id]);
        $comprobante = Comprobante::factory()->create();

        $venta = Venta::create([
            'cliente_id' => $cliente->id,
            'user_id' => $user->id,
            'comprobante_id' => $comprobante->id,
            'numero_comprobante' => 'B001-0002',
            'numero_venta' => 'V-002',
            'fecha_hora' => now(),
            'impuesto' => 0,
            'total' => 100.00,
            'estado' => 1,
            'metodo_pago' => 'efectivo',
        ]);

        // Adjuntar producto a la venta (relación many-to-many)
        $venta->productos()->attach($producto->id, [
            'cantidad' => 2,
            'precio_venta' => 50.00,
            'descuento' => 0,
        ]);

        // Verificar relaciones
        $this->assertNotNull($venta->cliente);
        $this->assertEquals($cliente->id, $venta->cliente->id);
        
        $this->assertNotNull($venta->user);
        $this->assertEquals($user->id, $venta->user->id);
        
        $this->assertCount(1, $venta->productos);
    }

    /** @test */
    public function puede_usar_factory_para_crear_ventas()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $documento = Documento::factory()->create();
        $persona = Persona::factory()->create(['documento_id' => $documento->id]);
        $cliente = Cliente::factory()->create(['persona_id' => $persona->id]);
        $comprobante = Comprobante::factory()->create();

        // Usar el factory
        $venta = Venta::factory()->create([
            'cliente_id' => $cliente->id,
            'user_id' => $user->id,
            'comprobante_id' => $comprobante->id,
        ]);

        $this->assertNotNull($venta->id);
        
        // Verificar que se creó la venta correctamente
        $this->assertDatabaseHas('ventas', [
            'id' => $venta->id,
            'user_id' => $user->id,
            'cliente_id' => $cliente->id,
        ]);
        
        // Verificar campos generados por factory
        $this->assertNotNull($venta->numero_comprobante);
        $this->assertNotNull($venta->medio_pago);
        $this->assertNotNull($venta->total);
    }
}
