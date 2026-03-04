<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Comprobante;
use App\Models\Venta;
use App\Services\VentaService;
use App\Exceptions\StockInsuficienteException;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class VentaFlowIntegrationTest extends TestCase
{
    use DatabaseMigrations;

    protected VentaService $ventaService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ventaService = app(VentaService::class);
        $this->actingAs(User::factory()->create());
    }

    /** @test */
    public function flujo_completo_de_venta_con_producto_fisico()
    {
        $cliente = Cliente::factory()->create(['lavados_acumulados' => 0]);
        $producto = Producto::factory()->create([
            'stock' => 20,
            'precio_venta' => 50.00,
            'es_servicio_lavado' => false,
        ]);
        $comprobante = Comprobante::factory()->create();

        $venta = $this->ventaService->procesarVenta([
            'cliente_id' => $cliente->id,
            'comprobante_id' => $comprobante->id,
            'total' => 100.00,
            'impuesto' => 0,
            'medio_pago' => 'efectivo',
            'servicio_lavado' => false,
            'arrayidproducto' => [$producto->id],
            'arraycantidad' => [2],
            'arrayprecioventa' => [50.00],
            'arraydescuento' => [0],
        ]);

        // La venta fue creada correctamente
        $this->assertDatabaseHas('ventas', [
            'id' => $venta->id,
            'total' => 100.00,
            'medio_pago' => 'efectivo',
            'estado' => 1,
        ]);

        // El stock fue descontado correctamente (20 - 2 = 18)
        $this->assertEquals(18, $producto->fresh()->stock);

        // El producto está asociado a la venta
        $this->assertDatabaseHas('producto_venta', [
            'venta_id' => $venta->id,
            'producto_id' => $producto->id,
            'cantidad' => 2,
        ]);
    }

    /** @test */
    public function flujo_completo_de_venta_con_servicio_lavado_acumula_lavado()
    {
        $cliente = Cliente::factory()->create(['lavados_acumulados' => 3]);
        $servicio = Producto::factory()->servicioLavado()->create(['precio_venta' => 30.00]);
        $comprobante = Comprobante::factory()->create();

        $this->ventaService->procesarVenta([
            'cliente_id' => $cliente->id,
            'comprobante_id' => $comprobante->id,
            'total' => 30.00,
            'impuesto' => 0,
            'medio_pago' => 'efectivo',
            'servicio_lavado' => true,
            'arrayidproducto' => [$servicio->id],
            'arraycantidad' => [1],
            'arrayprecioventa' => [30.00],
            'arraydescuento' => [0],
        ]);

        // El lavado acumulado debe incrementar en 1 (3 + 1 = 4)
        $this->assertEquals(4, $cliente->fresh()->lavados_acumulados);
    }

    /** @test */
    public function flujo_con_stock_insuficiente_lanza_excepcion_y_no_crea_venta()
    {
        $cliente = Cliente::factory()->create();
        $producto = Producto::factory()->create([
            'stock' => 2,
            'es_servicio_lavado' => false,
        ]);
        $comprobante = Comprobante::factory()->create();
        $ventasAntes = Venta::count();

        try {
            $this->ventaService->procesarVenta([
                'cliente_id' => $cliente->id,
                'comprobante_id' => $comprobante->id,
                'total' => 150.00,
                'impuesto' => 0,
                'medio_pago' => 'efectivo',
                'servicio_lavado' => false,
                'arrayidproducto' => [$producto->id],
                'arraycantidad' => [10],  // pedir 10 cuando hay solo 2
                'arrayprecioventa' => [15.00],
                'arraydescuento' => [0],
            ]);
            $this->fail('Se esperaba StockInsuficienteException pero no fue lanzada');
        } catch (StockInsuficienteException $e) {
            // Excepción esperada
        }

        // No se debe haber creado ninguna venta
        $this->assertEquals($ventasAntes, Venta::count());

        // El stock no debe haber cambiado
        $this->assertEquals(2, $producto->fresh()->stock);
    }

    /** @test */
    public function anular_venta_revierte_stock_y_fidelizacion()
    {
        $cliente = Cliente::factory()->create(['lavados_acumulados' => 5]);
        $servicio = Producto::factory()->servicioLavado()->create();
        $comprobante = Comprobante::factory()->create();

        $venta = $this->ventaService->procesarVenta([
            'cliente_id' => $cliente->id,
            'comprobante_id' => $comprobante->id,
            'total' => 30.00,
            'impuesto' => 0,
            'medio_pago' => 'efectivo',
            'servicio_lavado' => true,
            'arrayidproducto' => [$servicio->id],
            'arraycantidad' => [1],
            'arrayprecioventa' => [30.00],
            'arraydescuento' => [0],
        ]);

        // Fidelización fue acumulada (5 + 1 = 6)
        $this->assertEquals(6, $cliente->fresh()->lavados_acumulados);

        // Anular la venta
        $this->ventaService->anularVenta($venta, 'Test de anulación');

        // La venta debe estar marcada como anulada
        $this->assertEquals(0, $venta->fresh()->estado);

        // La fidelización debe haberse revertido (6 - 1 = 5)
        $this->assertEquals(5, $cliente->fresh()->lavados_acumulados);
    }
}
