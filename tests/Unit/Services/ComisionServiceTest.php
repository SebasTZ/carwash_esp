<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\ComisionService;
use App\Models\ControlLavado;
use App\Models\Lavador;
use App\Models\TipoVehiculo;
use App\Models\Venta;
use App\Models\PagoComision;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ComisionServiceTest extends TestCase
{
    use DatabaseMigrations;

    protected ComisionService $comisionService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->comisionService = app(ComisionService::class);
    }

    /** @test */
    public function calcula_comision_para_moto_correctamente()
    {
        // Arrange
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create([
            'nombre' => 'Moto',
            'comision' => 15.00,
            'estado' => 'activo',
        ]);
        $venta = Venta::factory()->create(['total' => 100.00]);
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'fin_interior' => now(),
        ]);

        // Act
        $pagoComision = $this->comisionService->registrarComisionLavado($controlLavado);

        // Assert
        $this->assertNotNull($pagoComision);
        $this->assertEquals(15.00, $pagoComision->monto_pagado);
    }

    /** @test */
    public function calcula_comision_para_sedan_correctamente()
    {
        // Arrange
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create([
            'nombre' => 'Sedan',
            'comision' => 20.00,
            'estado' => 'activo',
        ]);
        $venta = Venta::factory()->create(['total' => 100.00]);
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'fin_interior' => now(),
        ]);

        // Act
        $pagoComision = $this->comisionService->registrarComisionLavado($controlLavado);

        // Assert
        $this->assertNotNull($pagoComision);
        $this->assertEquals(20.00, $pagoComision->monto_pagado);
    }

    /** @test */
    public function calcula_comision_para_suv_correctamente()
    {
        // Arrange
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create([
            'nombre' => 'SUV',
            'comision' => 25.00,
            'estado' => 'activo',
        ]);
        $venta = Venta::factory()->create(['total' => 150.00]);
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'fin_interior' => now(),
        ]);

        // Act
        $pagoComision = $this->comisionService->registrarComisionLavado($controlLavado);

        // Assert
        $this->assertNotNull($pagoComision);
        $this->assertEquals(25.00, $pagoComision->monto_pagado);
    }

    /** @test */
    public function calcula_comision_para_camioneta_correctamente()
    {
        // Arrange
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create([
            'nombre' => 'Camioneta',
            'comision' => 30.00,
            'estado' => 'activo',
        ]);
        $venta = Venta::factory()->create(['total' => 200.00]);
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'fin_interior' => now(),
        ]);

        // Act
        $pagoComision = $this->comisionService->registrarComisionLavado($controlLavado);

        // Assert
        $this->assertNotNull($pagoComision);
        $this->assertEquals(30.00, $pagoComision->monto_pagado);
    }

    /** @test */
    public function registra_comision_en_base_de_datos()
    {
        // Arrange
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create([
            'nombre' => 'Sedan',
            'comision' => 20.00,
            'estado' => 'activo',
        ]);
        $venta = Venta::factory()->create(['total' => 100.00]);
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'fin_interior' => now(),
        ]);

        // Act
        $pagoComision = $this->comisionService->registrarComisionLavado($controlLavado);

        // Assert
        $this->assertInstanceOf(PagoComision::class, $pagoComision);
        $this->assertEquals(20.00, $pagoComision->monto_pagado);
        $this->assertEquals($lavador->id, $pagoComision->lavador_id);

        $this->assertDatabaseHas('pagos_comisiones', [
            'lavador_id' => $lavador->id,
            'monto_pagado' => 20.00,
        ]);
    }

    /** @test */
    public function no_registra_comision_si_lavado_no_tiene_lavador()
    {
        // Arrange
        $tipoVehiculo = TipoVehiculo::factory()->create([
            'estado' => 'activo',
            'comision' => 20.00,
        ]);
        $venta = Venta::factory()->create(['total' => 100.00]);
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => null,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'fin_interior' => now(),
        ]);

        // Assert & Act
        $this->expectException(\Exception::class);
        
        $this->comisionService->registrarComisionLavado($controlLavado);
    }

    /** @test */
    public function no_registra_comision_si_lavado_no_finalizado()
    {
        // Arrange
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create([
            'estado' => 'activo',
            'comision' => 20.00,
        ]);
        $venta = Venta::factory()->create(['total' => 100.00]);
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'fin_interior' => null,
        ]);

        // Assert & Act
        $this->expectException(\Exception::class);
        
        $this->comisionService->registrarComisionLavado($controlLavado);
    }

    /** @test */
    public function usa_factor_default_para_tipo_vehiculo_desconocido()
    {
        // Arrange
        $lavador = Lavador::factory()->create(['estado' => 'activo']);
        $tipoVehiculo = TipoVehiculo::factory()->create([
            'nombre' => 'Tipo Desconocido',
            'comision' => 18.50,
            'estado' => 'activo',
        ]);
        $venta = Venta::factory()->create(['total' => 100.00]);
        $controlLavado = ControlLavado::factory()->create([
            'venta_id' => $venta->id,
            'lavador_id' => $lavador->id,
            'tipo_vehiculo_id' => $tipoVehiculo->id,
            'fin_interior' => now(),
        ]);

        // Act
        $pagoComision = $this->comisionService->registrarComisionLavado($controlLavado);

        // Assert
        $this->assertNotNull($pagoComision);
        $this->assertEquals(18.50, $pagoComision->monto_pagado);
    }
}
