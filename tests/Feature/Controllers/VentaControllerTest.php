<?php

namespace Tests\Feature\Controllers;

use App\Exceptions\VentaException;
use App\Models\Cliente;
use App\Models\Comprobante;
use App\Models\Documento;
use App\Models\Persona;
use App\Models\User;
use App\Models\Venta;
use App\Repositories\ProductoRepository;
use App\Repositories\VentaRepository;
use App\Services\VentaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class VentaControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Cliente $cliente;
    protected Comprobante $comprobante;
    protected VentaService $ventaServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            \Illuminate\Auth\Middleware\Authenticate::class,
            \Illuminate\Auth\Middleware\Authorize::class,
            \Spatie\Permission\Middleware\PermissionMiddleware::class,
            \Spatie\Permission\Middleware\RoleMiddleware::class,
            \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        $this->user = User::factory()->create(['name' => 'Tester']);
        $this->actingAs($this->user);

        $documento = Documento::factory()->create();
        $persona = Persona::factory()->create([
            'documento_id' => $documento->id,
            'estado' => 1,
            'tipo_persona' => 'Cliente',
        ]);

        $this->cliente = Cliente::factory()->create([
            'persona_id' => $persona->id,
        ]);

        $this->comprobante = Comprobante::factory()->create();

        $this->ventaServiceMock = Mockery::mock(VentaService::class)->shouldIgnoreMissing();
        $productoRepoMock = Mockery::mock(ProductoRepository::class)->shouldIgnoreMissing();
        $ventaRepoMock = Mockery::mock(VentaRepository::class)->shouldIgnoreMissing();

        $this->app->instance(VentaService::class, $this->ventaServiceMock);
        $this->app->instance(ProductoRepository::class, $productoRepoMock);
        $this->app->instance(VentaRepository::class, $ventaRepoMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function store_cuando_servicio_exitoso_redirige_a_index_con_mensaje()
    {
        $venta = Venta::factory()->make([
            'numero_comprobante' => 'B001-0042',
        ]);

        $this->ventaServiceMock
            ->shouldReceive('procesarVenta')
            ->once()
            ->andReturn($venta);

        $response = $this->post(route('ventas.store'), [
            'impuesto' => 18,
            'total' => 120,
            'cliente_id' => $this->cliente->id,
            'comprobante_id' => $this->comprobante->id,
            'medio_pago' => 'efectivo',
            'efectivo' => 120,
        ]);

        $response->assertRedirect(route('ventas.index'));
        $response->assertSessionHas('success', 'Venta #B001-0042 realizada exitosamente');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function store_cuando_servicio_lanza_venta_exception_redirige_con_error()
    {
        $this->ventaServiceMock
            ->shouldReceive('procesarVenta')
            ->once()
            ->andThrow(new VentaException('No se pudo procesar la venta'));

        $response = $this->post(route('ventas.store'), [
            'impuesto' => 18,
            'total' => 90,
            'cliente_id' => $this->cliente->id,
            'comprobante_id' => $this->comprobante->id,
            'medio_pago' => 'efectivo',
            'efectivo' => 90,
        ]);

        $response->assertRedirect(route('ventas.create'));
        $response->assertSessionHas('error', 'No se pudo procesar la venta');
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

        $this->ventaServiceMock
            ->shouldReceive('anularVenta')
            ->once()
            ->withArgs(function (Venta $ventaRecibida, string $motivo) use ($venta) {
                return $ventaRecibida->id === $venta->id
                    && $motivo === 'Anulada por usuario Tester';
            })
            ->andReturnNull();

        $response = $this->delete(route('ventas.destroy', $venta));

        $response->assertRedirect(route('ventas.index'));
        $response->assertSessionHas('success', 'Venta anulada correctamente. Stock y fidelización revertidos.');
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
}
