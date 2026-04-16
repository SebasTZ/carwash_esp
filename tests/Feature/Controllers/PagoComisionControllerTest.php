<?php

namespace Tests\Feature\Controllers;

use App\Models\ControlLavado;
use App\Models\Lavador;
use App\Models\PagoComision;
use App\Models\User;
use App\Services\ComisionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class PagoComisionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected ComisionService $comisionServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        \Spatie\Permission\Models\Permission::create(['name' => 'ver-pago-comision']);
        \Spatie\Permission\Models\Permission::create(['name' => 'crear-pago-comision']);
        \Spatie\Permission\Models\Permission::create(['name' => 'ver-historial-pago-comision']);

        $role = \Spatie\Permission\Models\Role::create(['name' => 'admin-pago-test']);
        $role->givePermissionTo(['ver-pago-comision','crear-pago-comision','ver-historial-pago-comision']);

        $this->user = User::factory()->create();
        $this->user->assignRole('admin-pago-test');
        $this->actingAs($this->user);

        $this->comisionServiceMock = Mockery::mock(ComisionService::class)->shouldIgnoreMissing();
        $this->app->instance(ComisionService::class, $this->comisionServiceMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function store_con_lavados_pendientes_crea_pago_de_comision()
    {
        $lavador = Lavador::factory()->create(['estado' => 'activo']);

        ControlLavado::factory()->create([
            'lavador_id' => $lavador->id,
            'hora_llegada' => now()->subDay()->setTime(10, 0),
        ]);

        $payload = [
            'lavador_id' => $lavador->id,
            'monto_pagado' => 120,
            'desde' => now()->subDays(2)->toDateString(),
            'hasta' => now()->toDateString(),
            'fecha_pago' => now()->toDateString(),
            'observacion' => 'Pago quincenal',
        ];

        $response = $this->post(route('pagos_comisiones.store'), $payload);

        $response->assertRedirect(route('pagos_comisiones.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pagos_comisiones', [
            'lavador_id' => $lavador->id,
            'monto_pagado' => '120.00',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function store_sin_lavados_pendientes_retorna_warning()
    {
        $lavador = Lavador::factory()->create(['estado' => 'activo']);

        $response = $this->from(route('pagos_comisiones.create'))->post(route('pagos_comisiones.store'), [
            'lavador_id' => $lavador->id,
            'monto_pagado' => 75,
            'desde' => now()->subDays(7)->toDateString(),
            'hasta' => now()->toDateString(),
            'fecha_pago' => now()->toDateString(),
            'observacion' => 'Sin pendientes',
        ]);

        $response->assertRedirect(route('pagos_comisiones.create'));
        $response->assertSessionHas('warning');
        $this->assertDatabaseCount('pagos_comisiones', 0);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function show_lavador_filtra_pagos_por_rango_de_fechas()
    {
        $lavador = Lavador::factory()->create();

        PagoComision::factory()->create([
            'lavador_id' => $lavador->id,
            'desde' => '2026-04-01',
            'hasta' => '2026-04-10',
            'fecha_pago' => '2026-04-10',
        ]);

        PagoComision::factory()->create([
            'lavador_id' => $lavador->id,
            'desde' => '2026-03-01',
            'hasta' => '2026-03-05',
            'fecha_pago' => '2026-03-05',
        ]);

        $response = $this->get(route('pagos_comisiones.lavador', [
            'lavador' => $lavador,
            'fecha_inicio' => '2026-04-01',
            'fecha_fin' => '2026-04-30',
        ]));

        $response->assertOk();
        $response->assertViewIs('pagos_comisiones.show');
        $response->assertViewHas('pagos', function ($pagos) {
            return $pagos->count() === 1;
        });
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function reporte_comisiones_usa_servicio_y_renderiza_vista()
    {
        $dataEsperada = [
            [
                'lavador' => Lavador::factory()->make(['nombre' => 'Juan']),
                'cantidad' => 4,
                'comision_total' => 80.0,
                'pagado' => 50.0,
                'saldo' => 30.0,
            ],
        ];

        $this->comisionServiceMock
            ->shouldReceive('generarReporteComisiones')
            ->once()
            ->with('2026-04-01', '2026-04-30')
            ->andReturn($dataEsperada);

        $response = $this->get(route('pagos_comisiones.reporte', [
            'fecha_inicio' => '2026-04-01',
            'fecha_fin' => '2026-04-30',
        ]));

        $response->assertOk();
        $response->assertViewIs('pagos_comisiones.reporte');
        $response->assertViewHas('data', $dataEsperada);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function index_retorna_403_si_usuario_no_tiene_permiso(): void
    {
        $sinPermisos = User::factory()->create();
        $this->actingAs($sinPermisos);
        $this->get(route('pagos_comisiones.index'))->assertStatus(403);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function store_retorna_403_si_usuario_no_tiene_permiso(): void
    {
        $sinPermisos = User::factory()->create();
        $this->actingAs($sinPermisos);
        $this->post(route('pagos_comisiones.store'), [])->assertStatus(403);
    }
}
