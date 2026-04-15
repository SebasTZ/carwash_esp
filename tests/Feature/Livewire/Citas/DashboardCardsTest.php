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
            ->call('completar', $cita->id);

        $this->assertDatabaseHas('citas', [
            'id' => $cita->id,
            'estado' => 'pendiente',
        ]);
    }
}
