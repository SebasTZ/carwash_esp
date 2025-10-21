<?php

namespace App\Services;

use App\Models\ControlLavado;
use App\Repositories\ControlLavadoRepository;
use App\Exceptions\LavadoYaIniciadoException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ControlLavadoService
{
    protected $controlLavadoRepository;
    protected $auditoriaService;
    protected $comisionService;

    public function __construct(
        ControlLavadoRepository $controlLavadoRepository,
        AuditoriaService $auditoriaService,
        ComisionService $comisionService
    ) {
        $this->controlLavadoRepository = $controlLavadoRepository;
        $this->auditoriaService = $auditoriaService;
        $this->comisionService = $comisionService;
    }

    /**
     * Asigna un lavador y tipo de vehículo a un lavado
     */
    public function asignarLavador(
        int $lavadoId,
        int $lavadorId,
        int $tipoVehiculoId,
        ?string $motivo,
        int $usuarioId
    ): ControlLavado {
        $lavado = $this->controlLavadoRepository->find($lavadoId);

        if (!$lavado) {
            throw new \Exception("Lavado no encontrado");
        }

        // Validación de negocio
        if ($lavado->inicio_lavado) {
            throw new LavadoYaIniciadoException($lavadoId);
        }

        return DB::transaction(function () use (
            $lavado,
            $lavadorId,
            $tipoVehiculoId,
            $motivo,
            $usuarioId
        ) {
            $lavadorAnterior = $lavado->lavador_id;

            // Actualizar lavado
            $lavadoActualizado = $this->controlLavadoRepository->update($lavado->id, [
                'lavador_id' => $lavadorId,
                'tipo_vehiculo_id' => $tipoVehiculoId,
            ]);

            // Registrar auditoría solo si hubo cambio de lavador
            if ($lavadorAnterior !== null && $lavadorAnterior != $lavadorId) {
                $this->auditoriaService->registrarCambioLavador(
                    controlLavadoId: $lavado->id,
                    lavadorAnteriorId: $lavadorAnterior,
                    lavadorNuevoId: $lavadorId,
                    usuarioId: $usuarioId,
                    motivo: $motivo ?? 'Cambio de lavador'
                );
            }

            // Logging
            Log::channel('lavados')->info('Lavador asignado exitosamente', [
                'lavado_id' => $lavado->id,
                'lavador_anterior_id' => $lavadorAnterior,
                'lavador_nuevo_id' => $lavadorId,
                'tipo_vehiculo_id' => $tipoVehiculoId,
                'usuario_id' => $usuarioId,
                'timestamp' => now()->toISOString(),
            ]);

            return $lavadoActualizado;
        });
    }

    /**
     * Inicia el proceso de lavado
     */
    public function iniciarLavado(int $lavadoId, int $usuarioId): ControlLavado
    {
        $lavado = $this->controlLavadoRepository->find($lavadoId);

        if (!$lavado) {
            throw new \Exception("Lavado no encontrado");
        }

        // Validar que no haya sido iniciado previamente
        if ($lavado->inicio_lavado) {
            throw new \Exception("El lavado ya fue iniciado.");
        }

        // Validar que tenga lavador asignado
        if (!$lavado->lavador_id) {
            throw new \Exception("No se puede iniciar el lavado sin lavador asignado.");
        }

        $lavadoActualizado = $this->controlLavadoRepository->update($lavado->id, [
            'inicio_lavado' => now(),
            'estado' => 'En proceso',
        ]);

        Log::channel('lavados')->info('Lavado iniciado', [
            'lavado_id' => $lavado->id,
            'lavador_id' => $lavado->lavador_id,
            'usuario_id' => $usuarioId,
            'inicio_lavado' => now()->toISOString(),
        ]);

        return $lavadoActualizado;
    }

    /**
     * Finaliza el lavado exterior
     */
    public function finalizarLavado(int $lavadoId): ControlLavado
    {
        $lavado = $this->controlLavadoRepository->find($lavadoId);

        if (!$lavado) {
            throw new \Exception("Lavado no encontrado");
        }

        if (!$lavado->inicio_lavado) {
            throw new \Exception("El lavado no ha sido iniciado.");
        }

        if ($lavado->fin_lavado) {
            throw new \Exception("El lavado ya fue finalizado.");
        }

        $lavadoActualizado = $this->controlLavadoRepository->update($lavado->id, [
            'fin_lavado' => now(),
        ]);

        Log::channel('lavados')->info('Lavado exterior finalizado', [
            'lavado_id' => $lavado->id,
            'fin_lavado' => now()->toISOString(),
            'duracion_minutos' => now()->diffInMinutes($lavado->inicio_lavado),
        ]);

        return $lavadoActualizado;
    }

    /**
     * Inicia el lavado interior
     */
    public function iniciarInterior(int $lavadoId): ControlLavado
    {
        $lavado = $this->controlLavadoRepository->find($lavadoId);

        if (!$lavado) {
            throw new \Exception("Lavado no encontrado");
        }

        if (!$lavado->fin_lavado) {
            throw new \Exception("Debe finalizar el lavado exterior primero.");
        }

        if ($lavado->inicio_interior) {
            throw new \Exception("El lavado interior ya fue iniciado.");
        }

        $lavadoActualizado = $this->controlLavadoRepository->update($lavado->id, [
            'inicio_interior' => now(),
        ]);

        Log::channel('lavados')->info('Lavado interior iniciado', [
            'lavado_id' => $lavado->id,
            'inicio_interior' => now()->toISOString(),
        ]);

        return $lavadoActualizado;
    }

    /**
     * Finaliza el lavado interior y completa todo el proceso
     */
    public function finalizarInterior(int $lavadoId): ControlLavado
    {
        $lavado = $this->controlLavadoRepository->find($lavadoId);

        if (!$lavado) {
            throw new \Exception("Lavado no encontrado");
        }

        if (!$lavado->inicio_interior) {
            throw new \Exception("El lavado interior no ha sido iniciado.");
        }

        if ($lavado->fin_interior) {
            throw new \Exception("El lavado interior ya fue finalizado.");
        }

        return DB::transaction(function () use ($lavado) {
            $lavadoActualizado = $this->controlLavadoRepository->update($lavado->id, [
                'fin_interior' => now(),
                'hora_final' => now(),
                'estado' => 'Terminado',
            ]);

            // ✅ CORRECCIÓN BUG #1: Comisión Duplicada
            // El Observer (ControlLavadoObserver) ya se encarga de registrar la comisión automáticamente
            // cuando detecta el cambio en 'fin_interior'. Eliminar la llamada manual evita duplicados.
            // Ver: app/Observers/ControlLavadoObserver.php línea 38
            //
            // ANTES (DUPLICABA):
            // $this->comisionService->registrarComisionLavado($lavadoActualizado);

            Log::channel('lavados')->info('Lavado completado', [
                'lavado_id' => $lavado->id,
                'hora_final' => now()->toISOString(),
                'tiempo_total_minutos' => now()->diffInMinutes($lavado->hora_llegada),
            ]);

            return $lavadoActualizado;
        });
    }

    /**
     * Elimina un lavado
     */
    public function eliminarLavado(int $lavadoId, int $usuarioId): bool
    {
        $lavado = $this->controlLavadoRepository->find($lavadoId);

        if (!$lavado) {
            throw new \Exception("Lavado no encontrado");
        }

        $resultado = $this->controlLavadoRepository->delete($lavadoId);

        Log::channel('lavados')->warning('Lavado eliminado', [
            'lavado_id' => $lavadoId,
            'usuario_id' => $usuarioId,
            'timestamp' => now()->toISOString(),
        ]);

        return $resultado;
    }

    /**
     * Obtiene lavados con filtros
     */
    public function obtenerLavadosConFiltros(array $filtros = [], int $porPagina = 15)
    {
        return $this->controlLavadoRepository->getWithFilters($filtros, $porPagina);
    }

    /**
     * Obtiene un lavado con sus relaciones
     */
    public function obtenerLavadoConRelaciones(int $lavadoId, array $relaciones = [])
    {
        return $this->controlLavadoRepository->findOrFail($lavadoId, $relaciones);
    }
}
