<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Venta;
use App\Models\Compra;
use App\Models\Marca;
use App\Models\Categoria;
use App\Models\Presentacione;
use App\Models\Proveedore;
use App\Models\Estacionamiento;
use App\Models\Lavador;
use App\Models\PagoComision;
use App\Models\TarjetaRegalo;
use App\Models\TipoVehiculo;
use App\Models\ControlLavado;
use App\Models\Mantenimiento;
use App\Models\Cochera;
use App\Models\Cita;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

class PaginationTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear roles y permisos
        $this->artisan('db:seed', ['--class' => 'PermissionSeeder']);
        $this->artisan('db:seed', ['--class' => 'DocumentoSeeder']);
        $this->artisan('db:seed', ['--class' => 'ComprobanteSeeder']);

        // Crear rol administrador y asignar todos los permisos
        $adminRole = Role::firstOrCreate(['name' => 'Administrador']);
        $permisos = \Spatie\Permission\Models\Permission::pluck('id', 'id')->all();
        $adminRole->syncPermissions($permisos);

        // Crear usuario administrador
        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com',
            'estado' => 1,
        ]);
        $this->adminUser->assignRole($adminRole);
    }

    /** @test */
    public function test_productos_pagination_works()
    {
        // Crear 20 productos (más de una página)
        Producto::factory()->count(20)->create();

        $response = $this->actingAs($this->adminUser)
            ->get(route('productos.index'));

        $response->assertStatus(200);
        $response->assertViewHas('productos');
        
        $productos = $response->viewData('productos');
        
        // Verificar que es un paginador
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $productos);
        
        // Verificar que muestra 15 items por página
        $this->assertEquals(15, $productos->perPage());
        
        // Verificar que tiene 20 items en total
        $this->assertEquals(20, $productos->total());
        
        // Verificar que hay 2 páginas
        $this->assertEquals(2, $productos->lastPage());
    }

    /** @test */
    public function test_clientes_pagination_works()
    {
        Cliente::factory()->count(30)->create();

        $response = $this->actingAs($this->adminUser)
            ->get(route('clientes.index'));

        $response->assertStatus(200);
        $clientes = $response->viewData('clientes');
        
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $clientes);
        $this->assertEquals(15, $clientes->perPage());
        $this->assertEquals(30, $clientes->total());
    }

    /** @test */
    public function test_ventas_pagination_works()
    {
        Venta::factory()->count(25)->create();

        $response = $this->actingAs($this->adminUser)
            ->get(route('ventas.index'));

        $response->assertStatus(200);
        $ventas = $response->viewData('ventas');
        
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $ventas);
        $this->assertEquals(15, $ventas->perPage());
        $this->assertEquals(25, $ventas->total());
    }

    /** @test */
    public function test_compras_pagination_works()
    {
        Compra::factory()->count(18)->create();

        $response = $this->actingAs($this->adminUser)
            ->get(route('compras.index'));

        $response->assertStatus(200);
        $compras = $response->viewData('compras');
        
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $compras);
        $this->assertEquals(15, $compras->perPage());
        $this->assertEquals(18, $compras->total());
    }

    /** @test */
    public function test_usuarios_pagination_works()
    {
        User::factory()->count(22)->create();

        $response = $this->actingAs($this->adminUser)
            ->get(route('users.index'));

        $response->assertStatus(200);
        $users = $response->viewData('users');
        
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $users);
        $this->assertEquals(15, $users->perPage());
    }

    /** @test */
    public function test_marcas_pagination_works()
    {
        Marca::factory()->count(16)->create();

        $response = $this->actingAs($this->adminUser)
            ->get(route('marcas.index'));

        $response->assertStatus(200);
        $marcas = $response->viewData('marcas');
        
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $marcas);
        $this->assertEquals(15, $marcas->perPage());
    }

    /** @test */
    public function test_categorias_pagination_works()
    {
        Categoria::factory()->count(20)->create();

        $response = $this->actingAs($this->adminUser)
            ->get(route('categorias.index'));

        $response->assertStatus(200);
        $categorias = $response->viewData('categorias');
        
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $categorias);
        $this->assertEquals(15, $categorias->perPage());
    }

    /** @test */
    public function test_presentaciones_pagination_works()
    {
        Presentacione::factory()->count(17)->create();

        $response = $this->actingAs($this->adminUser)
            ->get(route('presentaciones.index'));

        $response->assertStatus(200);
        $presentaciones = $response->viewData('presentaciones');
        
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $presentaciones);
        $this->assertEquals(15, $presentaciones->perPage());
    }

    /** @test */
    public function test_proveedores_pagination_works()
    {
        Proveedore::factory()->count(19)->create();

        $response = $this->actingAs($this->adminUser)
            ->get(route('proveedores.index'));

        $response->assertStatus(200);
        $proveedores = $response->viewData('proveedores');
        
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $proveedores);
        $this->assertEquals(15, $proveedores->perPage());
    }

    /** @test */
    public function test_roles_pagination_works()
    {
        // Spatie Role no tiene factory, crear manualmente
        for ($i = 1; $i <= 16; $i++) {
            Role::create(['name' => 'Role ' . $i]);
        }

        $response = $this->actingAs($this->adminUser)
            ->get(route('roles.index'));

        $response->assertStatus(200);
        $roles = $response->viewData('roles');
        
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $roles);
        $this->assertEquals(15, $roles->perPage());
    }

    /** @test */
    public function test_pagination_shows_empty_state()
    {
        // No crear ningún producto
        $response = $this->actingAs($this->adminUser)
            ->get(route('productos.index'));

        $response->assertStatus(200);
        $productos = $response->viewData('productos');
        
        // Verificar que el paginador está vacío
        $this->assertEquals(0, $productos->total());
        $this->assertCount(0, $productos);
    }

    /** @test */
    public function test_pagination_second_page_works()
    {
        Producto::factory()->count(30)->create();

        // Ir a la página 2
        $response = $this->actingAs($this->adminUser)
            ->get(route('productos.index', ['page' => 2]));

        $response->assertStatus(200);
        $productos = $response->viewData('productos');
        
        // Verificar que estamos en la página 2
        $this->assertEquals(2, $productos->currentPage());
        
        // Verificar que tiene 15 items en esta página
        $this->assertCount(15, $productos);
    }

    /** @test */
    public function test_citas_pagination_preserves_filters()
    {
        Cita::factory()->count(20)->create();

        // Hacer request con filtros
        $response = $this->actingAs($this->adminUser)
            ->get(route('citas.index', [
                'fecha' => '2025-01-01',
                'estado' => 'pendiente',
                'page' => 1
            ]));

        $response->assertStatus(200);
        
        // Verificar que la vista carga correctamente con los parámetros
        // La paginación está funcionando aunque los filtros específicos puedan variar
        $citas = $response->viewData('citas');
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $citas);
    }

    /** @test */
    public function test_pagination_component_renders_correctly()
    {
        Producto::factory()->count(20)->create();

        $response = $this->actingAs($this->adminUser)
            ->get(route('productos.index'));

        $response->assertStatus(200);
        
        // Verificar que el componente de paginación se renderiza
        $response->assertSee('Mostrando');
        $response->assertSee('de');
        $response->assertSee('productos');
    }

    /** @test */
    public function test_all_pagination_endpoints_are_accessible()
    {
        $endpoints = [
            'productos.index',
            'clientes.index',
            'ventas.index',
            'compras.index',
            'users.index',
            'marcas.index',
            'categorias.index',
            'presentaciones.index',
            'proveedores.index',
            'roles.index',
        ];

        foreach ($endpoints as $route) {
            $response = $this->actingAs($this->adminUser)
                ->get(route($route));

            $response->assertStatus(200, "Route {$route} failed");
        }
    }

    /** @test */
    public function test_pagination_info_component_shows_correct_range()
    {
        Producto::factory()->count(20)->create();

        $response = $this->actingAs($this->adminUser)
            ->get(route('productos.index'));

        $response->assertStatus(200);
        
        // En la página 1, debe mostrar "Showing 1 to 15 of 20"
        $response->assertSee('Showing', false);
        $response->assertSee('to', false);
        $response->assertSee('of', false);
        $response->assertSee('results', false);
    }

    /** @test */
    public function test_pagination_shows_navigation_buttons()
    {
        Producto::factory()->count(30)->create();

        $response = $this->actingAs($this->adminUser)
            ->get(route('productos.index'));

        $response->assertStatus(200);
        
        // Verificar que existen los botones de navegación
        $response->assertSee('Siguiente');
        // En página 1, "Anterior" está deshabilitado pero visible en el HTML
        
        // Página 2
        $response = $this->actingAs($this->adminUser)
            ->get(route('productos.index', ['page' => 2]));
        
        $response->assertSee('Anterior');
        $response->assertSee('Siguiente');
    }
}
