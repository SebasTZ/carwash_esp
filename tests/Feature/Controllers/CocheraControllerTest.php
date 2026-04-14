<?php

namespace Tests\Feature\Controllers;

use App\Models\Cliente;
use App\Models\Cochera;
use App\Models\Documento;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CocheraControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Cliente $cliente;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            \Illuminate\Auth\Middleware\Authenticate::class,
            \Illuminate\Auth\Middleware\Authorize::class,
            \Spatie\Permission\Middleware\PermissionMiddleware::class,
            \Spatie\Permission\Middleware\RoleMiddleware::class,
            \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $documento = Documento::factory()->create();
        $persona = Persona::factory()->create([
            'documento_id' => $documento->id,
            'estado' => 1,
            'tipo_persona' => 'Cliente',
        ]);

        $this->cliente = Cliente::factory()->create([
            'persona_id' => $persona->id,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function store_crea_registro_activo_y_normaliza_placa()
    {
        $response = $this->post(route('cocheras.store'), [
            'cliente_id' => $this->cliente->id,
            'placa' => 'abc-123',
            'modelo' => 'Corolla',
            'color' => 'Blanco',
            'tipo_vehiculo' => 'Sedan',
            'fecha_ingreso' => now()->toDateTimeString(),
            'ubicacion' => 'A-12',
            'tarifa_hora' => 12.5,
            'tarifa_dia' => 100,
            'observaciones' => 'Ingreso normal',
        ]);

        $response->assertRedirect(route('cocheras.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cocheras', [
            'cliente_id' => $this->cliente->id,
            'placa' => 'ABC-123',
            'estado' => 'activo',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function update_si_ocurre_error_redirige_a_edit_con_mensaje()
    {
        $cochera = Cochera::create([
            'cliente_id' => $this->cliente->id,
            'placa' => 'AAA-111',
            'modelo' => 'Yaris',
            'color' => 'Rojo',
            'tipo_vehiculo' => 'Sedan',
            'fecha_ingreso' => now()->subHours(8)->startOfHour(),
            'tarifa_hora' => 10,
            'tarifa_dia' => 80,
            'estado' => 'activo',
        ]);

        $response = $this->put(route('cocheras.update', $cochera), [
            'cliente_id' => $this->cliente->id,
            'placa' => 'bbb-222',
            'modelo' => 'Yaris',
            'color' => 'Azul',
            'tipo_vehiculo' => 'Sedan',
            'fecha_ingreso' => $cochera->fecha_ingreso->format('Y-m-d H:i:s'),
            'fecha_salida' => '',
            'ubicacion' => 'B-01',
            'tarifa_hora' => 10,
            'tarifa_dia' => 80,
            'monto_total' => '',
            'observaciones' => 'Finalizado por prueba',
            'estado' => 'activo',
        ]);

        $response->assertRedirect(route('cocheras.edit', $cochera));
        $response->assertSessionHas('error');

        $cochera->refresh();
        $this->assertSame('AAA-111', $cochera->placa);
        $this->assertSame('activo', $cochera->estado);
        $this->assertNull($cochera->monto_total);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function finalizar_cierra_servicio_y_actualiza_monto()
    {
        $cochera = Cochera::create([
            'cliente_id' => $this->cliente->id,
            'placa' => 'CCC-333',
            'modelo' => 'Rio',
            'color' => 'Negro',
            'tipo_vehiculo' => 'Hatchback',
            'fecha_ingreso' => now()->subHours(3)->startOfHour(),
            'tarifa_hora' => 15,
            'estado' => 'activo',
        ]);

        $response = $this->post(route('cocheras.finalizar', $cochera));

        $response->assertRedirect(route('cocheras.show', $cochera));
        $response->assertSessionHas('success');

        $cochera->refresh();
        $this->assertSame('finalizado', $cochera->estado);
        $this->assertNotNull($cochera->fecha_salida);
        $this->assertGreaterThan(0, (float) $cochera->monto_total);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function destroy_elimina_registro_de_cochera()
    {
        $cochera = Cochera::create([
            'cliente_id' => $this->cliente->id,
            'placa' => 'DDD-444',
            'modelo' => 'Civic',
            'color' => 'Plata',
            'tipo_vehiculo' => 'Sedan',
            'fecha_ingreso' => now(),
            'tarifa_hora' => 10,
            'estado' => 'activo',
        ]);

        $response = $this->delete(route('cocheras.destroy', $cochera));

        $response->assertRedirect(route('cocheras.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('cocheras', ['id' => $cochera->id]);
    }
}
