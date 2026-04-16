<?php

namespace Tests\Feature\Controllers;

use App\Models\Cliente;
use App\Models\Documento;
use App\Models\Mantenimiento;
use App\Models\Persona;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MantenimientoControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Cliente $cliente;

    protected function setUp(): void
    {
        parent::setUp();

        \Spatie\Permission\Models\Permission::create(['name' => 'ver-mantenimiento']);
        \Spatie\Permission\Models\Permission::create(['name' => 'crear-mantenimiento']);
        \Spatie\Permission\Models\Permission::create(['name' => 'editar-mantenimiento']);
        \Spatie\Permission\Models\Permission::create(['name' => 'eliminar-mantenimiento']);
        \Spatie\Permission\Models\Permission::create(['name' => 'reporte-mantenimiento']);

        $role = \Spatie\Permission\Models\Role::create(['name' => 'admin-mant-test']);
        $role->givePermissionTo(['ver-mantenimiento','crear-mantenimiento','editar-mantenimiento','eliminar-mantenimiento','reporte-mantenimiento']);

        $this->user = User::factory()->create();
        $this->user->assignRole('admin-mant-test');
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
    public function store_crea_mantenimiento_con_datos_iniciales()
    {
        $response = $this->post(route('mantenimientos.store'), [
            'cliente_id' => $this->cliente->id,
            'placa' => 'abc123',
            'modelo' => 'Hilux',
            'tipo_vehiculo' => 'Camioneta',
            'fecha_ingreso' => now()->format('Y-m-d H:i:s'),
            'fecha_entrega_estimada' => now()->addDay()->format('Y-m-d H:i:s'),
            'tipo_servicio' => 'Cambio de aceite',
            'descripcion_trabajo' => 'Cambio completo y revision',
            'observaciones' => 'Ninguna',
            'costo_estimado' => 180,
            'mecanico_responsable' => 'Carlos',
        ]);

        $response->assertRedirect(route('mantenimientos.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('mantenimientos', [
            'cliente_id' => $this->cliente->id,
            'placa' => 'ABC123',
            'estado' => 'recibido',
            'pagado' => 0,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function update_si_ocurre_error_redirige_a_edit_con_mensaje()
    {
        $mantenimiento = Mantenimiento::create([
            'cliente_id' => $this->cliente->id,
            'placa' => 'AAA111',
            'modelo' => 'Corolla',
            'tipo_vehiculo' => 'Sedan',
            'fecha_ingreso' => now()->subDay(),
            'tipo_servicio' => 'Frenos',
            'descripcion_trabajo' => 'Cambio de pastillas',
            'estado' => 'en_proceso',
            'pagado' => false,
        ]);

        $response = $this->put(route('mantenimientos.update', $mantenimiento), [
            'cliente_id' => $this->cliente->id,
            'placa' => 'bbb222',
            'modelo' => 'Corolla',
            'tipo_vehiculo' => 'Sedan',
            'fecha_ingreso' => $mantenimiento->fecha_ingreso->format('Y-m-d H:i:s'),
            'fecha_entrega_estimada' => now()->addDay()->format('Y-m-d H:i:s'),
            'fecha_entrega_real' => '',
            'tipo_servicio' => 'Frenos',
            'descripcion_trabajo' => 'Cambio de pastillas y discos',
            'observaciones' => 'Listo para entrega',
            'costo_estimado' => 200,
            'costo_final' => 0,
            'mecanico_responsable' => 'Pedro',
            'estado' => 'en_proceso',
            'pagado' => '1',
        ]);

        $response->assertRedirect(route('mantenimientos.edit', $mantenimiento));
        $response->assertSessionHas('error');

        $mantenimiento->refresh();
        $this->assertSame('AAA111', $mantenimiento->placa);
        $this->assertSame('en_proceso', $mantenimiento->estado);
        $this->assertFalse($mantenimiento->pagado);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cambiar_estado_de_terminado_a_entregado_registra_fecha_y_redirige_a_show()
    {
        $mantenimiento = Mantenimiento::create([
            'cliente_id' => $this->cliente->id,
            'placa' => 'CCC333',
            'modelo' => 'Civic',
            'tipo_vehiculo' => 'Sedan',
            'fecha_ingreso' => now()->subDays(2),
            'tipo_servicio' => 'Diagnostico',
            'descripcion_trabajo' => 'Diagnostico general',
            'estado' => 'terminado',
            'pagado' => false,
        ]);

        $response = $this->post(route('mantenimientos.cambiarEstado', $mantenimiento), [
            'estado' => 'entregado',
        ]);

        $response->assertRedirect(route('mantenimientos.show', $mantenimiento));
        $response->assertSessionHas('success');

        $mantenimiento->refresh();
        $this->assertSame('entregado', $mantenimiento->estado);
        $this->assertNotNull($mantenimiento->fecha_entrega_real);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cambiar_estado_invalido_no_modifica_registro()
    {
        $mantenimiento = Mantenimiento::create([
            'cliente_id' => $this->cliente->id,
            'placa' => 'EEE555',
            'modelo' => 'Yaris',
            'tipo_vehiculo' => 'Sedan',
            'fecha_ingreso' => now()->subDay(),
            'tipo_servicio' => 'Inspección',
            'descripcion_trabajo' => 'Revisión completa',
            'estado' => 'recibido',
            'pagado' => false,
        ]);

        $response = $this->post(route('mantenimientos.cambiarEstado', $mantenimiento), [
            'estado' => 'entregado',
        ]);

        $response->assertRedirect(route('mantenimientos.show', $mantenimiento));
        $response->assertSessionHas('error');

        $mantenimiento->refresh();
        $this->assertSame('recibido', $mantenimiento->estado);
        $this->assertNull($mantenimiento->fecha_entrega_real);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function update_no_permite_salto_invalido_de_estado()
    {
        $mantenimiento = Mantenimiento::create([
            'cliente_id' => $this->cliente->id,
            'placa' => 'FFF666',
            'modelo' => 'Onix',
            'tipo_vehiculo' => 'Sedan',
            'fecha_ingreso' => now()->subDay(),
            'tipo_servicio' => 'Mantenimiento',
            'descripcion_trabajo' => 'Cambio de filtros',
            'estado' => 'recibido',
            'pagado' => false,
        ]);

        $response = $this->put(route('mantenimientos.update', $mantenimiento), [
            'cliente_id' => $this->cliente->id,
            'placa' => 'GGG777',
            'modelo' => 'Onix',
            'tipo_vehiculo' => 'Sedan',
            'fecha_ingreso' => $mantenimiento->fecha_ingreso->format('Y-m-d H:i:s'),
            'fecha_entrega_estimada' => now()->addDay()->format('Y-m-d H:i:s'),
            'fecha_entrega_real' => '',
            'tipo_servicio' => 'Mantenimiento',
            'descripcion_trabajo' => 'Cambio de filtros y aceite',
            'observaciones' => 'Sin observaciones',
            'costo_estimado' => 120,
            'costo_final' => 0,
            'mecanico_responsable' => 'Jose',
            'estado' => 'entregado',
            'pagado' => '1',
        ]);

        $response->assertRedirect(route('mantenimientos.edit', $mantenimiento));
        $response->assertSessionHas('error');

        $mantenimiento->refresh();
        $this->assertSame('FFF666', $mantenimiento->placa);
        $this->assertSame('recibido', $mantenimiento->estado);
        $this->assertNull($mantenimiento->fecha_entrega_real);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function vincular_venta_marca_pagado_y_asigna_costo_final()
    {
        $mantenimiento = Mantenimiento::create([
            'cliente_id' => $this->cliente->id,
            'placa' => 'DDD444',
            'modelo' => 'Rio',
            'tipo_vehiculo' => 'Hatchback',
            'fecha_ingreso' => now()->subDay(),
            'tipo_servicio' => 'Mantenimiento',
            'descripcion_trabajo' => 'Mantenimiento preventivo',
            'estado' => 'terminado',
            'pagado' => false,
        ]);

        $venta = Venta::factory()->create([
            'cliente_id' => $this->cliente->id,
            'total' => 350,
            'estado' => 1,
        ]);

        $response = $this->post(route('mantenimientos.vincularVenta', $mantenimiento), [
            'venta_id' => $venta->id,
        ]);

        $response->assertRedirect(route('mantenimientos.show', $mantenimiento));
        $response->assertSessionHas('success');

        $mantenimiento->refresh();
        $this->assertTrue($mantenimiento->pagado);
        $this->assertSame($venta->id, $mantenimiento->venta_id);
        $this->assertSame('350.00', number_format((float) $mantenimiento->costo_final, 2, '.', ''));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function index_retorna_403_si_usuario_no_tiene_permiso(): void
    {
        $sinPermisos = User::factory()->create();
        $this->actingAs($sinPermisos);
        $this->get(route('mantenimientos.index'))->assertStatus(403);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function store_retorna_403_si_usuario_no_tiene_permiso(): void
    {
        $sinPermisos = User::factory()->create();
        $this->actingAs($sinPermisos);
        $this->post(route('mantenimientos.store'), [])->assertStatus(403);
    }
}
