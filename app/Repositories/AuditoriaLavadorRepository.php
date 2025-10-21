<?php

namespace App\Repositories;

use App\Models\AuditoriaLavador;
use Illuminate\Database\Eloquent\Collection;

class AuditoriaLavadorRepository
{
    protected $model;

    public function __construct(AuditoriaLavador $model)
    {
        $this->model = $model;
    }

    /**
     * Crea un registro de auditoría
     */
    public function create(array $data): AuditoriaLavador
    {
        return $this->model->create($data);
    }

    /**
     * Obtiene auditorías de un lavado
     */
    public function getByControlLavado(int $controlLavadoId): Collection
    {
        return $this->model
            ->where('control_lavado_id', $controlLavadoId)
            ->with(['lavadorAnterior', 'lavadorNuevo', 'usuario'])
            ->orderBy('fecha_cambio', 'desc')
            ->get();
    }

    /**
     * Obtiene auditorías por usuario
     */
    public function getByUsuario(int $usuarioId): Collection
    {
        return $this->model
            ->where('usuario_id', $usuarioId)
            ->with(['controlLavado', 'lavadorAnterior', 'lavadorNuevo'])
            ->orderBy('fecha_cambio', 'desc')
            ->get();
    }

    /**
     * Obtiene auditorías por rango de fechas
     */
    public function getByDateRange(string $fechaInicio, string $fechaFin): Collection
    {
        return $this->model
            ->whereBetween('fecha_cambio', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->with(['controlLavado', 'lavadorAnterior', 'lavadorNuevo', 'usuario'])
            ->orderBy('fecha_cambio', 'desc')
            ->get();
    }
}
