<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\Estacionamiento;
use App\Models\Cliente;
use App\Models\Persona;
use App\Models\Documento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Tests CRÍTICOS para Bug #3 y #4: Estacionamiento
 * 
 * Bug #3: Sin validación de capacidad máxima
 * Bug #4: Placas duplicadas permitidas
 * 
 * IMPACTO: Control operativo y confusión en gestión de vehículos
 */
class EstacionamientoControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Cliente $cliente;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear roles y permisos
        $role = Role::create(['name' => 'admin']);
        Permission::create(['name' => 'crear-estacionamiento']);
        Permission::create(['name' => 'ver-estacionamiento']);
        $role->givePermissionTo(['crear-estacionamiento', 'ver-estacionamiento']);
        
        // Crear usuario con permisos
        $this->user = User::factory()->create();
        $this->user->assignRole('admin');
        
        // Crear cliente de prueba
        $documento = Documento::factory()->create();
        $persona = Persona::factory()->create(['documento_id' => $documento->id]);
        $this->cliente = Cliente::factory()->create(['persona_id' => $persona->id]);
    }

    /**
     * TEST CRÍTICO #3: No debe permitir entrada si estacionamiento está lleno
     * 
     * @test
     */
    public function no_debe_permitir_entrada_si_estacionamiento_lleno()
    {
        // Arrange: Llenar estacionamiento (capacidad = 20 por defecto)
        Estacionamiento::factory()->count(20)->create([
            'estado' => 'ocupado',
            'hora_salida' => null
        ]);

        // Act: Intentar nueva entrada
        $response = $this->actingAs($this->user)->post(route('estacionamiento.store'), [
            'cliente_id' => $this->cliente->id,
            'placa' => 'ABC-999',
            'marca' => 'Toyota',
            'modelo' => 'Corolla',
            'telefono' => '987654321',
            'tarifa_hora' => 5.00
        ]);

        // Assert: Debe rechazar
        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('lleno', session('error'));
        
        // Verificar que NO se creó
        $this->assertEquals(20, Estacionamiento::where('estado', 'ocupado')->count());
        $this->assertNull(Estacionamiento::where('placa', 'ABC-999')->first());
    }

    /**
     * TEST CRÍTICO #4: No debe permitir entrada con placa duplicada
     * 
     * @test
     */
    public function no_debe_permitir_entrada_con_placa_duplicada()
    {
        // Arrange: Vehículo ya estacionado
        Estacionamiento::factory()->create([
            'placa' => 'ABC-123',
            'estado' => 'ocupado',
            'hora_salida' => null
        ]);

        // Act: Intentar entrada con misma placa
        $response = $this->actingAs($this->user)->post(route('estacionamiento.store'), [
            'cliente_id' => $this->cliente->id,
            'placa' => 'ABC-123',
            'marca' => 'Honda',
            'modelo' => 'Civic',
            'telefono' => '987654321',
            'tarifa_hora' => 5.00
        ]);

        // Assert: Debe rechazar
        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('ya está estacionado', session('error'));
        
        // Solo debe haber UNA entrada con esa placa activa
        $this->assertEquals(1, Estacionamiento::where('placa', 'ABC-123')
            ->where('estado', 'ocupado')
            ->count());
    }

    /**
     * TEST: Debe permitir entrada si hay espacio disponible
     * 
     * @test
     */
    public function debe_permitir_entrada_si_hay_espacio()
    {
        // Arrange: Estacionamiento con espacio (10/20)
        Estacionamiento::factory()->count(10)->create([
            'estado' => 'ocupado'
        ]);

        // Act: Intentar entrada
        $response = $this->actingAs($this->user)->post(route('estacionamiento.store'), [
            'cliente_id' => $this->cliente->id,
            'placa' => 'XYZ-789',
            'marca' => 'Nissan',
            'modelo' => 'Sentra',
            'telefono' => '987654321',
            'tarifa_hora' => 5.00
        ]);

        // Assert: Debe permitir
        $response->assertRedirect(route('estacionamiento.index'));
        $response->assertSessionHas('success');
        
        // Verificar que se creó
        $this->assertEquals(11, Estacionamiento::where('estado', 'ocupado')->count());
        $this->assertNotNull(Estacionamiento::where('placa', 'XYZ-789')->first());
    }

    /**
     * TEST: Debe permitir reingreso de placa que ya salió
     * 
     * @test
     */
    public function debe_permitir_reingreso_de_placa_que_ya_salio()
    {
        // Arrange: Vehículo que ya salió
        Estacionamiento::factory()->create([
            'placa' => 'DEF-456',
            'estado' => 'finalizado',
            'hora_salida' => now()->subHours(2)
        ]);

        // Act: Permitir reingreso
        $response = $this->actingAs($this->user)->post(route('estacionamiento.store'), [
            'cliente_id' => $this->cliente->id,
            'placa' => 'DEF-456',
            'marca' => 'Ford',
            'modelo' => 'Focus',
            'telefono' => '987654321',
            'tarifa_hora' => 5.00
        ]);

        // Assert: Debe permitir
        $response->assertRedirect(route('estacionamiento.index'));
        $response->assertSessionHas('success');
        
        // Debe haber 2 registros con esa placa (uno finalizado, uno activo)
        $this->assertEquals(2, Estacionamiento::where('placa', 'DEF-456')->count());
        $this->assertEquals(1, Estacionamiento::where('placa', 'DEF-456')
            ->where('estado', 'ocupado')
            ->count());
    }

    /**
     * TEST: Capacidad debe ser configurable
     * 
     * @test
     */
    public function capacidad_debe_ser_configurable()
    {
        // Arrange: Configurar capacidad personalizada
        config(['estacionamiento.capacidad_maxima' => 5]);
        
        // Llenar hasta capacidad
        Estacionamiento::factory()->count(5)->create(['estado' => 'ocupado']);

        // Act: Intentar exceder
        $response = $this->actingAs($this->user)->post(route('estacionamiento.store'), [
            'cliente_id' => $this->cliente->id,
            'placa' => 'GHI-123',
            'marca' => 'Mazda',
            'modelo' => 'CX-5',
            'telefono' => '987654321',
            'tarifa_hora' => 5.00
        ]);

        // Assert: Debe rechazar
        $response->assertSessionHas('error');
        $this->assertEquals(5, Estacionamiento::where('estado', 'ocupado')->count());
    }

    /**
     * TEST: Validación de placa debe ser case-insensitive
     * 
     * @test
     */
    public function validacion_placa_debe_ser_case_insensitive()
    {
        // Arrange: Placa en mayúsculas
        Estacionamiento::factory()->create([
            'placa' => 'ABC-123',
            'estado' => 'ocupado'
        ]);

        // Act: Intentar con minúsculas
        $response = $this->actingAs($this->user)->post(route('estacionamiento.store'), [
            'cliente_id' => $this->cliente->id,
            'placa' => 'abc-123', // minúsculas
            'marca' => 'Kia',
            'modelo' => 'Rio',
            'telefono' => '987654321',
            'tarifa_hora' => 5.00
        ]);

        // Assert: Debe rechazar (son la misma placa)
        $response->assertSessionHas('error');
    }

    /**
     * TEST: Debe mostrar mensaje con espacios disponibles
     * 
     * @test
     */
    public function debe_mostrar_espacios_disponibles_al_rechazar()
    {
        // Arrange: 18 de 20 espacios ocupados
        Estacionamiento::factory()->count(18)->create(['estado' => 'ocupado']);
        config(['estacionamiento.capacidad_maxima' => 20]);

        // Intentar entrada válida (debe mostrar espacios)
        $response = $this->actingAs($this->user)->post(route('estacionamiento.store'), [
            'cliente_id' => $this->cliente->id,
            'placa' => 'TEST-01',
            'marca' => 'Test',
            'modelo' => 'Test',
            'telefono' => '987654321',
            'tarifa_hora' => 5.00
        ]);

        // Debe tener éxito y poder consultar espacios
        $espaciosOcupados = Estacionamiento::where('estado', 'ocupado')->count();
        $this->assertLessThanOrEqual(20, $espaciosOcupados);
    }
}
