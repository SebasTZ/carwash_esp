<?php

namespace App\Services;

use App\Models\AuditoriaLavador;
use App\Repositories\AuditoriaLavadorRepository;
use Illuminate\Support\Facades\Log;

class AuditoriaService
{
    protected $auditoriaRepository;

    public function __construct(AuditoriaLavadorRepository $auditoriaRepository)
    {
        $this->auditoriaRepository = $auditoriaRepository;
    }

    /**
     * Registra un cambio de lavador
     */
    public function registrarCambioLavador(
        int $controlLavadoId,
        ?int $lavadorAnteriorId,
        int $lavadorNuevoId,
        int $usuarioId,
        ?string $motivo = null
    ): AuditoriaLavador {
        $auditoria = $this->auditoriaRepository->create([
            'control_lavado_id' => $controlLavadoId,
            'lavador_id_anterior' => $lavadorAnteriorId,
            'lavador_id_nuevo' => $lavadorNuevoId,
            'usuario_id' => $usuarioId,
            'motivo' => $motivo ?? 'Cambio de lavador',
            'fecha_cambio' => now(),
        ]);

        Log::channel('auditoria')->info('Cambio de lavador registrado', [
            'auditoria_id' => $auditoria->id,
            'control_lavado_id' => $controlLavadoId,
            'lavador_anterior_id' => $lavadorAnteriorId,
            'lavador_nuevo_id' => $lavadorNuevoId,
            'usuario_id' => $usuarioId,
            'motivo' => $motivo,
            'timestamp' => now()->toISOString(),
        ]);

        return $auditoria;
    }

    /**
     * Obtiene el historial de cambios de un lavado
     */
    public function obtenerHistorial(int $controlLavadoId)
    {
        return $this->auditoriaRepository->getByControlLavado($controlLavadoId);
    }

    /**
     * Obtiene cambios realizados por un usuario
     */
    public function obtenerPorUsuario(int $usuarioId)
    {
        return $this->auditoriaRepository->getByUsuario($usuarioId);
    }

    /**
     * Obtiene auditorías por rango de fechas
     */
    public function obtenerPorRangoFechas(string $fechaInicio, string $fechaFin)
    {
        return $this->auditoriaRepository->getByDateRange($fechaInicio, $fechaFin);
    }
}
