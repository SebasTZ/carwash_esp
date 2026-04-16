<?php

namespace Tests\Feature\Controllers;

use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Documento;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CitaControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Cliente $cliente;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ([
            'ver-cita',
            'crear-cita',
            'editar-cita',
            'eliminar-cita',
            'calendario-cita',
            'confirmar-cita',
        ] as $permiso) {
            Permission::findOrCreate($permiso);
        }

        $this->user = User::factory()->create();
        $this->user->givePermissionTo([
            'ver-cita',
            'crear-cita',
            'editar-cita',
            'eliminar-cita',
            'calendario-cita',
            'confirmar-cita',
        ]);

        $this->actingAs($this->user);

        $documento = Documento::factory()->create(['tipo_documento' => 'DNI']);
        $persona = Persona::factory()->create([
            'documento_id' => $documento->id,
            'estado' => 1,
            'tipo_persona' => 'Cliente',
        ]);

        $this->cliente = Cliente::factory()->create([
            'persona_id' => $persona->id,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function index_sin_fecha_filtra_por_hoy_por_defecto()
    {
        $citaHoy = Cita::factory()->create([
            'cliente_id' => $this->cliente->id,
            'fecha' => now()->toDateString(),
            'estado' => 'pendiente',
            'posicion_cola' => 1,
        ]);

        Cita::factory()->create([
            'cliente_id' => $this->cliente->id,
            'fecha' => now()->addDay()->toDateString(),
            'estado' => 'pendiente',
            'posicion_cola' => 2,
        ]);

        $response = $this->get(route('citas.index'));

        $response->assertOk();
        $response->assertViewIs('citas.index');
        $response->assertViewHas('citas', function ($citas) use ($citaHoy) {
            return $citas->count() === 1 && $citas->first()->id === $citaHoy->id;
        });
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function dashboard_retorna_vista_sin_error()
    {
        $response = $this->get(route('citas.dashboard'));

        $response->assertOk();
        $response->assertViewIs('citas.dashboard');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function dashboard_retorna_403_si_usuario_no_tiene_permiso()
    {
        $usuarioSinPermisos = User::factory()->create();

        $response = $this->actingAs($usuarioSinPermisos)->get(route('citas.dashboard'));

        $response->assertForbidden();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function store_asigna_siguiente_posicion_en_cola()
    {
        $fecha = now()->addDay()->toDateString();

        Cita::factory()->create([
            'cliente_id' => $this->cliente->id,
            'fecha' => $fecha,
            'hora' => '09:00',
            'posicion_cola' => 1,
            'estado' => 'pendiente',
        ]);

        $response = $this->post(route('citas.store'), [
            'cliente_id' => $this->cliente->id,
            'fecha' => $fecha,
            'hora' => '10:30',
            'notas' => 'Cliente puntual',
        ]);

        $response->assertRedirect(route('citas.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('citas', [
            'cliente_id' => $this->cliente->id,
            'fecha' => $fecha,
            'hora' => '10:30:00',
            'posicion_cola' => 2,
            'estado' => 'pendiente',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function update_recalcula_posicion_cuando_cambia_fecha()
    {
        $fechaOriginal = now()->addDay()->toDateString();
        $fechaNueva = now()->addDays(2)->toDateString();

        $cita = Cita::factory()->create([
            'cliente_id' => $this->cliente->id,
            'fecha' => $fechaOriginal,
            'hora' => '11:00',
            'posicion_cola' => 1,
            'estado' => 'pendiente',
        ]);

        Cita::factory()->create([
            'cliente_id' => $this->cliente->id,
            'fecha' => $fechaNueva,
            'hora' => '09:00',
            'posicion_cola' => 4,
            'estado' => 'pendiente',
        ]);

        $response = $this->put(route('citas.update', $cita), [
            'fecha' => $fechaNueva,
            'hora' => '12:00',
            'notas' => 'Cambio de fecha',
        ]);

        $response->assertRedirect(route('citas.index'));
        $response->assertSessionHas('success');

        $cita->refresh();
        $this->assertSame($fechaNueva, $cita->fecha->format('Y-m-d'));
        $this->assertSame(5, $cita->posicion_cola);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function iniciar_cita_actualiza_estado_a_en_proceso()
    {
        $cita = Cita::factory()->create([
            'cliente_id' => $this->cliente->id,
            'fecha' => now()->toDateString(),
            'estado' => 'pendiente',
        ]);

        $response = $this->post(route('citas.iniciar', $cita));

        $response->assertRedirect(route('citas.dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('citas', [
            'id' => $cita->id,
            'estado' => 'en_proceso',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function completar_cita_desde_estado_invalido_retorna_error_y_no_modifica_estado()
    {
        $cita = Cita::factory()->create([
            'cliente_id' => $this->cliente->id,
            'fecha' => now()->toDateString(),
            'estado' => 'pendiente',
        ]);

        $response = $this->post(route('citas.completar', $cita));

        $response->assertRedirect(route('citas.dashboard'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('citas', [
            'id' => $cita->id,
            'estado' => 'pendiente',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function update_retorna_403_si_no_es_propietario_de_la_cita()
    {
        $duenio = User::factory()->create();
        $duenio->givePermissionTo(['editar-cita']);

        $cita = Cita::factory()->create([
            'cliente_id' => $this->cliente->id,
            'user_id' => $duenio->id,
            'fecha' => now()->addDay()->toDateString(),
            'hora' => '11:00',
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($this->user)->put(route('citas.update', $cita), [
            'fecha' => now()->addDays(3)->toDateString(),
            'hora' => '12:00',
            'notas' => 'Intento sin propiedad',
        ]);

        $response->assertForbidden();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function iniciar_cita_retorna_403_si_no_es_propietario()
    {
        $duenio = User::factory()->create();
        $duenio->givePermissionTo(['confirmar-cita']);

        $cita = Cita::factory()->create([
            'cliente_id' => $this->cliente->id,
            'user_id' => $duenio->id,
            'fecha' => now()->toDateString(),
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($this->user)->post(route('citas.iniciar', $cita));

        $response->assertForbidden();
    }
}
