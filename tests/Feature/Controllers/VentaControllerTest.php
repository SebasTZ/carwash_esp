<?php

namespace Tests\Feature\Controllers;

use App\Models\Cliente;
use App\Models\Comprobante;
use App\Models\Documento;
use App\Models\Persona;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class VentaControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Cliente $cliente;
    protected Comprobante $comprobante;
    protected Producto $producto;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ([
            'ver-venta',
            'mostrar-venta',
            'crear-venta',
            'eliminar-venta',
            'reporte-diario-venta',
            'reporte-semanal-venta',
            'reporte-mensual-venta',
            'reporte-personalizado-venta',
            'exportar-reporte-venta',
        ] as $perm) {
            Permission::findOrCreate($perm);
        }

        $this->user = User::factory()->create(['name' => 'Tester']);
        $this->user->givePermissionTo([
            'ver-venta',
            'mostrar-venta',
            'crear-venta',
            'eliminar-venta',
            'reporte-diario-venta',
            'reporte-semanal-venta',
            'reporte-mensual-venta',
            'reporte-personalizado-venta',
            'exportar-reporte-venta',
        ]);

        $this->actingAs($this->user);

        $documento = Documento::factory()->create();
        $persona = Persona::factory()->create([
            'documento_id' => $documento->id,
            'estado' => 1,
            'tipo_persona' => 'Cliente',
        ]);

        $this->cliente = Cliente::factory()->create([
            'persona_id' => $persona->id,
            'lavados_acumulados' => 0,
        ]);

        $this->comprobante = Comprobante::factory()->create([
            'serie' => 'B001-',
        ]);

        $this->producto = Producto::factory()->create([
            'stock' => 30,
            'precio_venta' => 60,
            'es_servicio_lavado' => false,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function store_cuando_servicio_exitoso_redirige_a_index_con_mensaje()
    {
        $response = $this->post(route('ventas.store'), [
            'impuesto' => 18,
            'total' => 120,
            'cliente_id' => $this->cliente->id,
            'comprobante_id' => $this->comprobante->id,
            'medio_pago' => 'efectivo',
            'efectivo' => 120,
            'arrayidproducto' => [$this->producto->id],
            'arraycantidad' => [2],
            'arrayprecioventa' => [60],
            'arraydescuento' => [0],
        ]);

        $response->assertRedirect(route('ventas.index'));
        $response->assertSessionHas('success', function (string $message): bool {
            return str_starts_with($message, 'Venta #B001-')
                && str_ends_with($message, ' realizada exitosamente');
        });

        $this->assertDatabaseHas('ventas', [
            'cliente_id' => $this->cliente->id,
            'comprobante_id' => $this->comprobante->id,
            'medio_pago' => 'efectivo',
            'estado' => 1,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function store_cuando_servicio_lanza_venta_exception_redirige_con_error()
    {
        $response = $this->post(route('ventas.store'), [
            'impuesto' => 18,
            'total' => 90,
            'cliente_id' => $this->cliente->id,
            'comprobante_id' => $this->comprobante->id,
            'medio_pago' => 'lavado_gratis',
            'arrayidproducto' => [$this->producto->id],
            'arraycantidad' => [1],
            'arrayprecioventa' => [90],
            'arraydescuento' => [0],
        ]);

        $response->assertRedirect(route('ventas.create'));
        $response->assertSessionHas('error', 'El cliente no tiene suficientes lavados acumulados para un lavado gratuito');

        $this->assertDatabaseCount('ventas', 0);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function destroy_invoca_anulacion_con_motivo_y_retorna_success()
    {
        $venta = Venta::factory()->create([
            'cliente_id' => $this->cliente->id,
            'user_id' => $this->user->id,
            'comprobante_id' => $this->comprobante->id,
            'estado' => 1,
        ]);

        $response = $this->delete(route('ventas.destroy', $venta));

        $response->assertRedirect(route('ventas.index'));
        $response->assertSessionHas('success', 'Venta anulada correctamente. Stock y fidelización revertidos.');

        $this->assertDatabaseHas('ventas', [
            'id' => $venta->id,
            'estado' => 0,
        ]);

        $venta->refresh();
        $this->assertStringContainsString('Anulada por usuario Tester', (string) $venta->comentarios);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function show_retorna_403_si_intenta_ver_venta_de_otro_usuario()
    {
        $duenioVenta = User::factory()->create();
        $duenioVenta->givePermissionTo(['ver-venta', 'mostrar-venta']);

        $usuarioSinPropiedad = User::factory()->create();
        $usuarioSinPropiedad->givePermissionTo(['ver-venta', 'mostrar-venta']);

        $venta = Venta::factory()->create([
            'cliente_id' => $this->cliente->id,
            'comprobante_id' => $this->comprobante->id,
            'user_id' => $duenioVenta->id,
            'estado' => 1,
        ]);

        $response = $this->actingAs($usuarioSinPropiedad)->get(route('ventas.show', $venta));

        $response->assertForbidden();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function destroy_retorna_403_si_intenta_anular_venta_de_otro_usuario()
    {
        $venta = Venta::factory()->create([
            'cliente_id' => $this->cliente->id,
            'comprobante_id' => $this->comprobante->id,
            'user_id' => $this->user->id,
            'estado' => 1,
        ]);

        $usuarioSinPropiedad = User::factory()->create();
        $usuarioSinPropiedad->givePermissionTo('eliminar-venta');

        $response = $this->actingAs($usuarioSinPropiedad)->delete(route('ventas.destroy', $venta));

        $response->assertForbidden();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function validar_fidelizacion_informa_lavados_faltantes_y_disponibles()
    {
        $clienteSinSaldo = Cliente::factory()->create([
            'persona_id' => Persona::factory()->create(['documento_id' => Documento::factory()])->id,
            'lavados_acumulados' => 7,
        ]);

        $clienteConSaldo = Cliente::factory()->create([
            'persona_id' => Persona::factory()->create(['documento_id' => Documento::factory()])->id,
            'lavados_acumulados' => 21,
        ]);

        $responseInvalido = $this->get(route('validar.fidelizacion', $clienteSinSaldo->id));
        $responseInvalido->assertOk();
        $responseInvalido->assertJson([
            'valido' => false,
            'lavados_actuales' => 7,
            'lavados_faltantes' => 3,
        ]);

        $responseValido = $this->get(route('validar.fidelizacion', $clienteConSaldo->id));
        $responseValido->assertOk();
        $responseValido->assertJson([
            'valido' => true,
            'lavados_actuales' => 21,
            'lavados_disponibles' => 2,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function create_incluye_configuracion_de_endpoint_para_validar_fidelizacion()
    {
        Permission::findOrCreate('ver-cliente');
        $this->user->givePermissionTo('ver-cliente');

        $response = $this->get(route('ventas.create'));

        $response->assertOk();
        $response->assertSee('id="venta-endpoints-config"', false);
        $response->assertSee('validarFidelizacionUrl', false);
        $response->assertSee('__cliente_id__', false);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function reporte_personalizado_sin_parametros_retorna_vista_sin_error()
    {
        $response = $this->get(route('ventas.reporte.personalizado'));

        $response->assertOk();
        $response->assertViewIs('venta.reporte');
        $response->assertViewHas('reporte', 'personalizado');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function reporte_diario_retorna_vista_sin_error()
    {
        $response = $this->get(route('ventas.reporte.diario'));

        $response->assertOk();
        $response->assertViewIs('venta.reporte');
        $response->assertViewHas('reporte', 'diario');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function reporte_semanal_retorna_vista_sin_error()
    {
        $response = $this->get(route('ventas.reporte.semanal'));

        $response->assertOk();
        $response->assertViewIs('venta.reporte');
        $response->assertViewHas('reporte', 'semanal');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function reporte_mensual_retorna_vista_sin_error()
    {
        $response = $this->get(route('ventas.reporte.mensual'));

        $response->assertOk();
        $response->assertViewIs('venta.reporte');
        $response->assertViewHas('reporte', 'mensual');
    }
}
