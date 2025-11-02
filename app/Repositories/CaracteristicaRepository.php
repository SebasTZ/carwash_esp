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
        return Marca::with('caracteristica')
            ->whereHas('caracteristica', fn($q) => $q->where('estado', 1))
            ->get()
            ->map(fn($marca) => (object)[
                'id' => $marca->id,
                'nombre' => $marca->caracteristica->nombre ?? 'Sin nombre'
            ]);
    }

    /**
     * Obtiene presentaciones activas con caché
     */
    public function obtenerPresentacionesActivas(): Collection
    {
        return Presentacione::with('caracteristica')
            ->whereHas('caracteristica', fn($q) => $q->where('estado', 1))
            ->get()
            ->map(fn($pres) => (object)[
                'id' => $pres->id,
                'nombre' => $pres->caracteristica->nombre ?? 'Sin nombre'
            ]);
    }

    /**
     * Obtiene categorías activas con caché
     */
    public function obtenerCategoriasActivas(): Collection
    {
        return Categoria::with('caracteristica')
            ->whereHas('caracteristica', fn($q) => $q->where('estado', 1))
            ->get()
            ->map(fn($cat) => (object)[
                'id' => $cat->id,
                'nombre' => $cat->caracteristica->nombre ?? 'Sin nombre'
            ]);
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
