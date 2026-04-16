<?php

namespace Tests\Feature\Livewire\Ventas;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VentaCreateLivewireIntegrationTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function vista_crear_venta_renderiza_componentes_livewire(): void
    {
        // Propósito: verificar que los componentes Livewire están presentes en el HTML.
        // El middleware de permisos se omite para aislar el renderizado de los componentes.
        $this->withoutMiddleware([
            \Illuminate\Auth\Middleware\Authenticate::class,
            \Illuminate\Auth\Middleware\Authorize::class,
            \Spatie\Permission\Middleware\PermissionMiddleware::class,
            \Spatie\Permission\Middleware\RoleMiddleware::class,
            \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $permisoCrearVenta = \Spatie\Permission\Models\Permission::findOrCreate('crear-venta');
        $rolCajero = \Spatie\Permission\Models\Role::findOrCreate('cajero');
        $rolCajero->givePermissionTo($permisoCrearVenta);

        $user = User::factory()->create();
        $user->assignRole($rolCajero);
        $this->actingAs($user);

        $response = $this->get(route('ventas.create'));

        $response->assertOk();
        $response->assertSee('wire:name="ventas.producto-select"', false);
        $response->assertSee('wire:name="ventas.cliente-select"', false);
        $response->assertSee('id="producto_id"', false);
        $response->assertSee('id="cliente_id"', false);
    }
}
