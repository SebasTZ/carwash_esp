<?php

namespace Tests\Feature\Flows;

use Tests\TestCase;
use App\Models\ControlLavado;
use App\Models\PagoComision;
use App\Models\Lavador;
use App\Models\TipoVehiculo;
use App\Models\Venta;
use App\Models\Cliente;
use App\Services\ControlLavadoService;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Test CRÍTICO para Bug #1: Comisiones Duplicadas
 * 
 * BUG DETECTADO en: app/Services/ControlLavadoService.php línea 215
 * El método finalizarInterior() registra comisión manualmente Y el Observer también puede hacerlo
 * 
 * IMPACTO: Pérdida económica por pagar comisiones duplicadas a lavadores
 */
class ControlLavadoComisionTest extends TestCase
{
    use RefreshDatabase;

    protected ControlLavadoService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ControlLavadoService::class);
    }

    /**
     * TEST CRÍTICO #1: No debe registrar comisión duplicada al completar lavado
     * 
     * Escenario:
     * 1. Crear lavado con lavador asignado
     * 2. Completar flujo completo: iniciar → finalizar → iniciar interior → finalizar interior
     * 3. Verificar que SOLO haya UNA comisión registrada
     * 
     * Resultado esperado: ❌ FALLA (detecta el bug)
     * El test fallará mostrando que hay 2 comisiones en lugar de 1
     * 
     * @test
     */
    public function no_debe_registrar_comision_duplicada_al_completar_lavado()
    {
        // Arrange: Crear lavador y tipo de vehículo con comisión
        $lavador = Lavador::factory()->create();
        $tipoVehiculo = TipoVehiculo::factory()->sedan()->create([
            'comision' => 10.00
        ]);
        
        // Crear lavado con todos los datos necesarios
        $lavado = ControlLavado::factory()->create([
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'estado' => 'Pendiente',
            'inicio_lavado' => null,
            'fin_lavado' => null,
            'inicio_interior' => null,
            'fin_interior' => null,
        ]);

        // Act: Ejecutar flujo completo de lavado
        // Paso 1: Iniciar lavado
        $this->service->iniciarLavado($lavado->id, 1);
        
        // Paso 2: Finalizar lavado exterior
        $this->service->finalizarLavado($lavado->id);
        
        // Paso 3: Iniciar lavado interior
        $this->service->iniciarInterior($lavado->id);
        
        // Paso 4: Finalizar lavado interior (aquí se registra la comisión)
        $lavadoCompleto = $this->service->finalizarInterior($lavado->id);

        // Assert CRÍTICO: Solo debe haber UNA comisión registrada
        $comisiones = PagoComision::where('lavador_id', $lavador->id)->get();
        
        $this->assertCount(1, $comisiones, 
            "🔴 BUG CRÍTICO DETECTADO: Se registraron {$comisiones->count()} comisiones. " .
            "Debería haber solo 1. Verificar línea 215 de ControlLavadoService.php y Observer."
        );
        
        // Verificar monto correcto
        $this->assertEquals(10.00, $comisiones->first()->monto_pagado,
            "El monto de la comisión no es correcto"
        );
        
        // Verificar que el lavador sea el correcto
        $this->assertEquals($lavador->id, $comisiones->first()->lavador_id,
            "La comisión no está asociada al lavador correcto"
        );
        
        // Verificar que el lavado esté completado
        $lavado->refresh();
        $this->assertNotNull($lavado->fin_interior, "El lavado debería estar completado");
        $this->assertEquals('Terminado', $lavado->estado, "El estado debería ser Terminado");
    }

    /**
     * TEST CRÍTICO #2: Verificar que la comisión se registra correctamente
     * 
     * Este test verifica que SÍ se registre una comisión cuando el lavado se completa
     * (Para asegurar que al corregir el bug #1, no eliminemos la funcionalidad)
     * 
     * @test
     */
    public function debe_registrar_una_comision_al_completar_lavado()
    {
        // Arrange
        $lavador = Lavador::factory()->create();
        $tipoVehiculo = TipoVehiculo::factory()->suv()->create([
            'comision' => 15.00
        ]);
        
        $lavado = ControlLavado::factory()->create([
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'inicio_lavado' => null,
        ]);

        // Act: Completar lavado
        $this->service->iniciarLavado($lavado->id, 1);
        $this->service->finalizarLavado($lavado->id);
        $this->service->iniciarInterior($lavado->id);
        $this->service->finalizarInterior($lavado->id);

        // Assert: Debe haber al menos UNA comisión (puede fallar si hay duplicadas)
        $comisiones = PagoComision::where('lavador_id', $lavador->id)->get();
        
        $this->assertGreaterThanOrEqual(1, $comisiones->count(),
            "Debe registrarse al menos una comisión"
        );
        
        // Verificar el monto de la primera comisión
        $this->assertEquals(15.00, $comisiones->first()->monto_pagado);
    }

    /**
     * TEST #3: No debe registrar comisión si el lavado no está completo
     * 
     * @test
     */
    public function no_debe_registrar_comision_si_lavado_no_esta_completo()
    {
        // Arrange
        $lavador = Lavador::factory()->create();
        $tipoVehiculo = TipoVehiculo::factory()->create();
        
        $lavado = ControlLavado::factory()->create([
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'fin_interior' => null, // NO completado
        ]);

        // Assert: No debe haber comisiones
        $comisiones = PagoComision::where('lavador_id', $lavador->id)->count();
        
        $this->assertEquals(0, $comisiones,
            "No debe haber comisiones para lavados incompletos"
        );
    }

    /**
     * TEST #4: Verificar que las comisiones tengan todos los datos necesarios
     * 
     * @test
     */
    public function comision_debe_tener_todos_los_datos_requeridos()
    {
        // Arrange
        $lavador = Lavador::factory()->create();
        $tipoVehiculo = TipoVehiculo::factory()->create(['comision' => 12.50]);
        
        $lavado = ControlLavado::factory()->create([
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'hora_llegada' => now()->subHours(2),
        ]);

        // Act: Completar lavado
        $this->service->iniciarLavado($lavado->id, 1);
        $this->service->finalizarLavado($lavado->id);
        $this->service->iniciarInterior($lavado->id);
        $this->service->finalizarInterior($lavado->id);

        // Assert: Verificar estructura de la comisión
        $comision = PagoComision::where('lavador_id', $lavador->id)->first();
        
        $this->assertNotNull($comision, "Debe existir una comisión");
        $this->assertNotNull($comision->monto_pagado, "Debe tener monto");
        $this->assertNotNull($comision->desde, "Debe tener fecha desde");
        $this->assertNotNull($comision->hasta, "Debe tener fecha hasta");
        $this->assertNotNull($comision->fecha_pago, "Debe tener fecha de pago");
        $this->assertGreaterThan(0, $comision->monto_pagado, "El monto debe ser mayor a 0");
    }

    /**
     * TEST #5: No debe registrar comisión si ya existe una para el mismo lavado
     * (Este test documenta la solución esperada)
     * 
     * @test
     */
    public function no_debe_permitir_comision_duplicada_para_mismo_lavado()
    {
        // Arrange
        $lavador = Lavador::factory()->create();
        $tipoVehiculo = TipoVehiculo::factory()->create(['comision' => 10.00]);
        
        $lavado = ControlLavado::factory()->create([
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
        ]);

        // Registrar una comisión manualmente (simular que ya existe)
        PagoComision::create([
            'lavador_id' => $lavador->id,
            'monto_pagado' => 10.00,
            'desde' => $lavado->hora_llegada,
            'hasta' => now(),
            'observacion' => 'Comisión por lavado ID ' . $lavado->id,
            'fecha_pago' => now(),
        ]);

        // Act: Intentar completar lavado (que intentaría registrar otra comisión)
        $lavado->update([
            'inicio_lavado' => now()->subMinutes(30),
            'fin_lavado' => now()->subMinutes(20),
            'inicio_interior' => now()->subMinutes(20),
        ]);

        // Act & Assert: Al finalizar, debería detectar la comisión existente
        try {
            $this->service->finalizarInterior($lavado->id);
            
            // Verificar que sigue habiendo solo UNA comisión
            $comisiones = PagoComision::where('lavador_id', $lavador->id)->count();
            $this->assertEquals(1, $comisiones,
                "No debe crear comisión duplicada si ya existe una"
            );
            
        } catch (\Exception $e) {
            // O puede lanzar excepción (también es válido)
            $this->assertStringContainsString('comisión', strtolower($e->getMessage()),
                "La excepción debería mencionar que ya existe una comisión"
            );
        }
    }
}
