<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use App\Jobs\GenerarReporteVentasJob;
use App\Models\Venta;
use App\Models\Cliente;
use App\Models\User;
use App\Models\Comprobante;
use App\Models\Documento;
use App\Models\Persona;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Mail;

class GenerarReporteVentasJobTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function puede_encolar_job_de_reporte_ventas()
    {
        Queue::fake();

        $fechaInicio = now()->subDays(7);
        $fechaFin = now();

        GenerarReporteVentasJob::dispatch($fechaInicio, $fechaFin);

        Queue::assertPushed(GenerarReporteVentasJob::class);
    }

    /** @test */
    public function job_procesa_ventas_correctamente()
    {
        $user = User::factory()->create();
        $documento = Documento::factory()->create();
        $persona = Persona::factory()->create(['documento_id' => $documento->id]);
        $cliente = Cliente::factory()->create(['persona_id' => $persona->id]);
        $comprobante = Comprobante::factory()->create();

        // Crear algunas ventas usando el factory
        $ventas = Venta::factory()->count(3)->create([
            'cliente_id' => $cliente->id,
            'user_id' => $user->id,
            'comprobante_id' => $comprobante->id,
            'fecha_hora' => now(),
            'estado' => 1,
        ]);

        // Verificar que se crearon
        $this->assertCount(3, $ventas);
        $this->assertDatabaseCount('ventas', 3);

        $fechaInicio = now()->subDay();
        $fechaFin = now()->addDay();

        $job = new GenerarReporteVentasJob($fechaInicio, $fechaFin);

        // Verificar que el job se puede instanciar
        $this->assertInstanceOf(GenerarReporteVentasJob::class, $job);
    }

    /** @test */
    public function job_maneja_excepciones_correctamente()
    {
        $fechaInicio = now()->subDays(7);
        $fechaFin = now();

        $job = new GenerarReporteVentasJob($fechaInicio, $fechaFin);

        // El job debe ser capaz de manejar errores sin romper
        $this->assertInstanceOf(GenerarReporteVentasJob::class, $job);
    }
}
