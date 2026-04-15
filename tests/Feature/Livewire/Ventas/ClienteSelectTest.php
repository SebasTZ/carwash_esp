<?php

namespace Tests\Feature\Livewire\Ventas;

use App\Livewire\Ventas\ClienteSelect;
use App\Models\Cliente;
use App\Models\Documento;
use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ClienteSelectTest extends TestCase
{
    use RefreshDatabase;

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

        Livewire::test(ClienteSelect::class)
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

        Livewire::test(ClienteSelect::class)
            ->call('selectOption', (string) $cliente->id)
            ->assertSet('selected', (string) $cliente->id)
            ->assertSet('selectedLabel', 'Ana Cliente - 45556667')
            ->assertDispatched('venta-select-updated');
    }
}
