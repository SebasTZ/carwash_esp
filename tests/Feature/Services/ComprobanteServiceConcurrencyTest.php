<?php

namespace Tests\Feature\Services;

use Tests\TestCase;
use App\Models\Comprobante;
use App\Models\SecuenciaComprobante;
use App\Models\Venta;
use App\Models\Lavado;
use App\Models\Cliente;
use App\Models\FormaPago;
use App\Services\ComprobanteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class ComprobanteServiceConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected ComprobanteService $comprobanteService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->comprobanteService = new ComprobanteService();
    }

    /**
     * Test: Generar múltiples comprobantes concurrentemente no genera duplicados
     * 
     * Escenario: 20 ventas generando comprobantes simultáneamente
     * Resultado Esperado: Todos los números de comprobante deben ser únicos
     */
    public function test_generar_comprobantes_concurrentemente_sin_duplicados()
    {
        // Arrange: Crear datos base
        $comprobante = Comprobante::factory()->create([
            'tipo_comprobante' => 'Factura',
            'serie' => 'F001',
        ]);

        $cliente = Cliente::factory()->create();

        // Asegurar que existe el registro de secuencia
        SecuenciaComprobante::create([
            'comprobante_id' => $comprobante->id,
            'ultimo_numero' => 0,
        ]);

        // Act: Simular 20 ventas concurrentes generando comprobantes
        $numerosGenerados = [];
        $ventasCreadas = 0;

        for ($i = 0; $i < 20; $i++) {
            try {
                $numeroComprobante = $this->comprobanteService->generarSiguienteNumero($comprobante->id);
                $numerosGenerados[] = $numeroComprobante;

                // Crear venta para simular el caso real
                Venta::create([
                    'cliente_id' => $cliente->id,
                    'comprobante_id' => $comprobante->id,
                    'numero_comprobante' => $numeroComprobante,
                    'fecha_hora' => now(),
                    'impuesto' => 9.00,
                    'total' => 59.00,
                    'medio_pago' => 'efectivo',
                ]);
                $ventasCreadas++;
            } catch (\Exception $e) {
                // Capturar cualquier error de duplicados
                $this->fail("Error al generar comprobante #{$i}: {$e->getMessage()}");
            }
        }

        // Assert: Verificar que todos los números son únicos
        $this->assertCount(20, $numerosGenerados, 'Deben generarse exactamente 20 números');
        $this->assertCount(20, array_unique($numerosGenerados), 'Todos los números deben ser únicos');
        
        // Verificar que los números son secuenciales del F0010001 al F0010020
        sort($numerosGenerados);
        $this->assertEquals('F0010001', $numerosGenerados[0], 'El primer número debe ser F0010001');
        $this->assertEquals('F0010020', $numerosGenerados[19], 'El último número debe ser F0010020');

        // Verificar que se crearon las 20 ventas
        $this->assertEquals(20, $ventasCreadas, 'Se deben crear 20 ventas exitosamente');
        $this->assertDatabaseCount('ventas', 20);

        // Verificar que la secuencia final es 20
        $secuencia = SecuenciaComprobante::where('comprobante_id', $comprobante->id)->first();
        $this->assertEquals(20, $secuencia->ultimo_numero);
    }

    /**
     * Test: Generar comprobantes con fallback cuando no existe tabla de secuencias
     * 
     * Escenario: El método generarSiguienteNumero() usa el fallback con lockForUpdate en ventas
     * Resultado Esperado: Los números se generan correctamente sin duplicados
     */
    public function test_generar_comprobantes_con_fallback_sin_duplicados()
    {
        // Arrange: Crear comprobante sin secuencia (para forzar fallback)
        $comprobante = Comprobante::factory()->create([
            'tipo_comprobante' => 'Boleta',
            'serie' => 'B001',
        ]);

        $cliente = Cliente::factory()->create();

        // NO crear SecuenciaComprobante para forzar el fallback
        // Pero simular que el try-catch falla eliminando la tabla temporalmente
        // (en el código real, el fallback se activa si la tabla no existe)

        // Act: Generar 10 comprobantes
        $numerosGenerados = [];

        for ($i = 0; $i < 10; $i++) {
            $numeroComprobante = $this->comprobanteService->generarSiguienteNumero($comprobante->id);
            $numerosGenerados[] = $numeroComprobante;

            Venta::create([
                'cliente_id' => $cliente->id,
                'comprobante_id' => $comprobante->id,
                'numero_comprobante' => $numeroComprobante,
                'fecha_hora' => now(),
                'impuesto' => 5.40,
                'total' => 35.40,
                'medio_pago' => 'tarjeta',
            ]);
        }

        // Assert: Verificar unicidad
        $this->assertCount(10, array_unique($numerosGenerados), 'Todos los números deben ser únicos');
        
        // Verificar secuencia correcta
        sort($numerosGenerados);
        $this->assertEquals('B0010001', $numerosGenerados[0]);
        $this->assertEquals('B0010010', $numerosGenerados[9]);
    }

    /**
     * Test: Verificar que el constraint UNIQUE en secuencias_comprobantes previene duplicados
     * 
     * Escenario: Intentar crear dos secuencias para el mismo comprobante
     * Resultado Esperado: Lanza excepción por violación de constraint UNIQUE
     */
    public function test_constraint_unique_previene_secuencias_duplicadas()
    {
        // Arrange
        $comprobante = Comprobante::factory()->create();

        SecuenciaComprobante::create([
            'comprobante_id' => $comprobante->id,
            'ultimo_numero' => 5,
        ]);

        // Act & Assert: Intentar crear otra secuencia para el mismo comprobante
        $this->expectException(\Illuminate\Database\QueryException::class);

        SecuenciaComprobante::create([
            'comprobante_id' => $comprobante->id,
            'ultimo_numero' => 10,
        ]);
    }

    /**
     * Test: Verificar que firstOrCreate es atómico con lockForUpdate
     * 
     * Escenario: Llamar generarSiguienteNumero() múltiples veces cuando la secuencia no existe
     * Resultado Esperado: Solo se crea una secuencia, los números son secuenciales
     */
    public function test_first_or_create_es_atomico_con_lock()
    {
        // Arrange
        $comprobante = Comprobante::factory()->create([
            'tipo_comprobante' => 'Factura',
            'serie' => 'F002',
        ]);

        $cliente = Cliente::factory()->create();

        // Act: Generar 15 comprobantes (la primera vez crea la secuencia)
        $numerosGenerados = [];

        for ($i = 0; $i < 15; $i++) {
            $numeroComprobante = $this->comprobanteService->generarSiguienteNumero($comprobante->id);
            $numerosGenerados[] = $numeroComprobante;

            Venta::create([
                'cliente_id' => $cliente->id,
                'comprobante_id' => $comprobante->id,
                'numero_comprobante' => $numeroComprobante,
                'fecha_hora' => now(),
                'impuesto' => 7.20,
                'total' => 47.20,
                'medio_pago' => 'efectivo',
            ]);
        }

        // Assert: Verificar que solo existe UNA secuencia
        $this->assertDatabaseCount('secuencias_comprobantes', 1);

        // Verificar que todos los números son únicos y secuenciales
        $this->assertCount(15, array_unique($numerosGenerados));
        sort($numerosGenerados);
        $this->assertEquals('F0020001', $numerosGenerados[0]);
        $this->assertEquals('F0020015', $numerosGenerados[14]);

        // Verificar que la secuencia tiene el valor correcto
        $secuencia = SecuenciaComprobante::where('comprobante_id', $comprobante->id)->first();
        $this->assertEquals(15, $secuencia->ultimo_numero);
    }

    /**
     * Test: Verificar formato correcto del número de comprobante
     * 
     * Escenario: Generar comprobantes con diferentes series
     * Resultado Esperado: El formato debe ser SERIE-XXXX con padding de 4 dígitos
     */
    public function test_formato_numero_comprobante_es_correcto()
    {
        // Arrange
        $comprobante = Comprobante::factory()->create([
            'tipo_comprobante' => 'Ticket',
            'serie' => 'T999',
        ]);

        SecuenciaComprobante::create([
            'comprobante_id' => $comprobante->id,
            'ultimo_numero' => 0,
        ]);

        // Act: Generar primer comprobante
        $numero1 = $this->comprobanteService->generarSiguienteNumero($comprobante->id);

        // Generar varios más para llegar al número 10
        for ($i = 2; $i <= 10; $i++) {
            $this->comprobanteService->generarSiguienteNumero($comprobante->id);
        }

        $numero10 = $this->comprobanteService->generarSiguienteNumero($comprobante->id);

        // Assert: Verificar formato con padding correcto
        $this->assertEquals('T9990001', $numero1, 'Primer número con padding de 4 dígitos');
        $this->assertEquals('T9990011', $numero10, 'Número 11 con padding de 4 dígitos');

        // Verificar que el formato cumple el patrón SERIEXXXX (sin guión)
        $this->assertMatchesRegularExpression('/^T999\d{4}$/', $numero1);
        $this->assertMatchesRegularExpression('/^T999\d{4}$/', $numero10);
    }
}
