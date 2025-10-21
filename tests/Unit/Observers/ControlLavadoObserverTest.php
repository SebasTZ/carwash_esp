<?php

namespace Tests\Unit\Observers;

use Tests\TestCase;
use App\Observers\ControlLavadoObserver;
use App\Services\ComisionService;
use App\Models\ControlLavado;
use App\Models\Lavador;
use App\Models\TipoVehiculo;
use App\Models\Venta;
use App\Models\PagoComision;
use App\Events\LavadoCompletadoEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Event;

class ControlLavadoObserverTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Registrar el observer manualmente para los tests
        ControlLavado::observe(ControlLavadoObserver::class);
    }

    /** @test */
    public function registra_comision_al_finalizar_interior()
    {
        // Arrange - NO fake events para que el Observer funcione
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create([
            'nombre' => 'Sedan',
            'comision' => 20.00,
            'estado' => 'activo',
        ]);
        $venta = Venta::factory()->create(['total' => 100.00]);
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'inicio_lavado' => now()->subMinutes(60),
            'fin_lavado' => now()->subMinutes(30),
            'inicio_interior' => now()->subMinutes(20),
            'fin_interior' => null,
        ]);

        // Act - Marcar como finalizado debería disparar el observer
        $controlLavado->fin_interior = now();
        $controlLavado->save();

        // Assert - Verificar que se creó el pago de comisión
        $this->assertDatabaseHas('pagos_comisiones', [
            'lavador_id' => $lavador->id,
        ]);
        
        // Verificar que el monto de la comisión se calculó correctamente
        $pagoComision = PagoComision::where('lavador_id', $lavador->id)->first();
        $this->assertNotNull($pagoComision);
        $this->assertEquals(20.00, $pagoComision->monto_pagado);
        $this->assertStringContainsString('Comisión por lavado ID ' . $controlLavado->id, $pagoComision->observacion);
    }

    /** @test */
    public function no_registra_comision_si_no_se_finaliza_interior()
    {
        // Arrange - NO fake events para que el Observer funcione
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        $venta = Venta::factory()->create(['total' => 100.00]);
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'inicio_lavado' => now()->subMinutes(30),
            'fin_lavado' => null,
            'fin_interior' => null,
        ]);

        // Act - Solo marcar fin de lavado, NO interior
        $controlLavado->fin_lavado = now();
        $controlLavado->save();

        // Assert - No debe haber comisión
        $this->assertDatabaseMissing('pagos_comisiones', [
            'lavador_id' => $lavador->id,
        ]);
    }

    /** @test */
    public function no_registra_comision_duplicada()
    {
        // Arrange - NO fake events
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create([
            'nombre' => 'Sedan',
            'comision' => 20.00,
            'estado' => 'activo',
        ]);
        $venta = Venta::factory()->create(['total' => 100.00]);
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'inicio_lavado' => now()->subMinutes(60),
            'fin_lavado' => now()->subMinutes(30),
            'inicio_interior' => now()->subMinutes(20),
            'fin_interior' => now(),
        ]);

        // Ya existe una comisión por este lavado (finalizado previamente)
        $comisionesAntes = PagoComision::where('lavador_id', $lavador->id)->count();

        // Act - Actualizar otro campo (NO cambiar fin_interior) - usar estado que sí existe
        $controlLavado->estado = 'Completado';
        $controlLavado->save();

        // Assert - No debe haber creado otra comisión
        $comisionesDespues = PagoComision::where('lavador_id', $lavador->id)->count();
        $this->assertEquals($comisionesAntes, $comisionesDespues);
    }

    /** @test */
    public function calcula_comision_correcta_segun_tipo_vehiculo()
    {
        // Arrange - NO fake events
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        
        $testCases = [
            ['tipo' => 'Moto', 'comision' => 15.00],
            ['tipo' => 'Sedan', 'comision' => 20.00],
            ['tipo' => 'SUV', 'comision' => 25.00],
            ['tipo' => 'Camioneta', 'comision' => 30.00],
        ];

        foreach ($testCases as $testCase) {
            // Arrange
            $tipoVehiculo = TipoVehiculo::factory()->create([
                'nombre' => $testCase['tipo'],
                'comision' => $testCase['comision'],
                'estado' => 'activo',
            ]);
            $venta = Venta::factory()->create(['total' => 100.00]);
            $controlLavado = ControlLavado::factory()->create([
                'venta_id' => $venta->id,
                'lavador_id' => $lavador->id,
                'tipo_vehiculo_id' => $tipoVehiculo->id,
                'inicio_lavado' => now()->subMinutes(60),
                'fin_lavado' => now()->subMinutes(30),
                'inicio_interior' => now()->subMinutes(20),
                'fin_interior' => null,
            ]);

            // Act
            $controlLavado->fin_interior = now();
            $controlLavado->save();

            // Assert - Verificar que se creó la comisión con el monto correcto
            $pagoComision = PagoComision::where('lavador_id', $lavador->id)
                ->where('observacion', 'LIKE', '%lavado ID ' . $controlLavado->id . '%')
                ->first();
                
            $this->assertNotNull($pagoComision, "No se encontró comisión para {$testCase['tipo']}");
            $this->assertEquals($testCase['comision'], $pagoComision->monto_pagado, "Comisión incorrecta para {$testCase['tipo']}");
        }
    }

    /** @test */
    public function observer_maneja_errores_sin_romper_actualizacion()
    {
        // Arrange - NO fake events
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        // Tipo de vehículo sin comisión configurada
        $tipoVehiculo = TipoVehiculo::factory()->create([
            'comision' => 0,
            'estado' => 'activo',
        ]);
        
        $venta = Venta::factory()->create(['total' => 100.00]);
        
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'inicio_lavado' => now()->subMinutes(60),
            'fin_lavado' => now()->subMinutes(30),
            'inicio_interior' => now()->subMinutes(20),
            'fin_interior' => null,
        ]);

        // Act - No debe lanzar excepción, debe usar comisión base
        $controlLavado->fin_interior = now();
        $controlLavado->save();

        // Assert - El lavado debe haberse actualizado y debe haber creado comisión base
        $controlLavado->refresh();
        $this->assertNotNull($controlLavado->fin_interior);
        
        // Debe haber creado una comisión con el valor base (10.00)
        $pagoComision = PagoComision::where('lavador_id', $lavador->id)->first();
        $this->assertNotNull($pagoComision);
        $this->assertEquals(10.00, $pagoComision->monto_pagado);
    }
}
