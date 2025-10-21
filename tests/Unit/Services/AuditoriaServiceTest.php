<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\AuditoriaService;
use App\Repositories\AuditoriaLavadorRepository;
use App\Models\ControlLavado;
use App\Models\Lavador;
use App\Models\TipoVehiculo;
use App\Models\Venta;
use App\Models\User;
use App\Models\AuditoriaLavador;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AuditoriaServiceTest extends TestCase
{
    use DatabaseMigrations;

    protected AuditoriaService $auditoriaService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->auditoriaService = app(AuditoriaService::class);
    }

    /** @test */
    public function puede_registrar_cambio_de_lavador()
    {
        // Arrange
        $lavadorAnterior = Lavador::factory()->create(['estado' => 'activo']);
        $lavadorNuevo = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        $venta = Venta::factory()->create();
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavadorNuevo->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
        ]);
        $user = User::factory()->create();

        // Act
        $auditoria = $this->auditoriaService->registrarCambioLavador(
            controlLavadoId: $controlLavado->id,
            lavadorAnteriorId: $lavadorAnterior->id,
            lavadorNuevoId: $lavadorNuevo->id,
            usuarioId: $user->id,
            motivo: 'Cambio por disponibilidad'
        );

        // Assert
        $this->assertInstanceOf(AuditoriaLavador::class, $auditoria);
        $this->assertEquals($controlLavado->id, $auditoria->control_lavado_id);
        $this->assertEquals($lavadorAnterior->id, $auditoria->lavador_id_anterior);
        $this->assertEquals($lavadorNuevo->id, $auditoria->lavador_id_nuevo);
        $this->assertEquals($user->id, $auditoria->usuario_id);
        $this->assertEquals('Cambio por disponibilidad', $auditoria->motivo);

        $this->assertDatabaseHas('auditoria_lavadores', [
            'control_lavado_id' => $controlLavado->id,
            'lavador_id_anterior' => $lavadorAnterior->id,
            'lavador_id_nuevo' => $lavadorNuevo->id,
            'usuario_id' => $user->id,
            'motivo' => 'Cambio por disponibilidad',
        ]);
    }

    /** @test */
    public function puede_obtener_auditoria_por_control_lavado()
    {
        // Arrange
        $lavadorAnterior = Lavador::factory()->create(['estado' => 'activo']);
        $lavadorNuevo = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        $venta = Venta::factory()->create();
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavadorNuevo->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
        ]);
        $user = User::factory()->create();

        AuditoriaLavador::factory()->create([
            'control_lavado_id' => $controlLavado->id,
            'lavador_id_anterior' => $lavadorAnterior->id,
            'lavador_id_nuevo' => $lavadorNuevo->id,
            'usuario_id' => $user->id,
        ]);

        // Act
        $auditorias = $this->auditoriaService->obtenerHistorial($controlLavado->id);

        // Assert
        $this->assertCount(1, $auditorias);
        $this->assertEquals($controlLavado->id, $auditorias->first()->control_lavado_id);
    }

    /** @test */
    public function puede_obtener_auditoria_por_usuario()
    {
        // Arrange
        $lavadorAnterior = Lavador::factory()->create(['estado' => 'activo']);
        $lavadorNuevo = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        $venta = Venta::factory()->create();
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavadorNuevo->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
        ]);
        $user = User::factory()->create();

        AuditoriaLavador::factory()->count(3)->create([
            'control_lavado_id' => $controlLavado->id,
            'lavador_id_anterior' => $lavadorAnterior->id,
            'lavador_id_nuevo' => $lavadorNuevo->id,
            'usuario_id' => $user->id,
        ]);

        // Act
        $auditorias = $this->auditoriaService->obtenerPorUsuario($user->id);

        // Assert
        $this->assertCount(3, $auditorias);
        $this->assertEquals($user->id, $auditorias->first()->usuario_id);
    }

    /** @test */
    public function puede_obtener_auditoria_por_rango_de_fechas()
    {
        // Arrange
        $lavadorAnterior = Lavador::factory()->create(['estado' => 'activo']);
        $lavadorNuevo = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        $venta = Venta::factory()->create();
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavadorNuevo->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
        ]);
        $user = User::factory()->create();

        AuditoriaLavador::factory()->create([
            'control_lavado_id' => $controlLavado->id,
            'lavador_id_anterior' => $lavadorAnterior->id,
            'lavador_id_nuevo' => $lavadorNuevo->id,
            'usuario_id' => $user->id,
            'created_at' => now(),
        ]);

        $fechaInicio = now()->subDays(1)->format('Y-m-d');
        $fechaFin = now()->addDays(1)->format('Y-m-d');

        // Act
        $auditorias = $this->auditoriaService->obtenerPorRangoFechas($fechaInicio, $fechaFin);

        // Assert
        $this->assertCount(1, $auditorias);
    }

    /** @test */
    public function registra_motivo_default_si_no_se_proporciona()
    {
        // Arrange
        $lavadorAnterior = Lavador::factory()->create(['estado' => 'activo']);
        $lavadorNuevo = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        $venta = Venta::factory()->create();
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavadorNuevo->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
        ]);
        $user = User::factory()->create();

        // Act
        $auditoria = $this->auditoriaService->registrarCambioLavador(
            controlLavadoId: $controlLavado->id,
            lavadorAnteriorId: $lavadorAnterior->id,
            lavadorNuevoId: $lavadorNuevo->id,
            usuarioId: $user->id,
            motivo: null
        );

        // Assert
        $this->assertNotNull($auditoria->motivo);
        $this->assertEquals('Cambio de lavador', $auditoria->motivo);
    }
}
