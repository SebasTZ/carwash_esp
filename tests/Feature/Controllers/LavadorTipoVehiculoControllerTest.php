<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Lavador;
use App\Models\TipoVehiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class LavadorTipoVehiculoControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role = Role::create(['name' => 'admin']);
        $permisos = [
            'ver-lavador', 'crear-lavador', 'editar-lavador', 'eliminar-lavador',
            'ver-tipo-vehiculo', 'crear-tipo-vehiculo', 'editar-tipo-vehiculo', 'eliminar-tipo-vehiculo',
        ];
        foreach ($permisos as $p) {
            Permission::create(['name' => $p]);
        }
        $role->givePermissionTo($permisos);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    // ── LAVADORES ────────────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function lavadores_index_devuelve_vista()
    {
        $response = $this->actingAs($this->admin)->get(route('lavadores.index'));
        $response->assertStatus(200);
        $response->assertViewIs('lavadores.index');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function lavadores_store_crea_lavador()
    {
        $response = $this->actingAs($this->admin)->post(route('lavadores.store'), [
            'nombre' => 'Carlos Mendoza',
            'dni'    => '45678901',
            'estado' => 'activo',
        ]);

        $response->assertRedirect(route('lavadores.index'));
        $this->assertDatabaseHas('lavadores', ['dni' => '45678901']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function lavadores_store_falla_con_dni_duplicado()
    {
        Lavador::factory()->create(['dni' => '11111111']);

        $response = $this->actingAs($this->admin)->post(route('lavadores.store'), [
            'nombre' => 'Otro lavador',
            'dni'    => '11111111',
            'estado' => 'activo',
        ]);

        $response->assertSessionHasErrors('dni');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function lavadores_store_falla_sin_nombre()
    {
        $response = $this->actingAs($this->admin)->post(route('lavadores.store'), [
            'nombre' => '',
            'dni'    => '99999999',
            'estado' => 'activo',
        ]);

        $response->assertSessionHasErrors('nombre');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function lavadores_update_actualiza_datos()
    {
        $lavador = Lavador::factory()->create();

        $response = $this->actingAs($this->admin)->put(route('lavadores.update', $lavador), [
            'nombre' => 'Nombre Actualizado',
            'dni'    => $lavador->dni,
            'estado' => 'activo',
        ]);

        $response->assertRedirect(route('lavadores.index'));
        $this->assertDatabaseHas('lavadores', ['id' => $lavador->id, 'nombre' => 'Nombre Actualizado']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function lavadores_destroy_marca_como_inactivo()
    {
        $lavador = Lavador::factory()->create(['estado' => 'activo']);

        $response = $this->actingAs($this->admin)->delete(route('lavadores.destroy', $lavador));

        $response->assertRedirect(route('lavadores.index'));
        $this->assertDatabaseHas('lavadores', ['id' => $lavador->id, 'estado' => 'inactivo']);
    }

    // ── TIPOS DE VEHÍCULO ────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function tipos_vehiculo_index_devuelve_vista()
    {
        $response = $this->actingAs($this->admin)->get(route('tipos_vehiculo.index'));
        $response->assertStatus(200);
        $response->assertViewIs('tipos_vehiculo.index');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function tipos_vehiculo_store_crea_tipo()
    {
        $response = $this->actingAs($this->admin)->post(route('tipos_vehiculo.store'), [
            'nombre'   => 'Camioneta',
            'comision' => 15.00,
            'estado'   => 'activo',
        ]);

        $response->assertRedirect(route('tipos_vehiculo.index'));
        $this->assertDatabaseHas('tipos_vehiculo', ['nombre' => 'Camioneta']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function tipos_vehiculo_store_falla_sin_nombre()
    {
        $response = $this->actingAs($this->admin)->post(route('tipos_vehiculo.store'), [
            'nombre'   => '',
            'comision' => 10.00,
            'estado'   => 'activo',
        ]);

        $response->assertSessionHasErrors('nombre');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function tipos_vehiculo_store_falla_con_comision_no_numerica()
    {
        $response = $this->actingAs($this->admin)->post(route('tipos_vehiculo.store'), [
            'nombre'   => 'Sedan',
            'comision' => 'abc',
            'estado'   => 'activo',
        ]);

        $response->assertSessionHasErrors('comision');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function tipos_vehiculo_update_actualiza_tipo()
    {
        $tipo = TipoVehiculo::factory()->create();

        $response = $this->actingAs($this->admin)->put(route('tipos_vehiculo.update', $tipo), [
            'nombre'   => 'SUV Actualizado',
            'comision' => 12.50,
            'estado'   => 'activo',
        ]);

        $response->assertRedirect(route('tipos_vehiculo.index'));
        $this->assertDatabaseHas('tipos_vehiculo', ['id' => $tipo->id, 'nombre' => 'SUV Actualizado']);
    }
}
