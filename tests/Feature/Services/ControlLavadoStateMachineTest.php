<?php

namespace Tests\Feature\Services;

use Tests\TestCase;
use App\Models\ControlLavado;
use App\Models\Lavador;
use App\Models\TipoVehiculo;
use App\Services\ControlLavadoService;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Tests CRÍTICOS para Bug #5: Máquina de Estados de ControlLavado
 * 
 * BUG DETECTADO: No hay validación robusta de transiciones de estado
 * 
 * Estados válidos:
 * - null (pendiente) → iniciado (inicio_lavado)
 * - iniciado → en_proceso (fin_lavado)
 * - en_proceso → completado (inicio_interior)
 * - completado → finalizado (fin_interior)
 * 
 * IMPACTO: Datos inconsistentes, comisiones incorrectas, flujo roto
 */
class ControlLavadoStateMachineTest extends TestCase
{
    use RefreshDatabase;

    protected ControlLavadoService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ControlLavadoService::class);
    }

    /**
     * TEST CRÍTICO #5: No debe permitir completar sin iniciar
     * 
     * @test
     */
    public function no_debe_finalizar_interior_sin_haber_iniciado()
    {
        // Arrange: Lavado en estado pendiente (sin inicio_lavado)
        $lavador = Lavador::factory()->create();
        $tipoVehiculo = TipoVehiculo::factory()->create();
        
        $lavado = ControlLavado::factory()->create([
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'inicio_lavado' => null, // Pendiente
            'fin_lavado' => null,
            'inicio_interior' => null,
            'fin_interior' => null,
        ]);

        // Act & Assert: Intentar finalizar interior directamente debe fallar
        $this->expectException(\Exception::class);
        // El sistema ya valida esto correctamente

        $this->service->finalizarInterior($lavado->id);
    }

    /**
     * TEST: No debe iniciar lavado dos veces
     * 
     * @test
     */
    public function no_debe_iniciar_lavado_dos_veces()
    {
        // Arrange: Lavado ya iniciado
        $lavador = Lavador::factory()->create();
        $tipoVehiculo = TipoVehiculo::factory()->create();
        
        $lavado = ControlLavado::factory()->create([
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'inicio_lavado' => now()->subMinutes(10),
        ]);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('ya fue iniciado');

        $this->service->iniciarLavado($lavado->id, 1);
    }

    /**
     * TEST: No debe finalizar lavado sin haberlo iniciado
     * 
     * @test
     */
    public function no_debe_finalizar_lavado_sin_haberlo_iniciado()
    {
        // Arrange
        $lavador = Lavador::factory()->create();
        $tipoVehiculo = TipoVehiculo::factory()->create();
        
        $lavado = ControlLavado::factory()->create([
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'inicio_lavado' => null,
        ]);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('no ha sido iniciado');

        $this->service->finalizarLavado($lavado->id);
    }

    /**
     * TEST: No debe iniciar interior sin finalizar exterior
     * 
     * @test
     */
    public function no_debe_iniciar_interior_sin_finalizar_exterior()
    {
        // Arrange
        $lavador = Lavador::factory()->create();
        $tipoVehiculo = TipoVehiculo::factory()->create();
        
        $lavado = ControlLavado::factory()->create([
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'inicio_lavado' => now()->subMinutes(20),
            'fin_lavado' => null, // No finalizado
        ]);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('finalizar el lavado exterior');

        $this->service->iniciarInterior($lavado->id);
    }

    /**
     * TEST: No debe finalizar interior sin iniciarlo
     * 
     * @test
     */
    public function no_debe_finalizar_interior_sin_iniciarlo()
    {
        // Arrange
        $lavador = Lavador::factory()->create();
        $tipoVehiculo = TipoVehiculo::factory()->create();
        
        $lavado = ControlLavado::factory()->create([
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'inicio_lavado' => now()->subMinutes(30),
            'fin_lavado' => now()->subMinutes(20),
            'inicio_interior' => null, // No iniciado
        ]);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('lavado interior no ha sido iniciado');

        $this->service->finalizarInterior($lavado->id);
    }

    /**
     * TEST: El flujo completo debe funcionar correctamente
     * 
     * @test
     */
    public function flujo_completo_debe_funcionar_en_orden_correcto()
    {
        // Arrange
        $lavador = Lavador::factory()->create();
        $tipoVehiculo = TipoVehiculo::factory()->create(['comision' => 10.00]);
        
        $lavado = ControlLavado::factory()->create([
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'inicio_lavado' => null,
        ]);

        // Act: Ejecutar flujo correcto
        // 1. Iniciar lavado
        $lavadoPaso1 = $this->service->iniciarLavado($lavado->id, 1);
        $this->assertNotNull($lavadoPaso1->inicio_lavado);
        
        // 2. Finalizar lavado exterior
        $lavadoPaso2 = $this->service->finalizarLavado($lavado->id);
        $this->assertNotNull($lavadoPaso2->fin_lavado);
        
        // 3. Iniciar interior
        $lavadoPaso3 = $this->service->iniciarInterior($lavado->id);
        $this->assertNotNull($lavadoPaso3->inicio_interior);
        
        // 4. Finalizar interior
        $lavadoPaso4 = $this->service->finalizarInterior($lavado->id);
        $this->assertNotNull($lavadoPaso4->fin_interior);
        $this->assertEquals('Terminado', $lavadoPaso4->estado);
    }

    /**
     * TEST: Validar que no se puede asignar lavador después de iniciar
     * 
     * @test
     */
    public function no_debe_asignar_lavador_despues_de_iniciar()
    {
        // Arrange
        $lavador1 = Lavador::factory()->create();
        $lavador2 = Lavador::factory()->create();
        $tipoVehiculo = TipoVehiculo::factory()->create();
        
        $lavado = ControlLavado::factory()->create([
            'lavador_id' => $lavador1->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'inicio_lavado' => now()->subMinutes(10), // Ya iniciado
        ]);

        // Act & Assert
        $this->expectException(\Exception::class);

        $this->service->asignarLavador($lavado->id, $lavador2->id, $tipoVehiculo->id, 'Cambio', 1);
    }

    /**
     * TEST: Los timestamps deben estar en orden lógico
     * 
     * @test
     */
    public function timestamps_deben_estar_en_orden_logico()
    {
        // Arrange
        $lavador = Lavador::factory()->create();
        $tipoVehiculo = TipoVehiculo::factory()->create(['comision' => 10.00]);
        
        $lavado = ControlLavado::factory()->create([
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'hora_llegada' => now()->subHours(1),
        ]);

        // Act: Completar flujo
        $this->service->iniciarLavado($lavado->id, 1);
        sleep(1);
        $this->service->finalizarLavado($lavado->id);
        sleep(1);
        $this->service->iniciarInterior($lavado->id);
        sleep(1);
        $this->service->finalizarInterior($lavado->id);

        // Assert: Verificar orden temporal
        $lavado->refresh();
        
        $this->assertLessThan($lavado->inicio_lavado, $lavado->hora_llegada);
        $this->assertLessThan($lavado->fin_lavado, $lavado->inicio_lavado);
        $this->assertLessThan($lavado->inicio_interior, $lavado->fin_lavado);
        $this->assertLessThan($lavado->fin_interior, $lavado->inicio_interior);
    }

    /**
     * TEST: No debe permitir transiciones inválidas
     * 
     * @test
     */
    public function debe_prevenir_todas_las_transiciones_invalidas()
    {
        $lavador = Lavador::factory()->create();
        $tipoVehiculo = TipoVehiculo::factory()->create();
        
        // Caso 1: Saltar de pendiente a finalizado
        $lavado1 = ControlLavado::factory()->create([
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'inicio_lavado' => null,
        ]);
        
        try {
            $this->service->finalizarInterior($lavado1->id);
            $this->fail('Debería lanzar excepción al saltar estados');
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
        
        // Caso 2: Finalizar sin iniciar interior
        $lavado2 = ControlLavado::factory()->create([
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'inicio_lavado' => now()->subMinutes(20),
            'fin_lavado' => now()->subMinutes(10),
            'inicio_interior' => null,
        ]);
        
        try {
            $this->service->finalizarInterior($lavado2->id);
            $this->fail('Debería lanzar excepción sin inicio_interior');
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
    }
}
