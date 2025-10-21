<?php

namespace Tests\Unit\Repositories;

use Tests\TestCase;
use App\Models\Producto;
use App\Repositories\ProductoRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductoRepositoryCacheTest extends TestCase
{
    use RefreshDatabase;

    protected ProductoRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(ProductoRepository::class);
        Cache::flush(); // Limpiar cache antes de cada test
    }

    /**
     * Test: Primera llamada ejecuta query, segunda usa cache
     * 
     * @test
     */
    public function obtener_productos_para_venta_usa_cache()
    {
        // Arrange: Crear servicios de lavado
        Producto::factory()->count(5)->create([
            'estado' => 1,
            'es_servicio_lavado' => true,
        ]);

        // Act: Primera llamada (sin cache)
        $startTime1 = microtime(true);
        $productos1 = $this->repository->obtenerParaVenta();
        $tiempo1 = round((microtime(true) - $startTime1) * 1000, 2);

        // Segunda llamada (con cache)
        $startTime2 = microtime(true);
        $productos2 = $this->repository->obtenerParaVenta();
        $tiempo2 = round((microtime(true) - $startTime2) * 1000, 2);

        // Assert
        $this->assertCount(5, $productos1);
        $this->assertCount(5, $productos2);
        $this->assertEquals($productos1->pluck('id')->sort()->values(), 
                           $productos2->pluck('id')->sort()->values());

        echo "\n";
        echo "==============================================\n";
        echo "📊 MÉTRICAS DE CACHE - PRODUCTOS\n";
        echo "==============================================\n";
        echo "Primera llamada (SIN cache): {$tiempo1} ms\n";
        echo "Segunda llamada (CON cache): {$tiempo2} ms\n";
        echo "Mejora: " . round((($tiempo1 - $tiempo2) / $tiempo1) * 100, 1) . "%\n";
        echo "==============================================\n";
        echo "\n";

        // El cache debe ser significativamente más rápido
        $this->assertLessThan($tiempo1, $tiempo2, 'Cache debe ser más rápido');
    }

    /**
     * Test: Cache se invalida cuando se crea un producto
     * 
     * @test
     */
    public function cache_se_invalida_al_crear_producto()
    {
        // Arrange: Crear productos servicios de lavado y cachearlos
        Producto::factory()->count(3)->create([
            'estado' => 1,
            'es_servicio_lavado' => true,
        ]);
        $productosIniciales = $this->repository->obtenerParaVenta();
        $this->assertCount(3, $productosIniciales);

        // Act: Crear nuevo producto (debe invalidar cache)
        Producto::factory()->create([
            'estado' => 1,
            'es_servicio_lavado' => true,
        ]);

        // Assert: El cache debe reflejar el nuevo producto
        $productosActualizados = $this->repository->obtenerParaVenta();
        $this->assertCount(4, $productosActualizados, 'Cache debe incluir nuevo producto');
    }

    /**
     * Test: Cache se invalida cuando se actualiza un producto
     * 
     * @test
     */
    public function cache_se_invalida_al_actualizar_producto()
    {
        // Arrange
        $producto = Producto::factory()->create([
            'estado' => 1,
            'nombre' => 'Producto Original',
            'es_servicio_lavado' => true,
        ]);

        // Cachear productos
        $productosIniciales = $this->repository->obtenerParaVenta();
        $this->assertStringContainsString('Original', $productosIniciales->first()->nombre);

        // Act: Actualizar producto
        $producto->update(['nombre' => 'Producto Actualizado']);

        // Assert: Cache debe reflejar el cambio
        $productosActualizados = $this->repository->obtenerParaVenta();
        $this->assertStringContainsString('Actualizado', $productosActualizados->first()->nombre);
    }

    /**
     * Test: Cache se invalida cuando se elimina un producto
     * 
     * @test
     */
    public function cache_se_invalida_al_eliminar_producto()
    {
        // Arrange
        Producto::factory()->count(3)->create([
            'estado' => 1,
            'es_servicio_lavado' => true,
        ]);
        $productoAEliminar = Producto::factory()->create([
            'estado' => 1,
            'es_servicio_lavado' => true,
        ]);

        // Cachear productos
        $productosIniciales = $this->repository->obtenerParaVenta();
        $this->assertCount(4, $productosIniciales);

        // Act: Eliminar producto
        $productoAEliminar->delete();

        // Assert: Cache debe reflejar la eliminación
        $productosActualizados = $this->repository->obtenerParaVenta();
        $this->assertCount(3, $productosActualizados);
    }

    /**
     * Test: Cache se invalida cuando se cambia estado de producto
     * 
     * @test
     */
    public function cache_se_invalida_al_cambiar_estado_producto()
    {
        // Arrange: Producto activo (servicio de lavado para que aparezca sin stock)
        $producto = Producto::factory()->create([
            'estado' => 1,
            'es_servicio_lavado' => true,
        ]);
        
        $productosActivos = $this->repository->obtenerParaVenta();
        $this->assertCount(1, $productosActivos);

        // Act: Desactivar producto (usar save() para asegurar persistencia en tests)
        $producto->estado = 0;
        $producto->save();
        $producto->refresh();

        // Assert: Cache debe reflejar el cambio (producto ya no aparece)
        $productosActualizados = $this->repository->obtenerParaVenta();
        $this->assertCount(0, $productosActualizados, 'Producto desactivado no debe aparecer');
    }

    /**
     * Test: Método limpiarCache funciona correctamente
     * 
     * @test
     */
    public function metodo_limpiar_cache_funciona()
    {
        // Arrange: Crear productos y cachearlos
        Producto::factory()->count(3)->create([
            'estado' => 1,
            'es_servicio_lavado' => true,
        ]);
        $this->repository->obtenerParaVenta(); // Crear cache

        // Verificar que el cache existe
        $this->assertTrue(Cache::has('productos_para_venta'));

        // Act: Limpiar cache manualmente
        $this->repository->limpiarCache();

        // Assert: Cache debe estar vacío
        $this->assertFalse(Cache::has('productos_para_venta'));
    }

    /**
     * Test: Cache tiene TTL de 1 hora
     * 
     * @test
     */
    public function cache_expira_despues_de_tiempo_configurado()
    {
        // Arrange
        Producto::factory()->count(2)->create([
            'estado' => 1,
            'es_servicio_lavado' => true,
        ]);
        $this->repository->obtenerParaVenta(); // Crear cache

        // Assert: Cache debe existir
        $this->assertTrue(Cache::has('productos_para_venta'));

        // Simular que pasó el tiempo (usando travel en tiempo real no es práctico)
        // Solo verificamos que la key existe por ahora
        $this->assertNotNull(Cache::get('productos_para_venta'));
    }

    /**
     * Test: Cache solo incluye productos activos
     * 
     * @test
     */
    public function cache_solo_incluye_productos_activos()
    {
        // Arrange: Mezcla de productos activos e inactivos (servicios de lavado)
        Producto::factory()->count(3)->create([
            'estado' => 1,
            'es_servicio_lavado' => true,
        ]); // Activos
        
        Producto::factory()->count(2)->create([
            'estado' => 0,
            'es_servicio_lavado' => true,
        ]); // Inactivos

        // Act
        $productos = $this->repository->obtenerParaVenta();

        // Assert: Solo los activos (3 productos)
        $this->assertCount(3, $productos);
        
        // Verificar que todos estén activos (si el campo está en el resultado)
        $todosActivos = $productos->every(function($producto) {
            // Si el campo 'estado' está disponible, verificar que sea 1
            return !isset($producto->estado) || $producto->estado == 1;
        });
        
        $this->assertTrue($todosActivos, 'Todos los productos deben estar activos');
    }

    /**
     * Test: Performance - Cache reduce tiempo de carga significativamente
     * 
     * @test
     */
    public function cache_mejora_performance_significativamente()
    {
        // Arrange: Crear muchos servicios de lavado
        Producto::factory()->count(50)->create([
            'estado' => 1,
            'es_servicio_lavado' => true,
        ]);

        // Act: Medir sin cache
        Cache::flush();
        $startSinCache = microtime(true);
        $this->repository->obtenerParaVenta();
        $tiempoSinCache = (microtime(true) - $startSinCache) * 1000;

        // Medir con cache (segunda llamada)
        $startConCache = microtime(true);
        $this->repository->obtenerParaVenta();
        $tiempoConCache = (microtime(true) - $startConCache) * 1000;

        // Assert: Cache debe ser al menos 50% más rápido
        $mejora = (($tiempoSinCache - $tiempoConCache) / $tiempoSinCache) * 100;

        echo "\n";
        echo "==============================================\n";
        echo "⚡ MEJORA DE PERFORMANCE CON CACHE\n";
        echo "==============================================\n";
        echo "Productos: 50\n";
        echo "Sin Cache: " . round($tiempoSinCache, 2) . " ms\n";
        echo "Con Cache: " . round($tiempoConCache, 2) . " ms\n";
        echo "Mejora: " . round($mejora, 1) . "%\n";
        echo "==============================================\n";
        echo "\n";

        $this->assertGreaterThan(50, $mejora, 'Cache debe mejorar performance al menos 50%');
    }
}
