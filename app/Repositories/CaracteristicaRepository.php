<?php

namespace App\Repositories;

use App\Models\Marca;
use App\Models\Presentacione;
use App\Models\Categoria;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CaracteristicaRepository
{
    /**
     * Obtiene marcas activas con caché
     */
    public function obtenerMarcasActivas(): Collection
    {
        return Cache::remember('marcas:activas', 3600, function() {
            return Marca::with('caracteristica')
                ->whereHas('caracteristica', fn($q) => $q->where('estado', 1))
                ->get()
                ->map(fn($marca) => [
                    'id' => $marca->id,
                    'nombre' => $marca->caracteristica->nombre ?? 'Sin nombre'
                ]);
        });
    }

    /**
     * Obtiene presentaciones activas con caché
     */
    public function obtenerPresentacionesActivas(): Collection
    {
        return Cache::remember('presentaciones:activas', 3600, function() {
            return Presentacione::with('caracteristica')
                ->whereHas('caracteristica', fn($q) => $q->where('estado', 1))
                ->get()
                ->map(fn($pres) => [
                    'id' => $pres->id,
                    'nombre' => $pres->caracteristica->nombre ?? 'Sin nombre'
                ]);
        });
    }

    /**
     * Obtiene categorías activas con caché
     */
    public function obtenerCategoriasActivas(): Collection
    {
        return Cache::remember('categorias:activas', 3600, function() {
            return Categoria::with('caracteristica')
                ->whereHas('caracteristica', fn($q) => $q->where('estado', 1))
                ->get()
                ->map(fn($cat) => [
                    'id' => $cat->id,
                    'nombre' => $cat->caracteristica->nombre ?? 'Sin nombre'
                ]);
        });
    }

    /**
     * Limpia el caché de características
     */
    public function limpiarCache(): void
    {
        Cache::forget('marcas:activas');
        Cache::forget('presentaciones:activas');
        Cache::forget('categorias:activas');
    }
}
