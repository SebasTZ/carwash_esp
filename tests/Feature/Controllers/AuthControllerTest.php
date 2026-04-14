<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'admin']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function login_muestra_formulario_si_no_autenticado()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function login_redirige_al_panel_si_ya_autenticado()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get('/login');
        $response->assertRedirect(route('panel'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function login_con_credenciales_correctas_redirige_al_panel()
    {
        $user = User::factory()->create([
            'email'    => 'test@test.com',
            'password' => bcrypt('password123'),
            'estado'   => 1,
        ]);
        $user->assignRole('admin');

        $response = $this->post('/login', [
            'email'    => 'test@test.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('panel'));
        $this->assertAuthenticatedAs($user);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function login_con_credenciales_incorrectas_muestra_error()
    {
        User::factory()->create([
            'email'    => 'test@test.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email'    => 'test@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function logout_cierra_sesion_y_redirige_al_login()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function rutas_protegidas_redirigen_a_login_sin_autenticacion()
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }
}
