<?php

namespace Tests\Unit\Events;

use Tests\TestCase;
use App\Events\LavadorCambiadoEvent;
use App\Events\LavadoCompletadoEvent;
use App\Models\ControlLavado;
use App\Models\Lavador;
use App\Models\TipoVehiculo;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Event;

class ControlLavadoEventsTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function lavador_cambiado_event_se_puede_crear()
    {
        // Arrange
        $lavadorAnterior = Lavador::factory()->create(['estado' => 'activo']);
        $lavadorNuevo = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        $venta = Venta::factory()->create();
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavadorNuevo->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
        ]);
        $usuarioId = 1;

        // Act
        $event = new LavadorCambiadoEvent(
            $controlLavado,
            $lavadorAnterior->id,
            $lavadorNuevo->id,
            $usuarioId,
            'Cambio por disponibilidad'
        );

        // Assert
        $this->assertInstanceOf(LavadorCambiadoEvent::class, $event);
        $this->assertEquals($controlLavado->id, $event->lavado->id);
        $this->assertEquals($lavadorAnterior->id, $event->lavadorAnteriorId);
        $this->assertEquals($lavadorNuevo->id, $event->lavadorNuevoId);
        $this->assertEquals($usuarioId, $event->usuarioId);
        $this->assertEquals('Cambio por disponibilidad', $event->motivo);
    }

    /** @test */
    public function lavador_cambiado_event_se_transmite_en_canal_correcto()
    {
        // Arrange
        $lavadorAnterior = Lavador::factory()->create(['estado' => 'activo']);
        $lavadorNuevo = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        $venta = Venta::factory()->create();
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavadorNuevo->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
        ]);
        $usuarioId = 1;

        $event = new LavadorCambiadoEvent(
            $controlLavado,
            $lavadorAnterior->id,
            $lavadorNuevo->id,
            $usuarioId,
            'Cambio por disponibilidad'
        );

        // Act
        $channels = $event->broadcastOn();

        // Assert
        $this->assertIsArray($channels);
        $this->assertCount(1, $channels);
        $this->assertEquals('private-control-lavados', $channels[0]->name);
    }

    /** @test */
    public function lavador_cambiado_event_implementa_should_broadcast()
    {
        // Arrange
        $lavadorAnterior = Lavador::factory()->create(['estado' => 'activo']);
        $lavadorNuevo = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        $venta = Venta::factory()->create();
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavadorNuevo->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
        ]);
        $usuarioId = 1;

        // Act
        $event = new LavadorCambiadoEvent(
            $controlLavado,
            $lavadorAnterior->id,
            $lavadorNuevo->id,
            $usuarioId,
            'Cambio por disponibilidad'
        );

        // Assert
        $this->assertInstanceOf(\Illuminate\Contracts\Broadcasting\ShouldBroadcast::class, $event);
    }

    /** @test */
    public function lavado_completado_event_se_puede_crear()
    {
        // Arrange
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        $venta = Venta::factory()->create();
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'fin_interior' => now(),
        ]);

        // Act
        $event = new LavadoCompletadoEvent($controlLavado);

        // Assert
        $this->assertInstanceOf(LavadoCompletadoEvent::class, $event);
        $this->assertEquals($controlLavado->id, $event->lavado->id);
    }

    /** @test */
    public function lavado_completado_event_se_transmite_en_canal_correcto()
    {
        // Arrange
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        $venta = Venta::factory()->create();
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'fin_interior' => now(),
        ]);

        $event = new LavadoCompletadoEvent($controlLavado);

        // Act
        $channels = $event->broadcastOn();

        // Assert
        $this->assertIsArray($channels);
        $this->assertCount(1, $channels);
        $this->assertEquals('private-control-lavados', $channels[0]->name);
    }

    /** @test */
    public function lavado_completado_event_implementa_should_broadcast()
    {
        // Arrange
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        $venta = Venta::factory()->create();
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'fin_interior' => now(),
        ]);

        // Act
        $event = new LavadoCompletadoEvent($controlLavado);

        // Assert
        $this->assertInstanceOf(\Illuminate\Contracts\Broadcasting\ShouldBroadcast::class, $event);
    }

    /** @test */
    public function eventos_se_pueden_disparar_correctamente()
    {
        // Arrange
        Event::fake();
        
        $lavadorAnterior = Lavador::factory()->create(['estado' => 'activo']);
        $lavadorNuevo = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        $venta = Venta::factory()->create();
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavadorNuevo->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
        ]);
        $usuarioId = 1;

        // Act
        event(new LavadorCambiadoEvent(
            $controlLavado,
            $lavadorAnterior->id,
            $lavadorNuevo->id,
            $usuarioId,
            'Test cambio'
        ));
        
        event(new LavadoCompletadoEvent($controlLavado));

        // Assert
        Event::assertDispatched(LavadorCambiadoEvent::class);
        Event::assertDispatched(LavadoCompletadoEvent::class);
    }

    /** @test */
    public function lavador_cambiado_event_incluye_datos_para_broadcast()
    {
        // Arrange
        $lavadorAnterior = Lavador::factory()->create([
            'nombre' => 'Juan Pérez',
            'estado' => 'activo',
        ]);
        $lavadorNuevo = Lavador::factory()->create([
            'nombre' => 'Pedro García',
            'estado' => 'activo',
        ]);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        $venta = Venta::factory()->create();
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavadorNuevo->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
        ]);
        $usuarioId = 1;

        $event = new LavadorCambiadoEvent(
            $controlLavado,
            $lavadorAnterior->id,
            $lavadorNuevo->id,
            $usuarioId,
            'Cambio por disponibilidad'
        );

        // Act
        $broadcastData = $event->broadcastWith();

        // Assert
        $this->assertIsArray($broadcastData);
        $this->assertArrayHasKey('control_lavado_id', $broadcastData);
        $this->assertEquals($controlLavado->id, $broadcastData['control_lavado_id']);
    }

    /** @test */
    public function lavado_completado_event_incluye_datos_para_broadcast()
    {
        // Arrange
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create(['estado' => 'activo']);
        $venta = Venta::factory()->create();
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'fin_interior' => now(),
        ]);

        $event = new LavadoCompletadoEvent($controlLavado);

        // Act
        $broadcastData = $event->broadcastWith();

        // Assert
        $this->assertIsArray($broadcastData);
        $this->assertArrayHasKey('lavado_id', $broadcastData);
        $this->assertEquals($controlLavado->id, $broadcastData['lavado_id']);
    }
}
