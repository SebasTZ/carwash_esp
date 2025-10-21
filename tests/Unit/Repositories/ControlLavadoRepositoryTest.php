<?php

namespace Tests\Unit\Repositories;

use Tests\TestCase;
use App\Repositories\ControlLavadoRepository;
use App\Models\ControlLavado;
use App\Models\Lavador;
use App\Models\TipoVehiculo;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Cache;

class ControlLavadoRepositoryTest extends TestCase
{
    use DatabaseMigrations;

    protected ControlLavadoRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->repository = app(ControlLavadoRepository::class);
    }

    /** @test */
    public function puede_encontrar_lavado_por_id()
    {
        // Arrange
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        $venta = Venta::factory()->create();
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
        ]);

        // Act
        $resultado = $this->repository->find($controlLavado->id);

        // Assert
        $this->assertInstanceOf(ControlLavado::class, $resultado);
        $this->assertEquals($controlLavado->id, $resultado->id);
    }

    /** @test */
    public function puede_encontrar_lavado_con_relaciones()
    {
        // Arrange
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        $venta = Venta::factory()->create();
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
        ]);

        // Act
        $resultado = $this->repository->findOrFail($controlLavado->id, ['venta', 'lavador']);

        // Assert
        $this->assertInstanceOf(ControlLavado::class, $resultado);
        $this->assertTrue($resultado->relationLoaded('venta'));
        $this->assertTrue($resultado->relationLoaded('lavador'));
    }

    /** @test */
    public function puede_actualizar_lavado()
    {
        // Arrange
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        $nuevoLavador = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        $venta = Venta::factory()->create();
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
        ]);

        // Act
        $resultado = $this->repository->update($controlLavado->id, [
            'lavador_id' => $nuevoLavador->id,
        ]);

        // Assert
        $this->assertEquals($nuevoLavador->id, $resultado->lavador_id);
        $this->assertDatabaseHas('control_lavados', [
            'id' => $controlLavado->id,
            'lavador_id' => $nuevoLavador->id,
        ]);
    }

    /** @test */
    public function puede_eliminar_lavado()
    {
        // Arrange
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        $venta = Venta::factory()->create();
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
        ]);

        // Act
        $resultado = $this->repository->delete($controlLavado->id);

        // Assert
        $this->assertTrue($resultado);
        $this->assertSoftDeleted('control_lavados', [
            'id' => $controlLavado->id,
        ]);
    }

    /** @test */
    public function puede_obtener_lavados_con_filtros()
    {
        // Arrange
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        $otroLavador = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        $venta = Venta::factory()->create();
        
        ControlLavado::factory()->count(3)->create([
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
        ]);
        
        ControlLavado::factory()->count(2)->create([
            'lavador_id' => $otroLavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
        ]);

        // Act
        $resultado = $this->repository->getWithFilters([
            'lavador_id' => $lavador->id,
        ], 10);

        // Assert
        $this->assertCount(3, $resultado);
        foreach ($resultado as $lavado) {
            $this->assertEquals($lavador->id, $lavado->lavador_id);
        }
    }

    /** @test */
    public function puede_obtener_lavados_del_dia()
    {
        // Arrange
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        
        // Lavados de hoy
        ControlLavado::factory()->count(3)->create([
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'created_at' => now(),
        ]);
        
        // Lavados de ayer
        ControlLavado::factory()->count(2)->create([
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'created_at' => now()->subDay(),
        ]);

        // Act
        $resultado = $this->repository->getToday();

        // Assert - Al menos deben estar los 3 que creamos
        $this->assertGreaterThanOrEqual(3, $resultado->count());
    }

    /** @test */
    public function puede_obtener_lavados_de_la_semana()
    {
        // Arrange
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        
        // Lavados de esta semana
        ControlLavado::factory()->count(5)->create([
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'created_at' => now(),
        ]);
        
        // Lavados de hace 2 semanas
        ControlLavado::factory()->count(2)->create([
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'created_at' => now()->subWeeks(2),
        ]);

        // Act
        $resultado = $this->repository->getThisWeek();

        // Assert - Al menos deben estar los 5 que creamos
        $this->assertGreaterThanOrEqual(5, $resultado->count());
    }

    /** @test */
    public function puede_obtener_lavados_del_mes()
    {
        // Arrange
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        
        // Lavados de este mes
        ControlLavado::factory()->count(7)->create([
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'created_at' => now(),
        ]);
        
        // Lavados del mes pasado
        ControlLavado::factory()->count(3)->create([
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'created_at' => now()->subMonth(),
        ]);

        // Act
        $resultado = $this->repository->getThisMonth();

        // Assert - Al menos deben estar los 7 que creamos
        $this->assertGreaterThanOrEqual(7, $resultado->count());
    }

    /** @test */
    public function puede_obtener_lavados_por_rango_de_fechas()
    {
        // Arrange
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        
        $fechaInicio = now()->subDays(5);
        $fechaFin = now();
        
        // Lavados dentro del rango
        ControlLavado::factory()->count(4)->create([
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'created_at' => now()->subDays(2),
        ]);
        
        // Lavados fuera del rango
        ControlLavado::factory()->count(2)->create([
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'created_at' => now()->subDays(10),
        ]);

        // Act
        $resultado = $this->repository->getByDateRange(
            $fechaInicio->format('Y-m-d'),
            $fechaFin->format('Y-m-d')
        );

        // Assert - Al menos deben estar los 4 que creamos en el rango
        $this->assertGreaterThanOrEqual(4, $resultado->count());
    }

    /** @test */
    public function usa_cache_al_buscar_por_id()
    {
        // Arrange
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        $venta = Venta::factory()->create();
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
        ]);

        // Act - Primera llamada, debe cachear
        $this->repository->findOrFail($controlLavado->id);
        
        // Segunda llamada, debe usar caché
        $resultado = $this->repository->findOrFail($controlLavado->id);

        // Assert
        $this->assertInstanceOf(ControlLavado::class, $resultado);
        $this->assertTrue(Cache::has("control_lavado:{$controlLavado->id}"));
    }

    /** @test */
    public function invalida_cache_al_actualizar()
    {
        // Arrange
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        $nuevoLavador = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        $venta = Venta::factory()->create();
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
        ]);

        // Cachear el lavado
        $this->repository->findOrFail($controlLavado->id);
        $this->assertTrue(Cache::has("control_lavado:{$controlLavado->id}"));

        // Act - Actualizar debe invalidar caché
        $this->repository->update($controlLavado->id, [
            'lavador_id' => $nuevoLavador->id,
        ]);

        // Assert
        $this->assertFalse(Cache::has("control_lavado:{$controlLavado->id}"));
    }

    /** @test */
    public function invalida_cache_al_eliminar()
    {
        // Arrange
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        $venta = Venta::factory()->create();
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
        ]);

        // Cachear el lavado
        $this->repository->findOrFail($controlLavado->id);
        $this->assertTrue(Cache::has("control_lavado:{$controlLavado->id}"));

        // Act - Eliminar debe invalidar caché
        $this->repository->delete($controlLavado->id);

        // Assert
        $this->assertFalse(Cache::has("control_lavado:{$controlLavado->id}"));
    }
}
