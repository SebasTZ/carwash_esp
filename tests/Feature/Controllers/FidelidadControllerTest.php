<?php

namespace Tests\Feature\Controllers;

use App\Models\Cliente;
use App\Models\Fidelizacion;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class FidelidadControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Cliente $cliente;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::create(['name' => 'gestionar-fidelidad']);
        Permission::create(['name' => 'reporte-fidelidad']);
        Permission::create(['name' => 'exportar-fidelidad']);

        $role = Role::create(['name' => 'admin-fidelidad-test']);
        $role->givePermissionTo([
            'gestionar-fidelidad',
            'reporte-fidelidad',
            'exportar-fidelidad',
        ]);

        $this->user = User::factory()->create();
        $this->user->assignRole($role);
        $this->actingAs($this->user);

        $this->cliente = Cliente::factory()->create([
            'lavados_acumulados' => 4,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function mostrar_lavados_devuelve_lavados_acumulados_del_cliente(): void
    {
        $response = $this->get('/clientes/' . $this->cliente->id . '/lavados-acumulados');

        $response->assertOk();
        $response->assertJson([
            'lavados_acumulados' => 4,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function incrementar_lavado_incrementa_el_contador_del_cliente(): void
    {
        $response = $this->post('/clientes/' . $this->cliente->id . '/incrementar-lavado');

        $response->assertOk();
        $response->assertJson([
            'lavados_acumulados' => 5,
        ]);

        $this->assertDatabaseHas('clientes', [
            'id' => $this->cliente->id,
            'lavados_acumulados' => 5,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function aplicar_lavado_gratis_retorna_false_cuando_no_tiene_acumulados_suficientes(): void
    {
        $response = $this->post('/clientes/' . $this->cliente->id . '/lavado-gratis');

        $response->assertOk();
        $response->assertJson([
            'lavado_gratis' => false,
            'lavados_acumulados' => 4,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function aplicar_lavado_gratis_retorna_true_cuando_el_servicio_permite_canjear(): void
    {
        $cliente = Cliente::factory()->create(['lavados_acumulados' => 10]);

        $response = $this->post('/clientes/' . $cliente->id . '/lavado-gratis');

        $response->assertOk();
        $response->assertJson([
            'lavado_gratis' => true,
            'lavados_acumulados' => 0,
        ]);

        $this->assertDatabaseHas('clientes', [
            'id' => $cliente->id,
            'lavados_acumulados' => 0,
        ]);

        $this->assertDatabaseHas('fidelizacion', [
            'cliente_id' => $cliente->id,
            'lavados_acumulados' => 10,
            'tipo' => 'lavado_gratis',
        ]);

        $registro = Fidelizacion::where('cliente_id', $cliente->id)->first();
        $this->assertNotNull($registro?->fecha_canje);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function reporte_fidelidad_devuelve_clientes_y_lavados_gratis(): void
    {
        Venta::factory()->create([
            'cliente_id' => $this->cliente->id,
            'lavado_gratis' => true,
        ]);

        $response = $this->get(route('fidelidad.reporte'));

        $response->assertOk();
        $response->assertJsonStructure([
            'clientes_frecuentes',
            'lavados_gratis',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function reporte_view_renderiza_vista_de_fidelidad(): void
    {
        $response = $this->get(route('fidelidad.reporte.view'));

        $response->assertOk();
        $response->assertViewIs('fidelidad.reporte');
        $response->assertViewHas('clientes_frecuentes');
        $response->assertViewHas('lavados_gratis');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function export_excel_descarga_archivo_de_reporte(): void
    {
        Excel::fake();

        $response = $this->get(route('fidelidad.export.excel'));

        $response->assertOk();
        Excel::assertDownloaded('reporte_fidelidad.xlsx');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function mostrar_lavados_retorna_403_si_usuario_no_tiene_permiso(): void
    {
        $sinPermisos = User::factory()->create();
        $this->actingAs($sinPermisos);

        $this->get('/clientes/' . $this->cliente->id . '/lavados-acumulados')->assertStatus(403);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function reporte_retorna_403_si_usuario_no_tiene_permiso(): void
    {
        $sinPermisos = User::factory()->create();
        $this->actingAs($sinPermisos);

        $this->get(route('fidelidad.reporte'))->assertStatus(403);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function export_retorna_403_si_usuario_no_tiene_permiso(): void
    {
        $sinPermisos = User::factory()->create();
        $this->actingAs($sinPermisos);

        $this->get(route('fidelidad.export.excel'))->assertStatus(403);
    }
}
