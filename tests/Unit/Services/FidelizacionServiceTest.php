<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\FidelizacionService;
use App\Models\Cliente;
use App\Models\Documento;
use App\Models\Persona;
use App\Models\Fidelizacion;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;

class FidelizacionServiceTest extends TestCase
{
    use DatabaseMigrations;

    private FidelizacionService $fidelizacionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fidelizacionService = new FidelizacionService();
    }

    /** @test */
    public function puede_acumular_lavado()
    {
        $documento = Documento::factory()->create();
        $persona = Persona::factory()->create([
            'documento_id' => $documento->id,
            'numero_documento' => '12345678',
        ]);
        $cliente = Cliente::factory()->create([
            'persona_id' => $persona->id,
            'lavados_acumulados' => 5,
        ]);

        $this->fidelizacionService->acumularLavado($cliente);

        // Verificar que se incrementaron los lavados acumulados
        $cliente->refresh();
        $this->assertEquals(6, $cliente->lavados_acumulados);
    }

    /** @test */
    public function puede_acumular_puntos_de_fidelizacion()
    {
        $documento = Documento::factory()->create();
        $persona = Persona::factory()->create([
            'documento_id' => $documento->id,
            'numero_documento' => '12345678',
        ]);
        $cliente = Cliente::factory()->create([
            'persona_id' => $persona->id,
        ]);

        $this->fidelizacionService->acumularPuntos($cliente, 100.00);

        // Verificar que se registró en la tabla fidelizacion con 10% del total
        $this->assertDatabaseHas('fidelizacion', [
            'cliente_id' => $cliente->id,
            'puntos' => 10.0, // 100.00 * 0.1 = 10 puntos
        ]);
    }

    /** @test */
    public function puede_verificar_si_puede_usar_lavado_gratis()
    {
        $documento = Documento::factory()->create();
        $persona = Persona::factory()->create([
            'documento_id' => $documento->id,
            'numero_documento' => '12345678',
        ]);
        $cliente = Cliente::factory()->create([
            'persona_id' => $persona->id,
            'lavados_acumulados' => 10,
        ]);

        $this->assertTrue($this->fidelizacionService->puedeUsarLavadoGratis($cliente));
    }

    /** @test */
    public function no_puede_usar_lavado_gratis_sin_puntos_suficientes()
    {
        $documento = Documento::factory()->create();
        $persona = Persona::factory()->create([
            'documento_id' => $documento->id,
            'numero_documento' => '12345678',
        ]);
        $cliente = Cliente::factory()->create([
            'persona_id' => $persona->id,
            'lavados_acumulados' => 5,
        ]);

        $this->assertFalse($this->fidelizacionService->puedeUsarLavadoGratis($cliente));
    }

    /** @test */
    public function puede_canjear_lavado_gratis()
    {
        $documento = Documento::factory()->create();
        $persona = Persona::factory()->create([
            'documento_id' => $documento->id,
            'numero_documento' => '12345678',
        ]);
        $cliente = Cliente::factory()->create([
            'persona_id' => $persona->id,
            'lavados_acumulados' => 10,
        ]);

        $this->fidelizacionService->canjearLavadoGratis($cliente);

        $cliente->refresh();
        $this->assertEquals(0, $cliente->lavados_acumulados);
    }

    /** @test */
    public function puede_revertir_lavado_acumulado()
    {
        $documento = Documento::factory()->create();
        $persona = Persona::factory()->create([
            'documento_id' => $documento->id,
            'numero_documento' => '12345678',
        ]);
        $cliente = Cliente::factory()->create([
            'persona_id' => $persona->id,
            'lavados_acumulados' => 5,
        ]);

        $this->fidelizacionService->revertirLavado($cliente);

        $cliente->refresh();
        $this->assertEquals(4, $cliente->lavados_acumulados);
    }

    /** @test */
    public function puede_obtener_progreso_de_fidelizacion()
    {
        $documento = Documento::factory()->create();
        $persona = Persona::factory()->create([
            'documento_id' => $documento->id,
            'numero_documento' => '12345678',
        ]);
        $cliente = Cliente::factory()->create([
            'persona_id' => $persona->id,
            'lavados_acumulados' => 7,
        ]);

        $progreso = $this->fidelizacionService->obtenerProgreso($cliente);

        $this->assertEquals(7, $progreso['lavados_acumulados']);
        $this->assertEquals(3, $progreso['lavados_faltantes']);
        $this->assertEquals(70, $progreso['progreso_porcentaje']);
        $this->assertFalse($progreso['puede_canjear']);
    }

    /** @test */
    public function calcula_puntos_correctamente_con_10_porciento()
    {
        $documento = Documento::factory()->create();
        $persona = Persona::factory()->create([
            'documento_id' => $documento->id,
            'numero_documento' => '12345678',
        ]);
        $cliente = Cliente::factory()->create([
            'persona_id' => $persona->id,
        ]);

        // Primera acumulación
        $this->fidelizacionService->acumularPuntos($cliente, 50.00);
        
        $cliente->refresh();
        $cliente->load('fidelizacion');
        $this->assertEquals(5.0, $cliente->fidelizacion->puntos); // 50 * 0.1 = 5

        // Segunda acumulación - verifica que incrementa
        $this->fidelizacionService->acumularPuntos($cliente, 50.00);
        
        $cliente->refresh();
        $cliente->load('fidelizacion');
        $this->assertEquals(10.0, $cliente->fidelizacion->puntos); // 5 + 5 = 10
    }
}
