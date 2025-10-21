<?php

namespace App\Repositories;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProductoRepository
{
    /**
     * Obtiene productos para formulario de venta con caché
     * 
     * OPTIMIZACIÓN: Cache de 1 hora (3600 segundos) para reducir queries
     * Se invalida automáticamente mediante ProductoObserver
     *
     * @return Collection
     */
    public function obtenerParaVenta(): Collection
    {
        return Cache::remember('productos_para_venta', 3600, function () {
            // Productos normales con último precio
            $productosNormales = $this->obtenerProductosNormalesConPrecio();

            // Servicios de lavado
            $serviciosLavado = Producto::select([
                'id',
                'nombre',
                'codigo',
                'stock',
                'precio_venta',
                'es_servicio_lavado'
            ])
                ->where('estado', 1)
                ->where('es_servicio_lavado', true)
                ->get();

            return $productosNormales->concat($serviciosLavado);
        });
    }

    /**
     * Obtiene productos normales con su último precio de compra
     *
     * @return Collection
     */
    private function obtenerProductosNormalesConPrecio(): Collection
    {
        // Usar una subconsulta optimizada
        $ultimosPrecios = DB::table('compra_producto')
            ->select('producto_id', DB::raw('MAX(id) as ultimo_id'))
            ->groupBy('producto_id');

        return Producto::select([
            'productos.id',
            'productos.nombre',
            'productos.codigo',
            'productos.stock',
            'productos.es_servicio_lavado',
            DB::raw('COALESCE(cp.precio_venta, productos.precio_venta, 0) as precio_venta')
        ])
            ->leftJoin('compra_producto as cp', function ($join) use ($ultimosPrecios) {
                $join->on('productos.id', '=', 'cp.producto_id')
                    ->whereIn('cp.id', function ($query) use ($ultimosPrecios) {
                        $query->select('ultimo_id')
                            ->fromSub($ultimosPrecios, 'ultimos');
                    });
            })
            ->where('productos.estado', 1)
            ->where('productos.stock', '>', 0)
            ->where('productos.es_servicio_lavado', false)
            ->get();
    }

    /**
     * Obtiene productos con filtros y paginación
     *
     * @param array $filtros
     * @return LengthAwarePaginator
     */
    public function obtenerConFiltros(array $filtros = []): LengthAwarePaginator
    {
        return Producto::query()
            ->with([
                'categorias.caracteristica',
                'marca.caracteristica',
                'presentacione.caracteristica'
            ])
            ->when($filtros['estado'] ?? null, fn($q, $estado) => $q->where('estado', $estado))
            ->when($filtros['categoria_id'] ?? null, fn($q, $catId) => 
                $q->whereHas('categorias', fn($qc) => $qc->where('categorias.id', $catId))
            )
            ->when($filtros['marca_id'] ?? null, fn($q, $marcaId) => $q->where('marca_id', $marcaId))
            ->when($filtros['stock_bajo'] ?? false, fn($q) => $q->where('stock', '<=', 10))
            ->when($filtros['buscar'] ?? null, fn($q, $buscar) => 
                $q->where(function($query) use ($buscar) {
                    $query->where('nombre', 'LIKE', "%{$buscar}%")
                          ->orWhere('codigo', 'LIKE', "%{$buscar}%");
                })
            )
            ->latest()
            ->paginate($filtros['per_page'] ?? 15);
    }

    /**
     * Obtiene productos con stock bajo
     *
     * @param int $limite
     * @return Collection
     */
    public function obtenerStockBajo(int $limite = 10): Collection
    {
        return Cache::remember("productos:stock_bajo:{$limite}", 300, function () use ($limite) {
            return Producto::with(['marca.caracteristica', 'categorias.caracteristica'])
                ->where('estado', 1)
                ->where('es_servicio_lavado', false)
                ->where('stock', '<=', $limite)
                ->where('stock', '>', 0)
                ->orderBy('stock', 'asc')
                ->get();
        });
    }

    /**
     * Busca productos por término
     *
     * @param string $termino
     * @param int $limite
     * @return Collection
     */
    public function buscar(string $termino, int $limite = 10): Collection
    {
        return Producto::where('estado', 1)
            ->where(function ($query) use ($termino) {
                $query->where('nombre', 'LIKE', "%{$termino}%")
                    ->orWhere('codigo', 'LIKE', "%{$termino}%");
            })
            ->with(['marca.caracteristica'])
            ->limit($limite)
            ->get();
    }

    /**
     * Obtiene productos más vendidos
     *
     * @param int $limite
     * @return Collection
     */
    public function obtenerMasVendidos(int $limite = 10): Collection
    {
        return Cache::remember("productos:mas_vendidos:{$limite}", 3600, function () use ($limite) {
            return Producto::select('productos.*', DB::raw('SUM(producto_venta.cantidad) as total_vendido'))
                ->join('producto_venta', 'productos.id', '=', 'producto_venta.producto_id')
                ->join('ventas', 'producto_venta.venta_id', '=', 'ventas.id')
                ->where('ventas.estado', 1)
                ->groupBy('productos.id')
                ->orderByDesc('total_vendido')
                ->limit($limite)
                ->get();
        });
    }

    /**
     * Limpia el caché de productos
     * 
     * OPTIMIZACIÓN: Invalida cache cuando productos cambian
     * Se llama automáticamente desde ProductoObserver
     *
     * @return void
     */
    public function limpiarCache(): void
    {
        // Limpiar ambas keys (legacy y nueva)
        Cache::forget('productos:para_venta');
        Cache::forget('productos_para_venta');
        
        // Tags solo si el driver lo soporta (Redis, Memcached)
        try {
            Cache::tags(['productos'])->flush();
        } catch (\Exception $e) {
            // Ignorar si el driver no soporta tags (file, database)
        }
    }
}
