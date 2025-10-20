<?php

namespace App\Jobs;

use App\Exports\ComprasExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class GenerarReporteComprasJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300;

    public function __construct(
        public string $tipo,
        public ?string $fechaInicio = null,
        public ?string $fechaFin = null,
        public ?int $usuarioId = null
    ) {}

    public function handle(): void
    {
        Log::info('Iniciando generación de reporte de compras', [
            'tipo' => $this->tipo,
            'fecha_inicio' => $this->fechaInicio,
            'fecha_fin' => $this->fechaFin,
        ]);

        try {
            $nombreArchivo = sprintf(
                'reportes/compras_%s_%s.xlsx',
                $this->tipo,
                now()->format('Y-m-d_His')
            );

            Excel::store(
                new ComprasExport($this->tipo, $this->fechaInicio, $this->fechaFin),
                $nombreArchivo,
                'local'
            );

            Log::info('Reporte de compras generado exitosamente', [
                'tipo' => $this->tipo,
                'archivo' => $nombreArchivo,
                'tamaño' => Storage::size($nombreArchivo),
            ]);

        } catch (\Exception $e) {
            Log::error('Error al generar reporte de compras', [
                'tipo' => $this->tipo,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Job de reporte de compras falló', [
            'tipo' => $this->tipo,
            'error' => $exception->getMessage(),
        ]);
    }
}
