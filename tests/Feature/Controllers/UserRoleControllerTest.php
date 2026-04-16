<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class UserRoleControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role = Role::create(['name' => 'admin']);
        $permisos = ['ver-user', 'crear-user', 'editar-user', 'eliminar-user',
                     'ver-role', 'crear-role', 'editar-role', 'eliminar-role'];
        foreach ($permisos as $p) {
            Permission::create(['name' => $p]);
        }
        $role->givePermissionTo($permisos);

        $this->admin = User::factory()->create(['estado' => 1]);
        $this->admin->assignRole('admin');
    }

    // ── USERS ────────────────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function users_index_devuelve_vista_con_usuarios()
    {
        $response = $this->actingAs($this->admin)->get(route('users.index'));
        $response->assertStatus(200);
        $response->assertViewIs('user.index');
        $response->assertViewHas('users');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function users_index_retorna_403_si_no_tiene_permiso()
    {
        $usuarioSinPermiso = User::factory()->create(['estado' => 1]);

        $response = $this->actingAs($usuarioSinPermiso)->get(route('users.index'));

        $response->assertForbidden();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function users_store_crea_usuario_y_asigna_rol()
    {
        Role::create(['name' => 'cajero']);

        $response = $this->actingAs($this->admin)->post(route('users.store'), [
            'name'             => 'Juan Pérez',
            'email'            => 'juan@test.com',
            'password'         => 'password123',
            'password_confirm' => 'password123',
            'estado'           => 1,
            'role'             => 'cajero',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['email' => 'juan@test.com']);
        $user = User::where('email', 'juan@test.com')->first();
        $this->assertTrue($user->hasRole('cajero'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function users_store_falla_con_email_duplicado()
    {
        User::factory()->create(['email' => 'existe@test.com']);
        Role::create(['name' => 'cajero']);

        $response = $this->actingAs($this->admin)->post(route('users.store'), [
            'name'             => 'Otro',
            'email'            => 'existe@test.com',
            'password'         => 'password123',
            'password_confirm' => 'password123',
            'estado'           => 1,
            'role'             => 'cajero',
        ]);

        $response->assertSessionHasErrors('email');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function users_update_modifica_datos_del_usuario()
    {
        $user = User::factory()->create(['email' => 'original@test.com', 'estado' => 1]);
        $user->assignRole('admin');

        $response = $this->actingAs($this->admin)->put(route('users.update', $user), [
            'name'             => 'Nombre Nuevo',
            'email'            => 'nuevo@test.com',
            'password'         => '',
            'password_confirm' => '',
            'estado'           => 1,
            'role'             => 'admin',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['email' => 'nuevo@test.com', 'name' => 'Nombre Nuevo']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function users_update_rechaza_password_menor_a_8_caracteres()
    {
        $user = User::factory()->create(['estado' => 1]);
        $user->assignRole('admin');

        $response = $this->actingAs($this->admin)->put(route('users.update', $user), [
            'name'             => 'Test',
            'email'            => $user->email,
            'password'         => '123',
            'password_confirm' => '123',
            'estado'           => 1,
            'role'             => 'admin',
        ]);

        $response->assertSessionHasErrors('password');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function users_destroy_elimina_usuario()
    {
        $user = User::factory()->create(['estado' => 1]);
        $user->assignRole('admin');

        $response = $this->actingAs($this->admin)->delete(route('users.destroy', $user));

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    // ── ROLES ────────────────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function roles_index_devuelve_vista_con_roles()
    {
        $response = $this->actingAs($this->admin)->get(route('roles.index'));
        $response->assertStatus(200);
        $response->assertViewIs('role.index');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function roles_index_retorna_403_si_no_tiene_permiso()
    {
        $usuarioSinPermiso = User::factory()->create(['estado' => 1]);

        $response = $this->actingAs($usuarioSinPermiso)->get(route('roles.index'));

        $response->assertForbidden();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function roles_store_crea_rol_con_permisos()
    {
        $permiso = Permission::create(['name' => 'nuevo-permiso']);

        $response = $this->actingAs($this->admin)->post(route('roles.store'), [
            'name'       => 'nuevo-rol',
            'permission' => ['nuevo-permiso'],
        ]);

        $response->assertRedirect(route('roles.index'));
        $this->assertDatabaseHas('roles', ['name' => 'nuevo-rol']);
        $rol = Role::findByName('nuevo-rol');
        $this->assertTrue($rol->hasPermissionTo('nuevo-permiso'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function roles_store_falla_sin_nombre()
    {
        $response = $this->actingAs($this->admin)->post(route('roles.store'), [
            'name'       => '',
            'permission' => ['ver-user'],
        ]);

        $response->assertSessionHasErrors('name');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function roles_update_actualiza_nombre_y_permisos()
    {
        $rol = Role::create(['name' => 'rol-viejo']);
        $permiso = Permission::create(['name' => 'permiso-nuevo']);

        $response = $this->actingAs($this->admin)->put(route('roles.update', $rol), [
            'name'       => 'rol-actualizado',
            'permission' => ['permiso-nuevo'],
        ]);

        $response->assertRedirect(route('roles.index'));
        $this->assertDatabaseHas('roles', ['name' => 'rol-actualizado']);
        $rol->refresh();
        $this->assertTrue($rol->hasPermissionTo('permiso-nuevo'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function roles_destroy_elimina_rol()
    {
        $rol = Role::create(['name' => 'rol-a-eliminar']);

        $response = $this->actingAs($this->admin)->delete(route('roles.destroy', $rol));

        $response->assertRedirect(route('roles.index'));
        $this->assertDatabaseMissing('roles', ['name' => 'rol-a-eliminar']);
    }
}
