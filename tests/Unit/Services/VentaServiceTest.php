<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\VentaService;
use App\Services\StockService;
use App\Services\FidelizacionService;
use App\Services\TarjetaRegaloService;
use App\Services\ComprobanteService;
use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Comprobante;
use App\Models\User;
use App\Exceptions\VentaException;
use App\Exceptions\StockInsuficienteException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;

class VentaServiceTest extends TestCase
{
    use DatabaseMigrations;

    protected VentaService $ventaService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->ventaService = app(VentaService::class);
    }

    /** @test */
    public function puede_procesar_venta_con_efectivo()
    {
        // Arrange
        $cliente = Cliente::factory()->create(['lavados_acumulados' => 0]);
        $producto = Producto::factory()->create([
            'stock' => 10,
            'precio_venta' => 50.00,
            'es_servicio_lavado' => false,
        ]);
        $comprobante = Comprobante::factory()->create();
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = [
            'cliente_id' => $cliente->id,
            'comprobante_id' => $comprobante->id,
            'total' => 100.00,
            'impuesto' => 18.00,
            'medio_pago' => 'efectivo',
            'servicio_lavado' => false,
            'arrayidproducto' => [$producto->id],
            'arraycantidad' => [2],
            'arrayprecioventa' => [50.00],
            'arraydescuento' => [0],
        ];

        // Act
        $venta = $this->ventaService->procesarVenta($data);

        // Assert
        $this->assertInstanceOf(Venta::class, $venta);
        $this->assertEquals(100.00, $venta->total);
        $this->assertEquals('efectivo', $venta->medio_pago);
        $this->assertEquals($cliente->id, $venta->cliente_id);
        
        // Verificar que el stock se descontó
        $producto->refresh();
        $this->assertEquals(8, $producto->stock); // 10 - 2 = 8
        
        // Verificar que se acumularon puntos (10% = 10 puntos)
        $this->assertDatabaseHas('fidelizacion', [
            'cliente_id' => $cliente->id,
            'puntos' => 10.00,
        ]);
    }

    /** @test */
    public function lanza_excepcion_cuando_stock_insuficiente()
    {
        // Arrange
        $cliente = Cliente::factory()->create();
        $producto = Producto::factory()->create([
            'stock' => 5,
            'es_servicio_lavado' => false,
        ]);
        $comprobante = Comprobante::factory()->create();
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = [
            'cliente_id' => $cliente->id,
            'comprobante_id' => $comprobante->id,
            'total' => 100.00,
            'impuesto' => 18.00,
            'medio_pago' => 'efectivo',
            'arrayidproducto' => [$producto->id],
            'arraycantidad' => [10], // Más de lo disponible
            'arrayprecioventa' => [10.00],
            'arraydescuento' => [0],
        ];

        // Act & Assert
        $this->expectException(StockInsuficienteException::class);
        $this->ventaService->procesarVenta($data);
        
        // Verificar que el stock NO se modificó
        $producto->refresh();
        $this->assertEquals(5, $producto->stock);
    }

    /** @test */
    public function puede_procesar_venta_con_servicio_lavado()
    {
        // Arrange
        $cliente = Cliente::factory()->create(['lavados_acumulados' => 5]);
        $servicio = Producto::factory()->create([
            'es_servicio_lavado' => true,
            'precio_venta' => 30.00,
        ]);
        $comprobante = Comprobante::factory()->create();
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = [
            'cliente_id' => $cliente->id,
            'comprobante_id' => $comprobante->id,
            'total' => 30.00,
            'impuesto' => 5.40,
            'medio_pago' => 'efectivo',
            'servicio_lavado' => true,
            'horario_lavado' => '14:00',
            'arrayidproducto' => [$servicio->id],
            'arraycantidad' => [1],
            'arrayprecioventa' => [30.00],
            'arraydescuento' => [0],
        ];

        // Act
        $venta = $this->ventaService->procesarVenta($data);

        // Assert
        $this->assertTrue($venta->servicio_lavado);
        $this->assertNotNull($venta->horario_lavado);
        
        // Verificar que se creó el control de lavado
        $this->assertDatabaseHas('control_lavados', [
            'venta_id' => $venta->id,
            'cliente_id' => $cliente->id,
            'estado' => 'En espera',
        ]);
        
        // Verificar que se acumuló 1 lavado
        $cliente->refresh();
        $this->assertEquals(6, $cliente->lavados_acumulados);
    }

    /** @test */
    public function puede_procesar_lavado_gratis()
    {
        // Arrange
        $cliente = Cliente::factory()->create(['lavados_acumulados' => 10]);
        $servicio = Producto::factory()->create([
            'es_servicio_lavado' => true,
            'precio_venta' => 30.00,
        ]);
        $comprobante = Comprobante::factory()->create();
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = [
            'cliente_id' => $cliente->id,
            'comprobante_id' => $comprobante->id,
            'total' => 0.00,
            'impuesto' => 0.00,
            'medio_pago' => 'lavado_gratis',
            'servicio_lavado' => true,
            'horario_lavado' => '14:00',
            'arrayidproducto' => [$servicio->id],
            'arraycantidad' => [1],
            'arrayprecioventa' => [0.00],
            'arraydescuento' => [0],
        ];

        // Act
        $venta = $this->ventaService->procesarVenta($data);

        // Assert
        $this->assertTrue($venta->lavado_gratis);
        $this->assertEquals('lavado_gratis', $venta->medio_pago);
        
        // Verificar que los lavados se resetearon
        $cliente->refresh();
        $this->assertEquals(0, $cliente->lavados_acumulados);
    }

    /** @test */
    public function lanza_excepcion_cuando_lavado_gratis_sin_puntos()
    {
        // Arrange
        $cliente = Cliente::factory()->create(['lavados_acumulados' => 5]); // Menos de 10
        $servicio = Producto::factory()->create(['es_servicio_lavado' => true]);
        $comprobante = Comprobante::factory()->create();
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = [
            'cliente_id' => $cliente->id,
            'comprobante_id' => $comprobante->id,
            'total' => 0.00,
            'impuesto' => 0.00,
            'medio_pago' => 'lavado_gratis',
            'servicio_lavado' => true,
            'horario_lavado' => '14:00',
            'arrayidproducto' => [$servicio->id],
            'arraycantidad' => [1],
            'arrayprecioventa' => [0.00],
            'arraydescuento' => [0],
        ];

        // Act & Assert
        $this->expectException(VentaException::class);
        $this->expectExceptionMessage('suficientes lavados acumulados');
        
        $this->ventaService->procesarVenta($data);
    }

    /** @test */
    public function rollback_en_caso_de_error()
    {
        // Arrange
        $cliente = Cliente::factory()->create();
        $producto = Producto::factory()->create(['stock' => 10]);
        $comprobante = Comprobante::factory()->create();
        $user = User::factory()->create();
        $this->actingAs($user);

        // Simular error forzando un servicio de lavado sin horario
        $data = [
            'cliente_id' => $cliente->id,
            'comprobante_id' => $comprobante->id,
            'total' => 50.00,
            'impuesto' => 9.00,
            'medio_pago' => 'efectivo',
            'servicio_lavado' => true,
            'horario_lavado' => null, // Esto causará error
            'arrayidproducto' => [$producto->id],
            'arraycantidad' => [2],
            'arrayprecioventa' => [25.00],
            'arraydescuento' => [0],
        ];

        // Act & Assert
        try {
            $this->ventaService->procesarVenta($data);
            $this->fail('Debería haber lanzado una excepción');
        } catch (VentaException $e) {
            // Verificar que NO se creó la venta (rollback)
            $this->assertEquals(0, Venta::count());
            
            // Verificar que el stock NO se modificó
            $producto->refresh();
            $this->assertEquals(10, $producto->stock);
            
            // Verificar que NO se acumularon puntos
            $this->assertEquals(0, DB::table('fidelizacion')->where('cliente_id', $cliente->id)->count());
        }
    }

    /** @test */
    public function no_descuenta_stock_de_servicios_de_lavado()
    {
        // Arrange
        $cliente = Cliente::factory()->create();
        $servicio = Producto::factory()->create([
            'es_servicio_lavado' => true,
            'stock' => 999, // Stock ficticio para servicios
        ]);
        $comprobante = Comprobante::factory()->create();
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = [
            'cliente_id' => $cliente->id,
            'comprobante_id' => $comprobante->id,
            'total' => 30.00,
            'impuesto' => 5.40,
            'medio_pago' => 'efectivo',
            'servicio_lavado' => true,
            'horario_lavado' => '14:00',
            'arrayidproducto' => [$servicio->id],
            'arraycantidad' => [1],
            'arrayprecioventa' => [30.00],
            'arraydescuento' => [0],
        ];

        // Act
        $this->ventaService->procesarVenta($data);

        // Assert - El stock NO debe cambiar en servicios
        $servicio->refresh();
        $this->assertEquals(999, $servicio->stock);
    }
}
