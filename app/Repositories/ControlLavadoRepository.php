<?php

namespace App\Repositories;

use App\Models\ControlLavado;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Collection;

class ControlLavadoRepository
{
    protected $model;
    protected $cacheTime = 300; // 5 minutos

    public function __construct(ControlLavado $model)
    {
        $this->model = $model;
    }

    /**
     * Encuentra un lavado por ID con relaciones
     */
    public function findOrFail(int $id, array $relations = []): ControlLavado
    {
        // Usar formato de clave consistente con tests
        $cacheKey = empty($relations) 
            ? "control_lavado:{$id}" 
            : "control_lavado_{$id}_" . md5(json_encode($relations));
        
        return Cache::remember($cacheKey, $this->cacheTime, function () use ($id, $relations) {
            $query = $this->model->newQuery();
            
            if (!empty($relations)) {
                $query->with($relations);
            }
            
            return $query->findOrFail($id);
        });
    }

    /**
     * Encuentra un lavado por ID sin caché
     */
    public function find(int $id): ?ControlLavado
    {
        return $this->model->find($id);
    }

    /**
     * Actualiza un lavado
     */
    public function update(int $id, array $data): ControlLavado
    {
        $lavado = $this->model->findOrFail($id);
        $lavado->update($data);
        
        // Invalidar caché
        $this->clearCache($id);
        
        return $lavado->fresh();
    }

    /**
     * Obtiene lavados con filtros
     */
    public function getWithFilters(array $filters = [], int $perPage = 15)
    {
        $query = $this->model->with(['venta', 'cliente', 'lavador', 'tipoVehiculo']);

        if (!empty($filters['lavador_id'])) {
            $query->where('lavador_id', $filters['lavador_id']);
        }

        if (!empty($filters['tipo_vehiculo_id'])) {
            $query->where('tipo_vehiculo_id', $filters['tipo_vehiculo_id']);
        }

        if (!empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        if (!empty($filters['fecha'])) {
            $query->whereDate('hora_llegada', $filters['fecha']);
        }

        return $query->orderBy('hora_llegada', 'desc')->paginate($perPage);
    }

    /**
     * Obtiene lavados por rango de fechas
     */
    public function getByDateRange(string $fechaInicio, string $fechaFin): Collection
    {
        return $this->model
            ->whereBetween('hora_llegada', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->with(['venta', 'cliente.persona'])
            ->get();
    }

    /**
     * Obtiene lavados del día
     */
    public function getToday(): Collection
    {
        return $this->model
            ->whereDate('hora_llegada', now()->toDateString())
            ->with(['venta', 'cliente.persona'])
            ->get();
    }

    /**
     * Obtiene lavados de la semana
     */
    public function getThisWeek(): Collection
    {
        return $this->model
            ->whereBetween('hora_llegada', [now()->startOfWeek(), now()->endOfWeek()])
            ->with(['venta', 'cliente.persona'])
            ->get();
    }

    /**
     * Obtiene lavados del mes
     */
    public function getThisMonth(): Collection
    {
        return $this->model
            ->whereMonth('hora_llegada', now()->month)
            ->with(['venta', 'cliente.persona'])
            ->get();
    }

    /**
     * Elimina un lavado
     */
    public function delete(int $id): bool
    {
        $lavado = $this->model->findOrFail($id);
        $this->clearCache($id);
        
        return $lavado->delete();
    }

    /**
     * Limpia el caché de un lavado específico
     */
    protected function clearCache(int $id): void
    {
        // Limpiar caché básico (formato usado en tests)
        Cache::forget("control_lavado:{$id}");
        
        // También limpiar formato antiguo por compatibilidad
        Cache::forget("control_lavado_{$id}");
        
        // Limpiar caché con posibles relaciones
        $possibleRelations = [
            ['venta', 'cliente'],
            ['auditoriaLavadores.lavadorAnterior', 'auditoriaLavadores.lavadorNuevo'],
            ['lavador', 'tipoVehiculo'],
        ];
        
        foreach ($possibleRelations as $relations) {
            $cacheKey = "control_lavado_{$id}_" . md5(json_encode($relations));
            Cache::forget($cacheKey);
        }
    }
}
