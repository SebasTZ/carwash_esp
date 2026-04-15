<?php

namespace Tests\Feature\Livewire\Ventas;

use App\Livewire\Ventas\ReportePersonalizado;
use App\Models\Cliente;
use App\Models\Comprobante;
use App\Models\Documento;
use App\Models\Persona;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportePersonalizadoTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function filtra_por_rango_y_excluye_medios_no_permitidos(): void
    {
        $comprobante = Comprobante::factory()->create();
        $user = User::factory()->create();

        $personaVisible = Persona::factory()->create([
            'documento_id' => Documento::factory(),
            'razon_social' => 'Cliente Visible',
            'estado' => 1,
        ]);
        $clienteVisible = Cliente::factory()->create(['persona_id' => $personaVisible->id]);

        $personaExcluida = Persona::factory()->create([
            'documento_id' => Documento::factory(),
            'razon_social' => 'Cliente Excluido',
            'estado' => 1,
        ]);
        $clienteExcluido = Cliente::factory()->create(['persona_id' => $personaExcluida->id]);

        Venta::factory()->create([
            'cliente_id' => $clienteVisible->id,
            'user_id' => $user->id,
            'comprobante_id' => $comprobante->id,
            'fecha_hora' => '2026-04-10 10:00:00',
            'total' => 120.00,
            'medio_pago' => 'efectivo',
            'estado' => 1,
        ]);

        Venta::factory()->create([
            'cliente_id' => $clienteExcluido->id,
            'user_id' => $user->id,
            'comprobante_id' => $comprobante->id,
            'fecha_hora' => '2026-04-11 10:00:00',
            'total' => 300.00,
            'medio_pago' => 'tarjeta_regalo',
            'estado' => 1,
        ]);

        Livewire::test(ReportePersonalizado::class)
            ->set('fechaInicio', '2026-04-01')
            ->set('fechaFin', '2026-04-30')
            ->call('filtrar')
            ->assertSet('ventas', function ($ventas) {
                return is_array($ventas)
                    && count($ventas) === 1
                    && ($ventas[0]['medio_pago'] ?? null) === 'efectivo';
            })
            ->assertSee('Cliente Visible')
            ->assertDontSee('Cliente Excluido')
            ->assertSee('S/ 120.00');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function valida_rango_de_fechas_en_filtro(): void
    {
        Livewire::test(ReportePersonalizado::class)
            ->set('fechaInicio', '2026-04-30')
            ->set('fechaFin', '2026-04-01')
            ->call('filtrar')
            ->assertHasErrors(['fechaInicio' => 'before_or_equal']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function aplica_busqueda_reactiva_sobre_resultados_filtrados(): void
    {
        $comprobante = Comprobante::factory()->create();
        $user = User::factory()->create();

        $personaA = Persona::factory()->create([
            'documento_id' => Documento::factory(),
            'razon_social' => 'Carlos Repetido',
            'estado' => 1,
        ]);
        $clienteA = Cliente::factory()->create(['persona_id' => $personaA->id]);

        $personaB = Persona::factory()->create([
            'documento_id' => Documento::factory(),
            'razon_social' => 'Maria Final',
            'estado' => 1,
        ]);
        $clienteB = Cliente::factory()->create(['persona_id' => $personaB->id]);

        Venta::factory()->create([
            'cliente_id' => $clienteA->id,
            'user_id' => $user->id,
            'comprobante_id' => $comprobante->id,
            'fecha_hora' => '2026-04-15 09:00:00',
            'total' => 90.00,
            'medio_pago' => 'efectivo',
            'estado' => 1,
        ]);

        Venta::factory()->create([
            'cliente_id' => $clienteB->id,
            'user_id' => $user->id,
            'comprobante_id' => $comprobante->id,
            'fecha_hora' => '2026-04-16 09:00:00',
            'total' => 80.00,
            'medio_pago' => 'tarjeta_credito',
            'estado' => 1,
        ]);

        Livewire::test(ReportePersonalizado::class)
            ->set('fechaInicio', '2026-04-01')
            ->set('fechaFin', '2026-04-30')
            ->call('filtrar')
            ->set('search', 'Carlos')
            ->assertSee('Carlos Repetido')
            ->assertDontSee('Maria Final');
    }
}
