<?php

namespace Tests\Feature\Performance;

use Tests\TestCase;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Comprobante;
use App\Services\VentaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class VentaPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected VentaService $ventaService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ventaService = app(VentaService::class);
    }

    /**
     * Test: Medir queries ejecutadas al procesar venta con 10 productos
     * 
     * Este test establece el BASELINE de performance ANTES de optimizar.
     * 
     * @test
     */
    public function medir_queries_en_procesamiento_venta()
    {
        // Arrange: Crear 10 productos
        $productos = Producto::factory()->count(10)->create([
            'stock' => 100,
            'precio_venta' => 50.00,
        ]);

        $cliente = Cliente::factory()->create();
        $comprobante = Comprobante::factory()->create();

        $data = [
            'cliente_id' => $cliente->id,
            'comprobante_id' => $comprobante->id,
            'arrayidproducto' => $productos->pluck('id')->toArray(),
            'arraycantidad' => array_fill(0, 10, 1),
            'arrayprecioventa' => array_fill(0, 10, 50.00),
            'arraydescuento' => array_fill(0, 10, 0),
            'total' => 500.00, // 10 productos x 50.00 cada uno
            'impuesto' => 0,
            'medio_pago' => 'efectivo',
            'efectivo' => 500.00,
        ];

        // Act: Contar queries
        DB::enableQueryLog();
        $startTime = microtime(true);

        $venta = $this->ventaService->procesarVenta($data);

        $endTime = microtime(true);
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Assert: Registrar métricas
        $totalQueries = count($queries);
        $executionTime = round(($endTime - $startTime) * 1000, 2); // ms

        echo "\n";
        echo "==============================================\n";
        echo "📊 MÉTRICAS DE PERFORMANCE - VENTA\n";
        echo "==============================================\n";
        echo "Total Queries: {$totalQueries}\n";
        echo "Tiempo Ejecución: {$executionTime} ms\n";
        echo "Productos Procesados: 10\n";
        echo "Queries por Producto: " . round($totalQueries / 10, 2) . "\n";
        echo "==============================================\n";
        echo "\n";

        // Guardar baseline para comparación futura
        $baselineData = [
            'fecha' => now()->toISOString(),
            'queries' => $totalQueries,
            'tiempo_ms' => $executionTime,
            'productos' => 10,
            'version' => 'ANTES_OPTIMIZACION',
        ];

        if (!file_exists(storage_path('logs'))) {
            mkdir(storage_path('logs'), 0755, true);
        }

        file_put_contents(
            storage_path('logs/performance_baseline.json'),
            json_encode($baselineData, JSON_PRETTY_PRINT)
        );

        echo "✅ Baseline guardado en: storage/logs/performance_baseline.json\n";
        echo "\n";

        // Verificar que la venta se creó correctamente
        $this->assertNotNull($venta->id);
        $this->assertEquals(10, $venta->productos()->count());
        
        // Mostrar algunas queries para análisis
        echo "🔍 Primeras 5 queries ejecutadas:\n";
        foreach (array_slice($queries, 0, 5) as $index => $query) {
            echo ($index + 1) . ". " . substr($query['query'], 0, 80) . "...\n";
        }
        echo "\n";
    }
}
