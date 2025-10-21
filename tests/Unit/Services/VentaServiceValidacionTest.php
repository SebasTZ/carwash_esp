<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Comprobante;
use App\Services\VentaService;
use App\Exceptions\StockInsuficienteException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VentaServiceValidacionTest extends TestCase
{
    use RefreshDatabase;

    protected VentaService $ventaService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ventaService = app(VentaService::class);
    }

    /**
     * Test: Validar stock COMPLETO antes de procesar venta
     * 
     * Escenario: Intentar vender 3 productos donde 2 NO tienen stock suficiente
     * Resultado esperado: Debe lanzar excepción con detalle de TODOS los productos problemáticos
     * 
     * @test
     */
    public function debe_validar_stock_completo_antes_de_procesar()
    {
        // Arrange: Crear 3 productos con diferentes niveles de stock
        $producto1 = Producto::factory()->create([
            'nombre' => 'Shampoo',
            'stock' => 5,
            'precio_venta' => 50.00,
        ]);

        $producto2 = Producto::factory()->create([
            'nombre' => 'Cera',
            'stock' => 2, // Insuficiente (se quiere comprar 10)
            'precio_venta' => 80.00,
        ]);

        $producto3 = Producto::factory()->create([
            'nombre' => 'Silicona',
            'stock' => 0, // Sin stock
            'precio_venta' => 30.00,
        ]);

        $cliente = Cliente::factory()->create();
        $comprobante = Comprobante::factory()->create();

        $data = [
            'cliente_id' => $cliente->id,
            'comprobante_id' => $comprobante->id,
            'arrayidproducto' => [$producto1->id, $producto2->id, $producto3->id],
            'arraycantidad' => [3, 10, 5], // producto1: OK, producto2: insuf, producto3: sin stock
            'arrayprecioventa' => [50.00, 80.00, 30.00],
            'arraydescuento' => [0, 0, 0],
            'total' => 1100.00,
            'impuesto' => 0,
            'medio_pago' => 'efectivo',
            'efectivo' => 1100.00,
        ];

        // Act & Assert: Debe lanzar excepción
        try {
            $this->ventaService->procesarVenta($data);
            $this->fail('Debería lanzar StockInsuficienteException');
        } catch (StockInsuficienteException $e) {
            // Verificar que el mensaje contiene información de AMBOS productos problemáticos
            $mensaje = $e->getMessage();
            
            echo "\n";
            echo "==============================================\n";
            echo "✅ EXCEPCIÓN CAPTURADA CORRECTAMENTE\n";
            echo "==============================================\n";
            echo "Mensaje: {$mensaje}\n";
            echo "==============================================\n";
            echo "\n";

            // Verificar que menciona ambos productos con problemas
            $this->assertStringContainsString('Cera', $mensaje, 'Debe mencionar Cera');
            $this->assertStringContainsString('Silicona', $mensaje, 'Debe mencionar Silicona');
            
            // Verificar que NO menciona el producto que SÍ tiene stock
            $this->assertStringNotContainsString('Shampoo', $mensaje, 'No debe mencionar Shampoo (tiene stock)');
            
            // Verificar cantidades
            $this->assertStringContainsString('10', $mensaje, 'Debe mencionar cantidad solicitada de Cera');
            $this->assertStringContainsString('5', $mensaje, 'Debe mencionar cantidad solicitada de Silicona');
        }

        // Verificar que NO se creó ninguna venta (rollback completo)
        $this->assertDatabaseCount('ventas', 0);
        $this->assertDatabaseCount('producto_venta', 0);
        
        // Verificar que el stock NO se modificó
        $this->assertEquals(5, $producto1->fresh()->stock);
        $this->assertEquals(2, $producto2->fresh()->stock);
        $this->assertEquals(0, $producto3->fresh()->stock);
    }

    /**
     * Test: No validar stock en servicios de lavado
     * 
     * @test
     */
    public function no_debe_validar_stock_en_servicios_lavado()
    {
        // Arrange: Producto marcado como servicio de lavado SIN stock
        $servicioLavado = Producto::factory()->create([
            'nombre' => 'Lavado Premium',
            'stock' => 0, // SIN stock (pero es servicio, no importa)
            'precio_venta' => 50.00,
            'es_servicio_lavado' => true,
        ]);

        $cliente = Cliente::factory()->create();
        $comprobante = Comprobante::factory()->create();

        $data = [
            'cliente_id' => $cliente->id,
            'comprobante_id' => $comprobante->id,
            'arrayidproducto' => [$servicioLavado->id],
            'arraycantidad' => [1],
            'arrayprecioventa' => [50.00],
            'arraydescuento' => [0],
            'total' => 50.00,
            'impuesto' => 0,
            'servicio_lavado' => true,
            'horario_lavado' => '14:00', // Requerido para servicios de lavado
            'medio_pago' => 'efectivo',
            'efectivo' => 50.00,
        ];

        // Act: Debe procesar correctamente SIN lanzar excepción
        $venta = $this->ventaService->procesarVenta($data);

        // Assert: Venta creada exitosamente
        $this->assertNotNull($venta->id);
        $this->assertEquals(50.00, $venta->total);
        
        // Stock no debe cambiar (servicios no descontan stock)
        $this->assertEquals(0, $servicioLavado->fresh()->stock);
    }

    /**
     * Test: Validación debe ser atómica (todo o nada)
     * 
     * @test
     */
    public function validacion_debe_fallar_si_un_producto_no_tiene_stock()
    {
        // Arrange: 5 productos, solo 1 sin stock suficiente
        $productosOK = Producto::factory()->count(4)->create(['stock' => 100]);
        $productoSinStock = Producto::factory()->create([
            'nombre' => 'Producto Agotado',
            'stock' => 1,
        ]);

        $cliente = Cliente::factory()->create();
        $comprobante = Comprobante::factory()->create();

        $todosLosIds = $productosOK->pluck('id')->push($productoSinStock->id)->toArray();

        $data = [
            'cliente_id' => $cliente->id,
            'comprobante_id' => $comprobante->id,
            'arrayidproducto' => $todosLosIds,
            'arraycantidad' => [1, 1, 1, 1, 5], // Último necesita 5 pero solo hay 1
            'arrayprecioventa' => array_fill(0, 5, 50.00),
            'arraydescuento' => array_fill(0, 5, 0),
            'total' => 250.00,
            'impuesto' => 0,
            'medio_pago' => 'efectivo',
            'efectivo' => 250.00,
        ];

        // Act & Assert
        $this->expectException(StockInsuficienteException::class);
        $this->ventaService->procesarVenta($data);

        // Verificar que NINGÚN producto se descontó
        foreach ($productosOK as $producto) {
            $this->assertEquals(100, $producto->fresh()->stock, "Stock de {$producto->nombre} no debe cambiar");
        }
        $this->assertEquals(1, $productoSinStock->fresh()->stock);
    }
}
