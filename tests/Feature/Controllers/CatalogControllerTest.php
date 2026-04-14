<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Marca;
use App\Models\Categoria;
use App\Models\Presentacione;
use App\Models\Caracteristica;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Tests para los controllers de catálogo: marcas, categorías, presentaciones.
 * Todos comparten el mismo patrón: Caracteristica + modelo hijo.
 */
class CatalogControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role = Role::create(['name' => 'admin']);
        $permisos = [
            'ver-marca', 'crear-marca', 'editar-marca', 'eliminar-marca',
            'ver-categoria', 'crear-categoria', 'editar-categoria', 'eliminar-categoria',
            'ver-presentacione', 'crear-presentacione', 'editar-presentacione', 'eliminar-presentacione',
        ];
        foreach ($permisos as $p) {
            Permission::create(['name' => $p]);
        }
        $role->givePermissionTo($permisos);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    // ── MARCAS ───────────────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function marcas_index_devuelve_vista()
    {
        $response = $this->actingAs($this->admin)->get(route('marcas.index'));
        $response->assertStatus(200);
        $response->assertViewIs('marca.index');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function marcas_store_crea_marca()
    {
        $response = $this->actingAs($this->admin)->post(route('marcas.store'), [
            'nombre'      => 'Toyota',
            'descripcion' => 'Marca japonesa',
        ]);

        $response->assertRedirect(route('marcas.index'));
        $this->assertDatabaseHas('caracteristicas', ['nombre' => 'Toyota']);
        $this->assertEquals(1, Marca::count());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function marcas_store_falla_sin_nombre()
    {
        $response = $this->actingAs($this->admin)->post(route('marcas.store'), [
            'nombre' => '',
        ]);

        $response->assertSessionHasErrors('nombre');
        $this->assertEquals(0, Marca::count());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function marcas_destroy_desactiva_marca()
    {
        $marca = Marca::factory()->create();
        $marca->caracteristica->update(['estado' => 1]);

        $response = $this->actingAs($this->admin)->delete(route('marcas.destroy', $marca));

        $response->assertRedirect(route('marcas.index'));
        $this->assertEquals(0, $marca->caracteristica->fresh()->estado);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function marcas_destroy_reactiva_marca_inactiva()
    {
        $marca = Marca::factory()->create();
        $marca->caracteristica->update(['estado' => 0]);

        $response = $this->actingAs($this->admin)->delete(route('marcas.destroy', $marca));

        $response->assertRedirect(route('marcas.index'));
        $this->assertEquals(1, $marca->caracteristica->fresh()->estado);
    }

    // ── CATEGORÍAS ───────────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function categorias_index_devuelve_vista()
    {
        $response = $this->actingAs($this->admin)->get(route('categorias.index'));
        $response->assertStatus(200);
        $response->assertViewIs('categoria.index');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function categorias_store_crea_categoria()
    {
        $response = $this->actingAs($this->admin)->post(route('categorias.store'), [
            'nombre'      => 'Lubricantes',
            'descripcion' => 'Aceites y lubricantes',
        ]);

        $response->assertRedirect(route('categorias.index'));
        $this->assertEquals(1, Categoria::count());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function categorias_destroy_desactiva_categoria()
    {
        $categoria = Categoria::factory()->create();
        $categoria->caracteristica->update(['estado' => 1]);

        $response = $this->actingAs($this->admin)->delete(route('categorias.destroy', $categoria));

        $response->assertRedirect(route('categorias.index'));
        $this->assertEquals(0, $categoria->caracteristica->fresh()->estado);
    }

    // ── PRESENTACIONES ───────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function presentaciones_index_devuelve_vista()
    {
        $response = $this->actingAs($this->admin)->get(route('presentaciones.index'));
        $response->assertStatus(200);
        $response->assertViewIs('presentacione.index');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function presentaciones_store_crea_presentacion()
    {
        $response = $this->actingAs($this->admin)->post(route('presentaciones.store'), [
            'nombre'      => 'Litro',
            'descripcion' => 'Un litro',
        ]);

        $response->assertRedirect(route('presentaciones.index'));
        $this->assertEquals(1, Presentacione::count());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function presentaciones_destroy_desactiva_presentacion()
    {
        $presentacion = Presentacione::factory()->create();
        $presentacion->caracteristica->update(['estado' => 1]);

        $response = $this->actingAs($this->admin)->delete(route('presentaciones.destroy', $presentacion));

        $response->assertRedirect(route('presentaciones.index'));
        $this->assertEquals(0, $presentacion->caracteristica->fresh()->estado);
    }
}
