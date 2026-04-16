<?php

namespace Tests\Feature\Controllers;

use App\Exceptions\LavadoYaIniciadoException;
use App\Models\ControlLavado;
use App\Models\Lavador;
use App\Models\TipoVehiculo;
use App\Models\User;
use App\Repositories\ControlLavadoRepository;
use App\Services\ControlLavadoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class ControlLavadoControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected MockInterface $serviceMock;
    protected MockInterface $repositoryMock;

    protected function setUp(): void
    {
        parent::setUp();

        \Spatie\Permission\Models\Permission::create(['name' => 'ver-control-lavado']);
        \Spatie\Permission\Models\Permission::create(['name' => 'crear-control-lavado']);
        \Spatie\Permission\Models\Permission::create(['name' => 'editar-control-lavado']);
        \Spatie\Permission\Models\Permission::create(['name' => 'eliminar-control-lavado']);
        \Spatie\Permission\Models\Permission::create(['name' => 'exportar-reporte-lavado']);

        $role = \Spatie\Permission\Models\Role::create(['name' => 'admin-lavado-test']);
        $role->givePermissionTo([
            'ver-control-lavado', 'crear-control-lavado',
            'editar-control-lavado', 'eliminar-control-lavado', 'exportar-reporte-lavado',
        ]);

        $this->user = User::factory()->create();
        $this->user->assignRole('admin-lavado-test');
        $this->actingAs($this->user);

        $this->serviceMock = Mockery::mock(ControlLavadoService::class)->shouldIgnoreMissing();
        $this->repositoryMock = Mockery::mock(ControlLavadoRepository::class)->shouldIgnoreMissing();

        $this->app->instance(ControlLavadoService::class, $this->serviceMock);
        $this->app->instance(ControlLavadoRepository::class, $this->repositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function inicio_lavado_sin_confirmacion_envia_flag_de_confirmacion()
    {
        $lavadoId = 12;

        $response = $this->post(route('control.lavados.inicioLavado', $lavadoId), [
            'confirmar' => 'no',
        ]);

        $response->assertRedirect(route('control.lavados'));
        $response->assertSessionHas('confirmar_inicio', $lavadoId);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function inicio_lavado_confirmado_invoca_servicio_y_retorna_success()
    {
        $lavadoId = 20;

        $this->serviceMock
            ->shouldReceive('iniciarLavado')
            ->once()
            ->with($lavadoId, $this->user->id)
            ->andReturn(ControlLavado::factory()->make());

        $response = $this->post(route('control.lavados.inicioLavado', $lavadoId), [
            'confirmar' => 'si',
        ]);

        $response->assertRedirect(route('control.lavados'));
        $response->assertSessionHas('success');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function asignar_lavador_captura_lavado_ya_iniciado_exception()
    {
        $lavadoId = 5;
        $lavador = Lavador::factory()->create();
        $tipoVehiculo = TipoVehiculo::factory()->create();

        $this->serviceMock
            ->shouldReceive('asignarLavador')
            ->once()
            ->andThrow(new LavadoYaIniciadoException($lavadoId));

        $response = $this->post(route('control.lavados.asignarLavador', $lavadoId), [
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'motivo' => 'Reasignacion',
        ]);

        $response->assertRedirect(route('control.lavados'));
        $response->assertSessionHas('error');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function destroy_si_servicio_lanza_error_retorna_mensaje_generico()
    {
        $lavadoId = 30;

        $this->serviceMock
            ->shouldReceive('eliminarLavado')
            ->once()
            ->with($lavadoId, $this->user->id)
            ->andThrow(new \Exception('Error inesperado'));

        $response = $this->delete(route('control.lavados.destroy', $lavadoId));

        $response->assertRedirect(route('control.lavados'));
        $response->assertSessionHas('error', 'Error al eliminar el lavado.');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function show_si_falla_servicio_redirige_a_index_con_error()
    {
        $lavadoId = 99;

        $this->serviceMock
            ->shouldReceive('obtenerLavadoConRelaciones')
            ->once()
            ->with($lavadoId, Mockery::type('array'))
            ->andThrow(new \Exception('No existe'));

        $response = $this->get(route('control.lavados.show', $lavadoId));

        $response->assertRedirect(route('control.lavados'));
        $response->assertSessionHas('error', 'Lavado no encontrado.');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function index_retorna_403_si_usuario_no_tiene_permiso(): void
    {
        $sinPermisos = User::factory()->create();
        $this->actingAs($sinPermisos);

        $response = $this->get(route('control.lavados'));

        $response->assertStatus(403);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function destroy_retorna_403_si_usuario_no_tiene_permiso(): void
    {
        $sinPermisos = User::factory()->create();
        $this->actingAs($sinPermisos);

        $response = $this->delete(route('control.lavados.destroy', 999));

        $response->assertStatus(403);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function asignar_lavador_retorna_403_si_usuario_no_tiene_permiso(): void
    {
        $sinPermisos = User::factory()->create();
        $this->actingAs($sinPermisos);

        $response = $this->post(route('control.lavados.asignarLavador', 999), []);

        $response->assertStatus(403);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function inicio_lavado_retorna_403_si_usuario_no_tiene_permiso(): void
    {
        $sinPermisos = User::factory()->create();
        $this->actingAs($sinPermisos);

        $response = $this->post(route('control.lavados.inicioLavado', 999), []);

        $response->assertStatus(403);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function export_diario_retorna_403_si_usuario_no_tiene_permiso(): void
    {
        $sinPermisos = User::factory()->create();
        $this->actingAs($sinPermisos);

        $response = $this->get(route('control.lavados.export.diario'));

        $response->assertStatus(403);
    }
}
