<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\ControlLavado;
use App\Models\Lavador;
use App\Models\TipoVehiculo;
use App\Models\Venta;
use App\Models\Cliente;
use App\Models\User;
use App\Models\PagoComision;
use App\Models\AuditoriaLavador;
use App\Events\LavadorCambiadoEvent;
use App\Events\LavadoCompletadoEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Cache;

class ControlLavadoFlowIntegrationTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function flujo_completo_de_lavado_con_asignacion_inicio_y_finalizacion()
    {
        // Arrange - NO fake events para que Observer funcione
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create([
            'nombre' => 'Sedan',
            'comision' => 20.00,
            'estado' => 'activo',
        ]);
        $cliente = Cliente::factory()->create();
        $venta = Venta::factory()->create([
            'cliente_id' => $cliente->id,
            'total' => 100.00,
        ]);
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => null,
            'tipo_vehiculo_id' => null,
        ]);
        $user = User::factory()->create();
        $this->actingAs($user);

        // Act 1: Asignar lavador y tipo de vehículo
        $service = app(\App\Services\ControlLavadoService::class);
        $lavadoAsignado = $service->asignarLavador(
            lavadoId: $controlLavado->id,
            lavadorId: $lavador->id,
            tipoVehiculoId: $tipoVehiculo->id,
            motivo: 'Asignación inicial',
            usuarioId: $user->id
        );

        // Assert 1
        $this->assertEquals($lavador->id, $lavadoAsignado->lavador_id);
        $this->assertEquals($tipoVehiculo->id, $lavadoAsignado->tipo_vehiculo_id);

        // Act 2: Iniciar lavado
        $lavadoIniciado = $service->iniciarLavado($controlLavado->id, $user->id);

        // Assert 2
        $this->assertNotNull($lavadoIniciado->inicio_lavado);

        // Act 3: Finalizar lavado exterior
        $lavadoFinExterior = $service->finalizarLavado($controlLavado->id);

        // Assert 3
        $this->assertNotNull($lavadoFinExterior->fin_lavado);

        // Act 4: Iniciar interior
        $lavadoInicioInterior = $service->iniciarInterior($controlLavado->id);

        // Assert 4
        $this->assertNotNull($lavadoInicioInterior->inicio_interior);

        // Act 5: Finalizar interior (esto debe disparar observer)
        $lavadoCompletado = $service->finalizarInterior($controlLavado->id);

        // Assert 5
        $this->assertNotNull($lavadoCompletado->fin_interior);

        // Assert 6: Verificar que se creó la comisión automáticamente (tabla y campos correctos)
        $this->assertDatabaseHas('pagos_comisiones', [
            'lavador_id' => $lavador->id,
        ]);
        
        // Verificar monto de comisión
        $pagoComision = PagoComision::where('lavador_id', $lavador->id)->first();
        $this->assertNotNull($pagoComision);
        $this->assertEquals(20.00, $pagoComision->monto_pagado);
    }

    /** @test */
    public function flujo_con_cambio_de_lavador_registra_auditoria()
    {
        // Arrange
        Event::fake();
        
        $lavadorInicial = Lavador::factory()->create([
            'nombre' => 'Juan Pérez',
            'estado' => 'activo',
        ]);
        $lavadorNuevo = Lavador::factory()->create([
            'nombre' => 'Pedro García',
            'estado' => 'activo',
        ]);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        $venta = Venta::factory()->create(['total' => 100.00]);
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavadorInicial->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'inicio_lavado' => null,
        ]);
        $user = User::factory()->create();
        $this->actingAs($user);

        // Act: Cambiar lavador
        $service = app(\App\Services\ControlLavadoService::class);
        $lavadoCambiado = $service->asignarLavador(
            lavadoId: $controlLavado->id,
            lavadorId: $lavadorNuevo->id,
            tipoVehiculoId: $tipoVehiculo->id,
            motivo: 'Cambio por disponibilidad',
            usuarioId: $user->id
        );

        // Assert 1: Lavador actualizado
        $this->assertEquals($lavadorNuevo->id, $lavadoCambiado->lavador_id);

        // Assert 2: Auditoría registrada
        $this->assertDatabaseHas('auditoria_lavadores', [
            'control_lavado_id' => $controlLavado->id,
            'lavador_id_anterior' => $lavadorInicial->id,
            'lavador_id_nuevo' => $lavadorNuevo->id,
            'usuario_id' => $user->id,
            'motivo' => 'Cambio por disponibilidad',
        ]);

        // Assert 3: Puede obtener la auditoría
        $auditoria = AuditoriaLavador::where('control_lavado_id', $controlLavado->id)->first();
        $this->assertNotNull($auditoria);
        $this->assertEquals($lavadorInicial->id, $auditoria->lavador_id_anterior);
        $this->assertEquals($lavadorNuevo->id, $auditoria->lavador_id_nuevo);
    }

    /** @test */
    public function no_permite_asignar_lavador_despues_de_iniciar()
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
            'inicio_lavado' => now(),
        ]);
        $user = User::factory()->create();
        $this->actingAs($user);

        // Assert & Act
        $this->expectException(\App\Exceptions\LavadoYaIniciadoException::class);

        $service = app(\App\Services\ControlLavadoService::class);
        $service->asignarLavador(
            lavadoId: $controlLavado->id,
            lavadorId: $nuevoLavador->id,
            tipoVehiculoId: $tipoVehiculo->id,
            motivo: 'Intento de cambio',
            usuarioId: $user->id
        );
    }

    /** @test */
    public function calcula_comisiones_correctas_para_diferentes_vehiculos()
    {
        // Arrange - NO fake events
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        
        $testCases = [
            ['tipo' => 'Moto', 'comision' => 15.00],
            ['tipo' => 'Sedan', 'comision' => 20.00],
            ['tipo' => 'SUV', 'comision' => 25.00],
            ['tipo' => 'Camioneta', 'comision' => 30.00],
        ];

        foreach ($testCases as $index => $testCase) {
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
                'inicio_lavado' => now()->subHour(),
                'fin_lavado' => now()->subMinutes(30),
                'inicio_interior' => now()->subMinutes(20),
                'fin_interior' => null,
            ]);

            // Act
            $service = app(\App\Services\ControlLavadoService::class);
            $service->finalizarInterior($controlLavado->id);

            // Assert - Buscar por observacion ya que no hay control_lavado_id
            $pagoComision = PagoComision::where('lavador_id', $lavador->id)
                ->where('observacion', 'LIKE', '%lavado ID ' . $controlLavado->id . '%')
                ->first();
            $this->assertNotNull($pagoComision, "No se creó comisión para {$testCase['tipo']}");
            $this->assertEquals(
                $testCase['comision'],
                $pagoComision->monto_pagado,
                "Comisión incorrecta para {$testCase['tipo']}"
            );
        }
    }

    /** @test */
    public function cache_se_invalida_correctamente_en_actualizaciones()
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

        $repository = app(\App\Repositories\ControlLavadoRepository::class);

        // Act 1: Leer (cachea)
        $repository->findOrFail($controlLavado->id);
        $this->assertTrue(Cache::has("control_lavado:{$controlLavado->id}"));

        // Act 2: Actualizar (debe invalidar caché)
        $repository->update($controlLavado->id, ['inicio_lavado' => now()]);

        // Assert: Caché invalidado
        $this->assertFalse(Cache::has("control_lavado:{$controlLavado->id}"));
    }

    /** @test */
    public function puede_obtener_lavados_filtrados_por_lavador_y_fecha()
    {
        // Arrange
        $lavador1 = Lavador::factory()->create(['estado' => 'activo']);
        $lavador2 = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        
        // Lavados del lavador 1, hoy
        ControlLavado::factory()->count(3)->create([
            'lavador_id' => $lavador1->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'created_at' => now(),
        ]);
        
        // Lavados del lavador 2, hoy
        ControlLavado::factory()->count(2)->create([
            'lavador_id' => $lavador2->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'created_at' => now(),
        ]);
        
        // Lavados del lavador 1, ayer
        ControlLavado::factory()->count(1)->create([
            'lavador_id' => $lavador1->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'created_at' => now()->subDay(),
        ]);

        // Act
        $service = app(\App\Services\ControlLavadoService::class);
        $resultado = $service->obtenerLavadosConFiltros([
            'lavador_id' => $lavador1->id,
            'fecha' => now()->format('Y-m-d'),
        ], 20);

        // Assert - Al menos los 3 del lavador1 hoy
        $this->assertGreaterThanOrEqual(3, $resultado->count());
        foreach ($resultado as $lavado) {
            $this->assertEquals($lavador1->id, $lavado->lavador_id);
        }
    }

    /** @test */
    public function exportaciones_usan_repository()
    {
        // Arrange
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        
        ControlLavado::factory()->count(5)->create([
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'created_at' => now(),
        ]);

        // Act
        $repository = app(\App\Repositories\ControlLavadoRepository::class);
        $lavadosHoy = $repository->getToday();
        $lavadosSemana = $repository->getThisWeek();
        $lavadosMes = $repository->getThisMonth();

        // Assert
        $this->assertCount(5, $lavadosHoy);
        $this->assertCount(5, $lavadosSemana);
        $this->assertCount(5, $lavadosMes);
    }

    /** @test */
    public function flujo_completo_con_multiples_cambios_de_lavador()
    {
        // Arrange
        Event::fake();
        
        $lavador1 = Lavador::factory()->create(['nombre' => 'Lavador 1', 'estado' => 'activo']);
        $lavador2 = Lavador::factory()->create(['nombre' => 'Lavador 2', 'estado' => 'activo']);
        $lavador3 = Lavador::factory()->create(['nombre' => 'Lavador 3', 'estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        $venta = Venta::factory()->create(['total' => 100.00]);
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavador1->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'inicio_lavado' => null,
        ]);
        $user = User::factory()->create();
        $this->actingAs($user);

        $service = app(\App\Services\ControlLavadoService::class);

        // Act 1: Cambio a lavador 2
        $service->asignarLavador(
            lavadoId: $controlLavado->id,
            lavadorId: $lavador2->id,
            tipoVehiculoId: $tipoVehiculo->id,
            motivo: 'Primer cambio',
            usuarioId: $user->id
        );

        // Act 2: Cambio a lavador 3
        $service->asignarLavador(
            lavadoId: $controlLavado->id,
            lavadorId: $lavador3->id,
            tipoVehiculoId: $tipoVehiculo->id,
            motivo: 'Segundo cambio',
            usuarioId: $user->id
        );

        // Assert: Debe haber 2 auditorías
        $auditorias = AuditoriaLavador::where('control_lavado_id', $controlLavado->id)
            ->orderBy('created_at')
            ->get();

        $this->assertCount(2, $auditorias);
        
        // Primera auditoría: lavador1 -> lavador2
        $this->assertEquals($lavador1->id, $auditorias[0]->lavador_id_anterior);
        $this->assertEquals($lavador2->id, $auditorias[0]->lavador_id_nuevo);
        
        // Segunda auditoría: lavador2 -> lavador3
        $this->assertEquals($lavador2->id, $auditorias[1]->lavador_id_anterior);
        $this->assertEquals($lavador3->id, $auditorias[1]->lavador_id_nuevo);

        // Assert: Lavador final es lavador3
        $controlLavado->refresh();
        $this->assertEquals($lavador3->id, $controlLavado->lavador_id);
    }
}
