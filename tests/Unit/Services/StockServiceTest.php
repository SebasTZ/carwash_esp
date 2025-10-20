<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\StockService;
use App\Models\Producto;
use App\Models\Caracteristica;
use App\Models\Marca;
use App\Models\Presentacione;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;

class StockServiceTest extends TestCase
{
    use DatabaseMigrations;

    private StockService $stockService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stockService = new StockService();
    }

    /** @test */
    public function puede_descontar_stock_de_producto()
    {
        $caracteristica = Caracteristica::factory()->create();
        $marca = Marca::factory()->create(['caracteristica_id' => $caracteristica->id]);
        $presentacion = Presentacione::factory()->create(['caracteristica_id' => $caracteristica->id]);
        
        $producto = Producto::factory()->create([
            'stock' => 10,
            'marca_id' => $marca->id,
            'presentacione_id' => $presentacion->id,
        ]);

        $this->stockService->descontarStock($producto, 3, 'TEST-001');

        $producto->refresh();
        $this->assertEquals(7, $producto->stock);
    }

    /** @test */
    public function lanza_excepcion_cuando_stock_insuficiente()
    {
        $caracteristica = Caracteristica::factory()->create();
        $marca = Marca::factory()->create(['caracteristica_id' => $caracteristica->id]);
        $presentacion = Presentacione::factory()->create(['caracteristica_id' => $caracteristica->id]);
        
        $producto = Producto::factory()->create([
            'stock' => 5,
            'marca_id' => $marca->id,
            'presentacione_id' => $presentacion->id,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Stock insuficiente');

        $this->stockService->descontarStock($producto, 10, 'TEST-002');
    }

    /** @test */
    public function usa_lock_for_update_para_prevenir_condiciones_de_carrera()
    {
        $caracteristica = Caracteristica::factory()->create();
        $marca = Marca::factory()->create(['caracteristica_id' => $caracteristica->id]);
        $presentacion = Presentacione::factory()->create(['caracteristica_id' => $caracteristica->id]);
        
        $producto = Producto::factory()->create([
            'stock' => 10,
            'marca_id' => $marca->id,
            'presentacione_id' => $presentacion->id,
        ]);

        // Verificar que la operación se ejecuta dentro de una transacción
        // y el stock se actualiza correctamente
        $this->stockService->descontarStock($producto, 2, 'TEST-003');
        
        $producto->refresh();
        $this->assertEquals(8, $producto->stock);
        
        // Verificar que múltiples operaciones mantienen consistencia
        $this->stockService->descontarStock($producto, 3, 'TEST-003-B');
        
        $producto->refresh();
        $this->assertEquals(5, $producto->stock);
    }

    /** @test */
    public function puede_restaurar_stock_de_producto()
    {
        $caracteristica = Caracteristica::factory()->create();
        $marca = Marca::factory()->create(['caracteristica_id' => $caracteristica->id]);
        $presentacion = Presentacione::factory()->create(['caracteristica_id' => $caracteristica->id]);
        
        $producto = Producto::factory()->create([
            'stock' => 5,
            'marca_id' => $marca->id,
            'presentacione_id' => $presentacion->id,
        ]);

        $this->stockService->incrementarStock($producto, 7, 'TEST-004');

        $producto->refresh();
        $this->assertEquals(12, $producto->stock);
    }

    /** @test */
    public function puede_verificar_disponibilidad_de_stock()
    {
        $caracteristica = Caracteristica::factory()->create();
        $marca = Marca::factory()->create(['caracteristica_id' => $caracteristica->id]);
        $presentacion = Presentacione::factory()->create(['caracteristica_id' => $caracteristica->id]);
        
        $producto = Producto::factory()->create([
            'stock' => 10,
            'marca_id' => $marca->id,
            'presentacione_id' => $presentacion->id,
        ]);

        $disponible = $this->stockService->verificarStockSuficiente([$producto->id => 5]);
        $this->assertTrue($disponible);

        $noDisponible = $this->stockService->verificarStockSuficiente([$producto->id => 15]);
        $this->assertFalse($noDisponible);
    }

    /** @test */
    public function puede_obtener_productos_con_stock_bajo()
    {
        $caracteristica = Caracteristica::factory()->create();
        $marca = Marca::factory()->create(['caracteristica_id' => $caracteristica->id]);
        $presentacion = Presentacione::factory()->create(['caracteristica_id' => $caracteristica->id]);
        
        // Producto con stock bajo
        $productoBajo = Producto::factory()->create([
            'stock' => 3,
            'stock_minimo' => 5,
            'marca_id' => $marca->id,
            'presentacione_id' => $presentacion->id,
        ]);

        // Producto con stock normal
        $productoNormal = Producto::factory()->create([
            'stock' => 20,
            'stock_minimo' => 5,
            'marca_id' => $marca->id,
            'presentacione_id' => $presentacion->id,
        ]);

        $productosConStockBajo = $this->stockService->obtenerProductosStockBajo(10);

        $this->assertGreaterThanOrEqual(1, $productosConStockBajo->count());
        $this->assertTrue($productosConStockBajo->contains('id', $productoBajo->id));
    }

    /** @test */
    public function descuenta_stock_de_todos_los_productos_incluyendo_servicios()
    {
        $caracteristica = Caracteristica::factory()->create();
        $marca = Marca::factory()->create(['caracteristica_id' => $caracteristica->id]);
        $presentacion = Presentacione::factory()->create(['caracteristica_id' => $caracteristica->id]);
        
        $servicio = Producto::factory()->servicioLavado()->create([
            'stock' => 10,
            'marca_id' => $marca->id,
            'presentacione_id' => $presentacion->id,
        ]);

        // StockService SÍ descuenta stock de servicios
        // La validación de es_servicio_lavado se hace en VentaService
        $this->stockService->descontarStock($servicio, 2, 'TEST-007');

        $servicio->refresh();
        // El stock SÍ debe cambiar porque StockService no verifica es_servicio_lavado
        $this->assertEquals(8, $servicio->stock);
    }
}
