<?php

namespace Tests\Feature\Livewire\Citas;

use App\Livewire\Citas\DashboardCards;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Documento;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DashboardCardsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Cliente $cliente;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::findOrCreate('confirmar-cita');
        Permission::findOrCreate('calendario-cita');

        $this->user = User::factory()->create();
        $this->user->givePermissionTo(['confirmar-cita', 'calendario-cita']);

        $persona = Persona::factory()->create([
            'documento_id' => Documento::factory(),
            'razon_social' => 'Cliente Dashboard',
            'estado' => 1,
            'tipo_persona' => 'Cliente',
        ]);

        $this->cliente = Cliente::factory()->create([
            'persona_id' => $persona->id,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function renderiza_citas_del_dia_actual(): void
    {
        Cita::factory()->create([
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

        Livewire::actingAs($this->user)
            ->test(DashboardCards::class)
            ->assertSee('Total de Citas')
            ->assertSee('Cliente Dashboard');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function iniciar_cita_cambia_estado_a_en_proceso(): void
    {
        $cita = Cita::factory()->create([
            'cliente_id' => $this->cliente->id,
            'fecha' => now()->toDateString(),
            'estado' => 'pendiente',
            'posicion_cola' => 1,
        ]);

        Livewire::actingAs($this->user)
            ->test(DashboardCards::class)
            ->call('iniciar', $cita->id);

        $this->assertDatabaseHas('citas', [
            'id' => $cita->id,
            'estado' => 'en_proceso',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function completar_cita_cambia_estado_a_completada(): void
    {
        $cita = Cita::factory()->create([
            'cliente_id' => $this->cliente->id,
            'fecha' => now()->toDateString(),
            'estado' => 'en_proceso',
            'posicion_cola' => 1,
        ]);

        Livewire::actingAs($this->user)
            ->test(DashboardCards::class)
            ->call('completar', $cita->id);

        $this->assertDatabaseHas('citas', [
            'id' => $cita->id,
            'estado' => 'completada',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function bloquea_transicion_invalida_de_pendiente_a_completada(): void
    {
        $cita = Cita::factory()->create([
            'cliente_id' => $this->cliente->id,
            'fecha' => now()->toDateString(),
            'estado' => 'pendiente',
            'posicion_cola' => 1,
        ]);

        Livewire::actingAs($this->user)
            ->test(DashboardCards::class)
            ->call('completar', $cita->id)
            ->assertSee('Transición inválida de estado: pendiente -> completada.');

        $this->assertDatabaseHas('citas', [
            'id' => $cita->id,
            'estado' => 'pendiente',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_usuario_sin_permiso_no_puede_cambiar_estado(): void
    {
        // Usuario sin el permiso 'confirmar-cita'
        $userSinPermiso = User::factory()->create();
        $userSinPermiso->givePermissionTo('calendario-cita'); // Solo tiene acceso a ver, no a confirmar

        $cita = Cita::factory()->create([
            'cliente_id' => $this->cliente->id,
            'fecha' => now()->toDateString(),
            'estado' => 'pendiente',
            'posicion_cola' => 1,
        ]);

        Livewire::actingAs($userSinPermiso)
            ->test(DashboardCards::class)
            ->call('iniciar', $cita->id);

        // El estado no debe cambiar porque el usuario no tiene permiso
        $this->assertDatabaseHas('citas', [
            'id' => $cita->id,
            'estado' => 'pendiente',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_usuario_sin_permiso_calendario_recibe_403(): void
    {
        // Usuario sin ningún permiso de citas
        $userSinPermiso = User::factory()->create();

        Livewire::actingAs($userSinPermiso)
            ->test(DashboardCards::class)
            ->assertForbidden();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_cancelar_cita_cambia_estado_a_cancelada(): void
    {
        $cita = Cita::factory()->create([
            'cliente_id' => $this->cliente->id,
            'fecha' => now()->toDateString(),
            'estado' => 'pendiente',
            'posicion_cola' => 1,
        ]);

        Livewire::actingAs($this->user)
            ->test(DashboardCards::class)
            ->call('cancelar', $cita->id);

        $this->assertDatabaseHas('citas', [
            'id' => $cita->id,
            'estado' => 'cancelada',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_cita_inexistente_no_lanza_excepcion(): void
    {
        $idInexistente = 99999;

        // Llamar cancelar/iniciar con un ID que no existe no debe lanzar excepción:
        // el componente muestra un flash de error y retorna silenciosamente.
        Livewire::actingAs($this->user)
            ->test(DashboardCards::class)
            ->call('cancelar', $idInexistente)
            ->assertHasNoErrors();

        // No hay ninguna cita en BD con ese ID
        $this->assertDatabaseMissing('citas', ['id' => $idInexistente]);
    }
}
