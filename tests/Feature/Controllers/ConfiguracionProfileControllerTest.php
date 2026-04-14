<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\ConfiguracionNegocio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class ConfiguracionProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role = Role::create(['name' => 'administrador']);
        $permisos = ['ver-configuracion', 'editar-configuracion', 'ver-perfil', 'editar-perfil'];
        foreach ($permisos as $p) {
            Permission::create(['name' => $p]);
        }
        $role->givePermissionTo($permisos);

        $this->admin = User::factory()->create(['email' => 'admin@test.com']);
        $this->admin->assignRole('administrador');
    }

    // ── CONFIGURACIÓN NEGOCIO ────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function configuracion_edit_devuelve_vista()
    {
        $response = $this->actingAs($this->admin)->get(route('configuracion.edit'));
        $response->assertStatus(200);
        $response->assertViewIs('configuracion.edit');
        $response->assertViewHas('configuracion');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function configuracion_edit_crea_registro_si_no_existe()
    {
        $this->assertEquals(0, ConfiguracionNegocio::count());

        $this->actingAs($this->admin)->get(route('configuracion.edit'));

        $this->assertEquals(1, ConfiguracionNegocio::count());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function configuracion_update_guarda_datos()
    {
        ConfiguracionNegocio::create([
            'nombre_negocio' => 'Empresa Vieja',
            'direccion'      => 'Calle 1',
            'telefono'       => '000000000',
        ]);

        $response = $this->actingAs($this->admin)->put(route('configuracion.update'), [
            'nombre_negocio' => 'CarWash Pro',
            'direccion'      => 'Av. Principal 123',
            'telefono'       => '987654321',
        ]);

        $response->assertRedirect(route('configuracion.edit'));
        $this->assertDatabaseHas('configuracion_negocios', ['nombre_negocio' => 'CarWash Pro']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function configuracion_update_falla_sin_nombre_negocio()
    {
        ConfiguracionNegocio::create([
            'nombre_negocio' => 'Empresa',
            'direccion'      => 'Calle 1',
            'telefono'       => '000000000',
        ]);

        $response = $this->actingAs($this->admin)->put(route('configuracion.update'), [
            'nombre_negocio' => '',
            'direccion'      => 'Av. Principal 123',
            'telefono'       => '987654321',
        ]);

        $response->assertSessionHasErrors('nombre_negocio');
    }

    // ── PERFIL ───────────────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function profile_index_devuelve_vista_con_usuario_autenticado()
    {
        $response = $this->actingAs($this->admin)->get(route('profile.index'));
        $response->assertStatus(200);
        $response->assertViewIs('profile.index');
        $response->assertViewHas('user');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function profile_update_modifica_nombre_y_email()
    {
        $response = $this->actingAs($this->admin)->put(route('profile.update', $this->admin), [
            'name'     => 'Nuevo Nombre',
            'email'    => 'nuevo@test.com',
            'password' => '',
        ]);

        $response->assertRedirect(route('profile.index'));
        $this->assertDatabaseHas('users', ['id' => $this->admin->id, 'name' => 'Nuevo Nombre']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function profile_update_cambia_password_cuando_se_provee()
    {
        $response = $this->actingAs($this->admin)->put(route('profile.update', $this->admin), [
            'name'     => $this->admin->name,
            'email'    => $this->admin->email,
            'password' => 'nuevapassword123',
        ]);

        $response->assertRedirect(route('profile.index'));
        $this->assertTrue(
            \Illuminate\Support\Facades\Hash::check('nuevapassword123', $this->admin->fresh()->password)
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function profile_update_rechaza_password_menor_a_8_caracteres()
    {
        $response = $this->actingAs($this->admin)->put(route('profile.update', $this->admin), [
            'name'     => $this->admin->name,
            'email'    => $this->admin->email,
            'password' => '123',
        ]);

        $response->assertSessionHasErrors('password');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function profile_update_falla_con_email_de_otro_usuario()
    {
        User::factory()->create(['email' => 'ocupado@test.com']);

        $response = $this->actingAs($this->admin)->put(route('profile.update', $this->admin), [
            'name'     => $this->admin->name,
            'email'    => 'ocupado@test.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('email');
    }
}
