<?php

namespace Tests\Feature\Livewire\Ventas;

use App\Livewire\Ventas\ClienteSelect;
use App\Models\Cliente;
use App\Models\Documento;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ClienteSelectTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->user = User::factory()->create();
        $this->user->assignRole(\Spatie\Permission\Models\Role::findOrCreate('cajero'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function filtra_clientes_activos_por_nombre_o_documento(): void
    {
        $personaActiva = Persona::factory()->create([
            'documento_id' => Documento::factory(),
            'razon_social' => 'Carlos Lavador',
            'numero_documento' => '70223311',
            'estado' => 1,
        ]);

        Cliente::factory()->create([
            'persona_id' => $personaActiva->id,
        ]);

        $personaInactiva = Persona::factory()->create([
            'documento_id' => Documento::factory(),
            'razon_social' => 'Cliente Inactivo',
            'numero_documento' => '70000000',
            'estado' => 0,
        ]);

        Cliente::factory()->create([
            'persona_id' => $personaInactiva->id,
        ]);

        Livewire::actingAs($this->user)
            ->test(ClienteSelect::class)
            ->set('search', 'Carlos')
            ->assertSee('Carlos Lavador - 70223311')
            ->assertDontSee('Cliente Inactivo - 70000000');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function selecciona_cliente_y_actualiza_estado(): void
    {
        $persona = Persona::factory()->create([
            'documento_id' => Documento::factory(),
            'razon_social' => 'Ana Cliente',
            'numero_documento' => '45556667',
            'estado' => 1,
        ]);

        $cliente = Cliente::factory()->create([
            'persona_id' => $persona->id,
        ]);

        Livewire::actingAs($this->user)
            ->test(ClienteSelect::class)
            ->call('selectOption', (string) $cliente->id)
            ->assertSet('selected', (string) $cliente->id)
            ->assertSet('selectedLabel', 'Ana Cliente - 45556667')
            ->assertDispatched('venta-select-updated');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function clear_selection_limpia_estado(): void
    {
        $persona = Persona::factory()->create([
            'documento_id' => Documento::factory(),
            'razon_social' => 'Pedro Test',
            'numero_documento' => '12345678',
            'estado' => 1,
        ]);

        $cliente = Cliente::factory()->create(['persona_id' => $persona->id]);

        Livewire::actingAs($this->user)
            ->test(ClienteSelect::class, ['value' => (string) $cliente->id])
            ->call('clearSelection')
            ->assertSet('selected', null)
            ->assertSet('selectedLabel', '')
            ->assertDispatched('venta-select-updated');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function sync_from_external_actualiza_estado(): void
    {
        $persona = Persona::factory()->create([
            'documento_id' => Documento::factory(),
            'razon_social' => 'Maria Sync',
            'numero_documento' => '87654321',
            'estado' => 1,
        ]);

        $cliente = Cliente::factory()->create(['persona_id' => $persona->id]);

        Livewire::actingAs($this->user)
            ->test(ClienteSelect::class)
            ->call('syncFromExternal', 'cliente_id', (string) $cliente->id, 'Maria Sync - 87654321')
            ->assertSet('selected', (string) $cliente->id)
            ->assertSet('selectedLabel', 'Maria Sync - 87654321');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function busqueda_vacia_devuelve_resultados_con_limite(): void
    {
        for ($i = 1; $i <= 25; $i++) {
            $persona = Persona::factory()->create([
                'documento_id' => Documento::factory(),
                'razon_social' => "Cliente Numero {$i}",
                'numero_documento' => str_pad((string) $i, 8, '0', STR_PAD_LEFT),
                'estado' => 1,
            ]);
            Cliente::factory()->create(['persona_id' => $persona->id]);
        }

        $component = Livewire::actingAs($this->user)
            ->test(ClienteSelect::class);

        $this->assertLessThanOrEqual(20, count($component->get('results')));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function usuario_no_autenticado_recibe_401(): void
    {
        // abort_unless(auth()->check(), 401) retorna 401 (no AuthenticationException)
        Livewire::test(ClienteSelect::class)
            ->assertStatus(401);
    }
}
