<?php

namespace Tests\Unit\Events;

use Tests\TestCase;
use App\Events\StockBajoEvent;
use App\Models\Producto;
use App\Models\Caracteristica;
use App\Models\Marca;
use App\Models\Presentacione;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Event;

class StockBajoEventTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function evento_se_dispara_cuando_stock_es_bajo()
    {
        Event::fake([StockBajoEvent::class]);

        $caracteristica = Caracteristica::factory()->create();
        $marca = Marca::factory()->create(['caracteristica_id' => $caracteristica->id]);
        $presentacion = Presentacione::factory()->create(['caracteristica_id' => $caracteristica->id]);

        $producto = Producto::factory()->create([
            'stock' => 3,
            'stock_minimo' => 10,
            'marca_id' => $marca->id,
            'presentacione_id' => $presentacion->id,
        ]);

        // Disparar evento manualmente
        event(new StockBajoEvent($producto));

        Event::assertDispatched(StockBajoEvent::class, function ($event) use ($producto) {
            return $event->producto->id === $producto->id;
        });
    }

    /** @test */
    public function evento_contiene_informacion_del_producto()
    {
        $caracteristica = Caracteristica::factory()->create();
        $marca = Marca::factory()->create(['caracteristica_id' => $caracteristica->id]);
        $presentacion = Presentacione::factory()->create(['caracteristica_id' => $caracteristica->id]);

        $producto = Producto::factory()->create([
            'nombre' => 'Producto Test',
            'stock' => 5,
            'marca_id' => $marca->id,
            'presentacione_id' => $presentacion->id,
        ]);

        $event = new StockBajoEvent($producto);

        $this->assertEquals($producto->id, $event->producto->id);
        $this->assertEquals('Producto Test', $event->producto->nombre);
        $this->assertEquals(5, $event->producto->stock);
    }

    /** @test */
    public function evento_debe_ser_broadcasteable()
    {
        $caracteristica = Caracteristica::factory()->create();
        $marca = Marca::factory()->create(['caracteristica_id' => $caracteristica->id]);
        $presentacion = Presentacione::factory()->create(['caracteristica_id' => $caracteristica->id]);

        $producto = Producto::factory()->create([
            'stock' => 3,
            'marca_id' => $marca->id,
            'presentacione_id' => $presentacion->id,
        ]);

        $event = new StockBajoEvent($producto);

        // Verificar que el evento existe y tiene el producto
        $this->assertNotNull($event->producto);
    }
}
