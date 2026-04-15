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
        $this->withoutMiddleware([
            \Illuminate\Auth\Middleware\Authenticate::class,
            \Illuminate\Auth\Middleware\Authorize::class,
            \Spatie\Permission\Middleware\PermissionMiddleware::class,
            \Spatie\Permission\Middleware\RoleMiddleware::class,
            \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        $this->actingAs(User::factory()->create());

        $response = $this->get(route('ventas.create'));

        $response->assertOk();
        $response->assertSee('wire:name="ventas.producto-select"', false);
        $response->assertSee('wire:name="ventas.cliente-select"', false);
        $response->assertSee('id="producto_id"', false);
        $response->assertSee('id="cliente_id"', false);
    }
}
