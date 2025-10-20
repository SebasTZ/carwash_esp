<?php

namespace App\Jobs;

use App\Exports\VentasExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class GenerarReporteVentasJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Número máximo de intentos
     */
    public $tries = 3;

    /**
     * Tiempo máximo de ejecución (en segundos)
     */
    public $timeout = 300; // 5 minutos

    /**
     * Constructor del job
     */
    public function __construct(
        public string $tipo,
        public ?string $fechaInicio = null,
        public ?string $fechaFin = null,
        public ?int $usuarioId = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Iniciando generación de reporte de ventas', [
            'tipo' => $this->tipo,
            'fecha_inicio' => $this->fechaInicio,
            'fecha_fin' => $this->fechaFin,
            'usuario_id' => $this->usuarioId,
        ]);

        try {
            // Generar nombre único para el archivo
            $nombreArchivo = sprintf(
                'reportes/ventas_%s_%s.xlsx',
                $this->tipo,
                now()->format('Y-m-d_His')
            );

            // Generar el reporte
            Excel::store(
                new VentasExport($this->tipo, $this->fechaInicio, $this->fechaFin),
                $nombreArchivo,
                'local'
            );

            Log::info('Reporte de ventas generado exitosamente', [
                'tipo' => $this->tipo,
                'archivo' => $nombreArchivo,
                'tamaño' => Storage::size($nombreArchivo),
            ]);

            // Aquí podrías:
            // 1. Enviar email con el archivo adjunto
            // 2. Crear una notificación en la BD
            // 3. Guardar referencia en tabla de reportes
            
            // Ejemplo de notificación (requiere tabla de notificaciones):
            /*
            if ($this->usuarioId) {
                Notification::create([
                    'user_id' => $this->usuarioId,
                    'type' => 'reporte_generado',
                    'data' => [
                        'titulo' => "Reporte de ventas {$this->tipo}",
                        'mensaje' => 'Tu reporte está listo para descargar',
                        'archivo' => $nombreArchivo,
                    ],
                ]);
            }
            */

        } catch (\Exception $e) {
            Log::error('Error al generar reporte de ventas', [
                'tipo' => $this->tipo,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e; // Re-lanzar para que Laravel reintente el job
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Job de reporte de ventas falló después de todos los intentos', [
            'tipo' => $this->tipo,
            'error' => $exception->getMessage(),
        ]);

        // Aquí podrías notificar al usuario que hubo un error
    }
}
