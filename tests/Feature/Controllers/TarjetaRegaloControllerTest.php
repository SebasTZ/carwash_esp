<?php

namespace Tests\Feature\Controllers;

use App\Models\Cliente;
use App\Models\Documento;
use App\Models\Persona;
use App\Models\TarjetaRegalo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TarjetaRegaloControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Cliente $cliente;

    protected function setUp(): void
    {
        parent::setUp();

        \Spatie\Permission\Models\Permission::create(['name' => 'ver-tarjeta-regalo']);
        \Spatie\Permission\Models\Permission::create(['name' => 'crear-tarjeta-regalo']);
        \Spatie\Permission\Models\Permission::create(['name' => 'editar-tarjeta-regalo']);
        \Spatie\Permission\Models\Permission::create(['name' => 'eliminar-tarjeta-regalo']);
        \Spatie\Permission\Models\Permission::create(['name' => 'reporte-tarjeta-regalo']);
        \Spatie\Permission\Models\Permission::create(['name' => 'historial-tarjeta-regalo']);
        \Spatie\Permission\Models\Permission::create(['name' => 'exportar-tarjeta-regalo']);

        $role = \Spatie\Permission\Models\Role::create(['name' => 'admin-tarjeta-test']);
        $role->givePermissionTo(['ver-tarjeta-regalo','crear-tarjeta-regalo','editar-tarjeta-regalo','eliminar-tarjeta-regalo','reporte-tarjeta-regalo','historial-tarjeta-regalo','exportar-tarjeta-regalo']);

        $this->user = User::factory()->create();
        $this->user->assignRole('admin-tarjeta-test');
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
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function store_web_crea_tarjeta_y_redirige_a_reporte_view()
    {
        $response = $this->post(route('tarjetas_regalo.store'), [
            'codigo' => 'TG-001-TEST',
            'valor_inicial' => 150,
            'fecha_venta' => now()->toDateString(),
            'fecha_vencimiento' => now()->addMonths(6)->toDateString(),
            'cliente_id' => $this->cliente->id,
        ]);

        $response->assertRedirect(route('tarjetas_regalo.reporte.view'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tarjetas_regalo', [
            'codigo' => 'TG-001-TEST',
            'valor_inicial' => '150.00',
            'saldo_actual' => '150.00',
            'estado' => 'activa',
            'cliente_id' => $this->cliente->id,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function store_ajax_invalido_devuelve_422_con_errores()
    {
        $response = $this->post(route('tarjetas_regalo.store'), [
            'codigo' => '',
            'valor_inicial' => 0,
            'fecha_venta' => '',
        ], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function check_devuelve_datos_basicos_o_404_si_no_existe()
    {
        $tarjeta = TarjetaRegalo::create([
            'codigo' => 'TG-CHECK-01',
            'valor_inicial' => 100,
            'saldo_actual' => 40,
            'estado' => 'activa',
            'fecha_venta' => now()->toDateString(),
            'cliente_id' => $this->cliente->id,
        ]);

        $ok = $this->get('/tarjetas_regalo/check/' . $tarjeta->codigo);
        $ok->assertOk();
        $ok->assertJson([
            'estado' => 'activa',
            'saldo_actual' => 40,
        ]);

        $notFound = $this->get('/tarjetas_regalo/check/CODIGO-INEXISTENTE');
        $notFound->assertStatus(404);
        $notFound->assertJson(['error' => 'not_found']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function update_modifica_estado_y_datos_editables()
    {
        $tarjeta = TarjetaRegalo::create([
            'codigo' => 'TG-UPD-01',
            'valor_inicial' => 120,
            'saldo_actual' => 120,
            'estado' => 'activa',
            'fecha_venta' => now()->toDateString(),
            'cliente_id' => $this->cliente->id,
        ]);

        $response = $this->put(route('tarjetas_regalo.update', $tarjeta->id), [
            'valor_inicial' => 200,
            'fecha_venta' => now()->toDateString(),
            'fecha_vencimiento' => now()->addYear()->toDateString(),
            'cliente_id' => $this->cliente->id,
            'estado' => 'vencida',
        ]);

        $response->assertRedirect(route('tarjetas_regalo.reporte.view'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tarjetas_regalo', [
            'id' => $tarjeta->id,
            'valor_inicial' => '200.00',
            'estado' => 'vencida',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function index_retorna_403_si_usuario_no_tiene_permiso(): void
    {
        $sinPermisos = User::factory()->create();
        $this->actingAs($sinPermisos);
        $this->get(route('tarjetas_regalo.index'))->assertStatus(403);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function store_retorna_403_si_usuario_no_tiene_permiso(): void
    {
        $sinPermisos = User::factory()->create();
        $this->actingAs($sinPermisos);
        $this->post(route('tarjetas_regalo.store'), [])->assertStatus(403);
    }
}
