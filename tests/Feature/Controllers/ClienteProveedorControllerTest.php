<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Proveedore;
use App\Models\Persona;
use App\Models\Documento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class ClienteProveedorControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Documento $documento;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role = Role::create(['name' => 'admin']);
        $permisos = [
            'ver-cliente', 'crear-cliente', 'editar-cliente', 'eliminar-cliente',
            'ver-proveedore', 'crear-proveedore', 'editar-proveedore', 'eliminar-proveedore',
        ];
        foreach ($permisos as $p) {
            Permission::create(['name' => $p]);
        }
        $role->givePermissionTo($permisos);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        $this->documento = Documento::factory()->create();
    }

    // ── CLIENTES ─────────────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function clientes_index_devuelve_vista()
    {
        $response = $this->actingAs($this->admin)->get(route('clientes.index'));
        $response->assertStatus(200);
        $response->assertViewIs('cliente.index');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function clientes_store_crea_cliente()
    {
        $response = $this->actingAs($this->admin)->post(route('clientes.store'), [
            'tipo_persona'     => 'natural',
            'razon_social'     => 'Juan García',
            'numero_documento' => '12345678',
            'documento_id'     => $this->documento->id,
            'direccion'        => 'Av. Lima 123',
            'telefono'         => '987654321',
            'email'            => 'juan@test.com',
            'estado'           => 1,
        ]);

        $response->assertRedirect(route('clientes.index'));
        $this->assertEquals(1, Cliente::count());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function clientes_store_falla_sin_razon_social()
    {
        $response = $this->actingAs($this->admin)->post(route('clientes.store'), [
            'tipo_persona'     => 'natural',
            'razon_social'     => '',
            'numero_documento' => '12345678',
            'documento_id'     => $this->documento->id,
        ]);

        $response->assertSessionHasErrors('razon_social');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function clientes_destroy_desactiva_cliente_activo()
    {
        $persona  = Persona::factory()->create(['estado' => 1, 'documento_id' => $this->documento->id]);
        $cliente  = Cliente::factory()->create(['persona_id' => $persona->id]);

        $response = $this->actingAs($this->admin)->delete(route('clientes.destroy', $cliente));

        $response->assertRedirect(route('clientes.index'));
        $this->assertEquals(0, $persona->fresh()->estado);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function clientes_destroy_reactiva_cliente_inactivo()
    {
        $persona  = Persona::factory()->create(['estado' => 0, 'documento_id' => $this->documento->id]);
        $cliente  = Cliente::factory()->create(['persona_id' => $persona->id]);

        $response = $this->actingAs($this->admin)->delete(route('clientes.destroy', $cliente));

        $response->assertRedirect(route('clientes.index'));
        $this->assertEquals(1, $persona->fresh()->estado);
    }

    // ── PROVEEDORES ──────────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function proveedores_index_devuelve_vista()
    {
        $response = $this->actingAs($this->admin)->get(route('proveedores.index'));
        $response->assertStatus(200);
        $response->assertViewIs('proveedore.index');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function proveedores_store_crea_proveedor()
    {
        $response = $this->actingAs($this->admin)->post(route('proveedores.store'), [
            'tipo_persona'     => 'juridica',
            'razon_social'     => 'Distribuidora SAC',
            'numero_documento' => '20123456789',
            'documento_id'     => $this->documento->id,
            'direccion'        => 'Jr. Comercio 456',
            'telefono'         => '012345678',
            'email'            => 'dist@test.com',
            'estado'           => 1,
        ]);

        $response->assertRedirect(route('proveedores.index'));
        $this->assertEquals(1, Proveedore::count());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function proveedores_destroy_desactiva_proveedor()
    {
        $persona    = Persona::factory()->create(['estado' => 1, 'documento_id' => $this->documento->id]);
        $proveedor  = Proveedore::factory()->create(['persona_id' => $persona->id]);

        $response = $this->actingAs($this->admin)->delete(route('proveedores.destroy', $proveedor));

        $response->assertRedirect(route('proveedores.index'));
        $this->assertEquals(0, $persona->fresh()->estado);
    }
}
