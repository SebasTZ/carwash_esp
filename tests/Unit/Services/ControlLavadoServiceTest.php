<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\ControlLavadoService;
use App\Services\AuditoriaService;
use App\Services\ComisionService;
use App\Repositories\ControlLavadoRepository;
use App\Models\ControlLavado;
use App\Models\Lavador;
use App\Models\TipoVehiculo;
use App\Models\Venta;
use App\Models\User;
use App\Exceptions\LavadoYaIniciadoException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ControlLavadoServiceTest extends TestCase
{
    use DatabaseMigrations;

    protected ControlLavadoService $controlLavadoService;
    protected ControlLavadoRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->controlLavadoService = app(ControlLavadoService::class);
        $this->repository = app(ControlLavadoRepository::class);
    }

    /** @test */
    public function puede_asignar_lavador_y_tipo_vehiculo()
    {
        // Arrange
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        $venta = Venta::factory()->create();
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => null,
            'tipo_vehiculo_id' => null,
            'inicio_lavado' => null,
        ]);
        $user = User::factory()->create();

        // Act
        $resultado = $this->controlLavadoService->asignarLavador(
            lavadoId: $controlLavado->id,
            lavadorId: $lavador->id,
            tipoVehiculoId: $tipoVehiculo->id,
            motivo: 'Asignación inicial',
            usuarioId: $user->id
        );

        // Assert
        $this->assertInstanceOf(ControlLavado::class, $resultado);
        $this->assertEquals($lavador->id, $resultado->lavador_id);
        $this->assertEquals($tipoVehiculo->id, $resultado->tipo_vehiculo_id);
        
        // Verificar que NO se creó auditoría (primera asignación)
        $this->assertDatabaseMissing('auditoria_lavadores', [
            'control_lavado_id' => $controlLavado->id,
        ]);
    }

    /** @test */
    public function crea_auditoria_al_cambiar_lavador()
    {
        // Arrange
        $lavadorAnterior = Lavador::factory()->create(['estado' => 'activo']);
        $lavadorNuevo = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        $venta = Venta::factory()->create();
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavadorAnterior->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'inicio_lavado' => null,
        ]);
        $user = User::factory()->create();

        // Act
        $resultado = $this->controlLavadoService->asignarLavador(
            lavadoId: $controlLavado->id,
            lavadorId: $lavadorNuevo->id,
            tipoVehiculoId: $tipoVehiculo->id,
            motivo: 'Cambio por disponibilidad',
            usuarioId: $user->id
        );

        // Assert
        $this->assertEquals($lavadorNuevo->id, $resultado->lavador_id);
        
        // Verificar que SÍ se creó auditoría
        $this->assertDatabaseHas('auditoria_lavadores', [
            'control_lavado_id' => $controlLavado->id,
            'lavador_id_anterior' => $lavadorAnterior->id,
            'lavador_id_nuevo' => $lavadorNuevo->id,
            'usuario_id' => $user->id,
            'motivo' => 'Cambio por disponibilidad',
        ]);
    }

    /** @test */
    public function no_permite_asignar_lavador_si_lavado_ya_inicio()
    {
        // Arrange
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        $venta = Venta::factory()->create();
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'inicio_lavado' => now(),
        ]);
        $user = User::factory()->create();

        // Assert & Act
        $this->expectException(LavadoYaIniciadoException::class);

        $this->controlLavadoService->asignarLavador(
            lavadoId: $controlLavado->id,
            lavadorId: $lavador->id,
            tipoVehiculoId: $tipoVehiculo->id,
            motivo: null,
            usuarioId: $user->id
        );
    }

    /** @test */
    public function puede_iniciar_lavado()
    {
        // Arrange
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        $venta = Venta::factory()->create();
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'inicio_lavado' => null,
        ]);
        $user = User::factory()->create();

        // Act
        $resultado = $this->controlLavadoService->iniciarLavado($controlLavado->id, $user->id);

        // Assert
        $this->assertNotNull($resultado->inicio_lavado);
        $this->assertInstanceOf(\Carbon\Carbon::class, $resultado->inicio_lavado);
    }

    /** @test */
    public function no_permite_iniciar_lavado_dos_veces()
    {
        // Arrange
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        $venta = Venta::factory()->create();
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'inicio_lavado' => now(),
        ]);
        $user = User::factory()->create();

        // Assert & Act
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('El lavado ya fue iniciado.');

        $this->controlLavadoService->iniciarLavado($controlLavado->id, $user->id);
    }

    /** @test */
    public function puede_finalizar_lavado()
    {
        // Arrange
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        $venta = Venta::factory()->create();
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'inicio_lavado' => now()->subMinutes(30),
            'fin_lavado' => null,
        ]);

        // Act
        $resultado = $this->controlLavadoService->finalizarLavado($controlLavado->id);

        // Assert
        $this->assertNotNull($resultado->fin_lavado);
        $this->assertInstanceOf(\Carbon\Carbon::class, $resultado->fin_lavado);
    }

    /** @test */
    public function puede_iniciar_interior()
    {
        // Arrange
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        $venta = Venta::factory()->create();
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'inicio_lavado' => now()->subMinutes(30),
            'fin_lavado' => now()->subMinutes(10),
            'inicio_interior' => null,
        ]);

        // Act
        $resultado = $this->controlLavadoService->iniciarInterior($controlLavado->id);

        // Assert
        $this->assertNotNull($resultado->inicio_interior);
        $this->assertInstanceOf(\Carbon\Carbon::class, $resultado->inicio_interior);
    }

    /** @test */
    public function puede_finalizar_interior()
    {
        // Arrange
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
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
        $resultado = $this->controlLavadoService->finalizarInterior($controlLavado->id);

        // Assert
        $this->assertNotNull($resultado->fin_interior);
        $this->assertInstanceOf(\Carbon\Carbon::class, $resultado->fin_interior);
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
        $user = User::factory()->create();

        // Act
        $resultado = $this->controlLavadoService->eliminarLavado($controlLavado->id, $user->id);

        // Assert
        $this->assertTrue($resultado);
        $this->assertSoftDeleted('control_lavados', [
            'id' => $controlLavado->id,
        ]);
    }

    /** @test */
    public function obtiene_lavados_con_filtros()
    {
        // Arrange
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        $venta = Venta::factory()->create();
        
        ControlLavado::factory()->count(5)->create([
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
        ]);

        // Act
        $resultado = $this->controlLavadoService->obtenerLavadosConFiltros([
            'lavador_id' => $lavador->id,
        ], 10);

        // Assert
        $this->assertNotNull($resultado);
        $this->assertCount(5, $resultado);
    }

    /** @test */
    public function obtiene_lavado_con_relaciones()
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
        $resultado = $this->controlLavadoService->obtenerLavadoConRelaciones(
            $controlLavado->id,
            ['venta', 'lavador', 'tipoVehiculo']
        );

        // Assert
        $this->assertInstanceOf(ControlLavado::class, $resultado);
        $this->assertTrue($resultado->relationLoaded('venta'));
        $this->assertTrue($resultado->relationLoaded('lavador'));
        $this->assertTrue($resultado->relationLoaded('tipoVehiculo'));
    }
}
