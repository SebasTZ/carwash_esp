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
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ReportePersonalizadoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::findOrCreate('reporte-personalizado-venta');
        Permission::findOrCreate('ver-venta');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function filtra_por_rango_y_excluye_medios_no_permitidos(): void
    {
        $comprobante = Comprobante::factory()->create();

        // Usuario con todos los permisos de ventas para ver todas
        $adminUser = User::factory()->create();
        $adminUser->givePermissionTo(['reporte-personalizado-venta', 'ver-venta']);

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
            'user_id' => $adminUser->id,
            'comprobante_id' => $comprobante->id,
            'fecha_hora' => '2026-04-10 10:00:00',
            'total' => 120.00,
            'medio_pago' => 'efectivo',
            'estado' => 1,
        ]);

        Venta::factory()->create([
            'cliente_id' => $clienteExcluido->id,
            'user_id' => $adminUser->id,
            'comprobante_id' => $comprobante->id,
            'fecha_hora' => '2026-04-11 10:00:00',
            'total' => 300.00,
            'medio_pago' => 'tarjeta_regalo',
            'estado' => 1,
        ]);

        Livewire::actingAs($adminUser)
            ->test(ReportePersonalizado::class)
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
        $user = User::factory()->create();
        $user->givePermissionTo(['reporte-personalizado-venta', 'ver-venta']);

        Livewire::actingAs($user)
            ->test(ReportePersonalizado::class)
            ->set('fechaInicio', '2026-04-30')
            ->set('fechaFin', '2026-04-01')
            ->call('filtrar')
            ->assertHasErrors(['fechaInicio' => 'before_or_equal']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function aplica_busqueda_reactiva_sobre_resultados_filtrados(): void
    {
        $comprobante = Comprobante::factory()->create();
        $adminUser = User::factory()->create();
        $adminUser->givePermissionTo(['reporte-personalizado-venta', 'ver-venta']);

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
            'user_id' => $adminUser->id,
            'comprobante_id' => $comprobante->id,
            'fecha_hora' => '2026-04-15 09:00:00',
            'total' => 90.00,
            'medio_pago' => 'efectivo',
            'estado' => 1,
        ]);

        Venta::factory()->create([
            'cliente_id' => $clienteB->id,
            'user_id' => $adminUser->id,
            'comprobante_id' => $comprobante->id,
            'fecha_hora' => '2026-04-16 09:00:00',
            'total' => 80.00,
            'medio_pago' => 'tarjeta_credito',
            'estado' => 1,
        ]);

        Livewire::actingAs($adminUser)
            ->test(ReportePersonalizado::class)
            ->set('fechaInicio', '2026-04-01')
            ->set('fechaFin', '2026-04-30')
            ->call('filtrar')
            ->set('search', 'Carlos')
            ->assertSee('Carlos Repetido')
            ->assertDontSee('Maria Final');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_usuario_sin_permiso_recibe_403(): void
    {
        // Usuario sin el permiso 'reporte-personalizado-venta'
        $userSinPermiso = User::factory()->create();

        Livewire::actingAs($userSinPermiso)
            ->test(ReportePersonalizado::class)
            ->assertForbidden();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_reset_filtros_limpia_estado(): void
    {
        $comprobante = Comprobante::factory()->create();
        $user = User::factory()->create();
        $user->givePermissionTo(['reporte-personalizado-venta', 'ver-venta']);

        $persona = Persona::factory()->create([
            'documento_id' => Documento::factory(),
            'razon_social' => 'Cliente Reset',
            'estado' => 1,
        ]);
        $cliente = Cliente::factory()->create(['persona_id' => $persona->id]);

        Venta::factory()->create([
            'cliente_id' => $cliente->id,
            'user_id' => $user->id,
            'comprobante_id' => $comprobante->id,
            'fecha_hora' => '2026-04-10 10:00:00',
            'total' => 50.00,
            'medio_pago' => 'efectivo',
            'estado' => 1,
        ]);

        Livewire::actingAs($user)
            ->test(ReportePersonalizado::class)
            ->set('fechaInicio', '2026-04-01')
            ->set('fechaFin', '2026-04-30')
            ->call('filtrar')
            ->assertSet('ventas', fn ($v) => is_array($v) && count($v) > 0)
            ->set('search', 'texto de busqueda')
            ->call('resetFiltros')
            ->assertSet('fechaInicio', '')
            ->assertSet('fechaFin', '')
            ->assertSet('search', '')
            ->assertSet('ventas', []);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_vendedor_solo_ve_sus_propias_ventas(): void
    {
        $comprobante = Comprobante::factory()->create();

        // Vendedor: tiene reporte pero NO tiene 'ver-venta' (ver todas las ventas)
        $vendedor = User::factory()->create(['name' => 'Vendedor Propio']);
        $vendedor->givePermissionTo('reporte-personalizado-venta');

        // Otro usuario (administrador)
        $otroUsuario = User::factory()->create(['name' => 'Otro Usuario']);
        $otroUsuario->givePermissionTo(['reporte-personalizado-venta', 'ver-venta']);

        $personaVendedor = Persona::factory()->create([
            'documento_id' => Documento::factory(),
            'razon_social' => 'Cliente Del Vendedor',
            'estado' => 1,
        ]);
        $clienteVendedor = Cliente::factory()->create(['persona_id' => $personaVendedor->id]);

        $personaOtro = Persona::factory()->create([
            'documento_id' => Documento::factory(),
            'razon_social' => 'Cliente De Otro',
            'estado' => 1,
        ]);
        $clienteOtro = Cliente::factory()->create(['persona_id' => $personaOtro->id]);

        // Venta del vendedor
        Venta::factory()->create([
            'cliente_id' => $clienteVendedor->id,
            'user_id' => $vendedor->id,
            'comprobante_id' => $comprobante->id,
            'fecha_hora' => '2026-04-10 10:00:00',
            'total' => 75.00,
            'medio_pago' => 'efectivo',
            'estado' => 1,
        ]);

        // Venta de otro usuario
        Venta::factory()->create([
            'cliente_id' => $clienteOtro->id,
            'user_id' => $otroUsuario->id,
            'comprobante_id' => $comprobante->id,
            'fecha_hora' => '2026-04-11 10:00:00',
            'total' => 200.00,
            'medio_pago' => 'efectivo',
            'estado' => 1,
        ]);

        // El vendedor solo debe ver sus propias ventas
        Livewire::actingAs($vendedor)
            ->test(ReportePersonalizado::class)
            ->set('fechaInicio', '2026-04-01')
            ->set('fechaFin', '2026-04-30')
            ->call('filtrar')
            ->assertSet('ventas', function ($ventas) use ($vendedor) {
                // Solo una venta, la del vendedor
                return is_array($ventas)
                    && count($ventas) === 1
                    && ($ventas[0]['vendedor']['name'] ?? null) === $vendedor->name;
            })
            ->assertSee('Cliente Del Vendedor')
            ->assertDontSee('Cliente De Otro');
    }
}
