<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\StockMovimiento;
use App\Events\StockBajoEvent;
use App\Exceptions\StockInsuficienteException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class StockService
{
    /**
     * Descuenta stock del producto con validación y auditoría
     *
     * @param Producto $producto
     * @param int $cantidad
     * @param string $referencia Descripción del movimiento (ej: "Venta #0001")
     * @return void
     * @throws StockInsuficienteException
     */
    public function descontarStock(Producto $producto, int $cantidad, string $referencia): void
    {
        $this->ajustarStock($producto, -$cantidad, 'venta', $referencia);
    }

    /**
     * Incrementa stock del producto (usado en compras o devoluciones)
     *
     * @param Producto $producto
     * @param int $cantidad
     * @param string $referencia
     * @return void
     */
    public function incrementarStock(Producto $producto, int $cantidad, string $referencia): void
    {
        $this->ajustarStock($producto, $cantidad, 'compra', $referencia);
    }

    /**
     * Ajusta el stock del producto de forma segura con lock pesimista
     *
     * @param Producto $producto
     * @param int $cantidad Positivo para incrementar, negativo para decrementar
     * @param string $tipo Tipo de movimiento: 'venta', 'compra', 'ajuste', 'devolucion'
     * @param string $referencia
     * @return void
     * @throws StockInsuficienteException
     */
    public function ajustarStock(Producto $producto, int $cantidad, string $tipo, string $referencia): void
    {
        DB::transaction(function () use ($producto, $cantidad, $tipo, $referencia) {
            // Lock pesimista para evitar race conditions
            $producto = Producto::lockForUpdate()->findOrFail($producto->id);

            $stockAnterior = $producto->stock;
            $stockNuevo = $stockAnterior + $cantidad;

            // Validar stock suficiente
            if ($stockNuevo < 0) {
                throw new StockInsuficienteException(
                    "Stock insuficiente para {$producto->nombre}. Disponible: {$stockAnterior}, Requerido: " . abs($cantidad)
                );
            }

            // Actualizar stock
            $producto->stock = $stockNuevo;
            $producto->save();

            // Registrar auditoría
            $this->registrarMovimiento($producto, $cantidad, $tipo, $referencia, $stockAnterior, $stockNuevo);

            // Limpiar caché de productos
            Cache::forget("producto_{$producto->id}_stock");
            Cache::tags(['productos'])->flush();

            // Verificar alerta de stock bajo
            $this->verificarStockBajo($producto);
        });
    }

    /**
     * Registra el movimiento de stock en auditoría
     */
    private function registrarMovimiento(
        Producto $producto,
        int $cantidad,
        string $tipo,
        string $referencia,
        int $stockAnterior,
        int $stockNuevo
    ): void {
        // Si no existe la tabla, puedes comentar esto temporalmente
        // Crear la migración con: php artisan make:migration create_stock_movimientos_table
        
        try {
            StockMovimiento::create([
                'producto_id' => $producto->id,
                'tipo' => $tipo,
                'cantidad' => $cantidad,
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $stockNuevo,
                'referencia' => $referencia,
                'usuario_id' => auth()->id(),
            ]);
        } catch (\Exception $e) {
            // Si la tabla no existe, solo loguear
            Log::channel('stock')->info("Movimiento de stock: {$producto->nombre} {$tipo} {$cantidad} unidades. Ref: {$referencia}");
        }
    }

    /**
     * Verifica si el stock está bajo el mínimo y dispara evento
     */
    private function verificarStockBajo(Producto $producto): void
    {
        // Puedes definir el stock mínimo como campo en la tabla productos
        // o usar un valor por defecto
        $stockMinimo = $producto->stock_minimo ?? 10;

        if ($producto->stock <= $stockMinimo && $producto->stock > 0) {
            Log::channel('stock')->warning('Stock bajo detectado', [
                'producto_id' => $producto->id,
                'producto' => $producto->nombre,
                'stock_actual' => $producto->stock,
                'stock_minimo' => $stockMinimo,
            ]);
            
            event(new StockBajoEvent($producto));
        }
    }

    /**
     * Obtiene productos con stock bajo
     *
     * @param int $limite Stock considerado como bajo
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function obtenerProductosStockBajo(int $limite = 10): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('productos:stock_bajo', 300, function () use ($limite) {
            return Producto::where('estado', 1)
                ->where('es_servicio_lavado', false)
                ->where('stock', '<=', $limite)
                ->where('stock', '>', 0)
                ->with(['marca.caracteristica', 'categorias.caracteristica'])
                ->get();
        });
    }

    /**
     * Verifica si hay stock suficiente para múltiples productos
     *
     * @param array $productos Array con ['producto_id' => cantidad]
     * @return bool
     */
    public function verificarStockSuficiente(array $productos): bool
    {
        foreach ($productos as $productoId => $cantidad) {
            $producto = Producto::find($productoId);

            if (!$producto || $producto->es_servicio_lavado) {
                continue;
            }

            if ($producto->stock < $cantidad) {
                return false;
            }
        }

        return true;
    }

    /**
     * Obtiene el historial de movimientos de un producto
     *
     * @param Producto $producto
     * @param int $limite
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function obtenerHistorialMovimientos(Producto $producto, int $limite = 20)
    {
        return StockMovimiento::where('producto_id', $producto->id)
            ->with('usuario')
            ->latest()
            ->paginate($limite);
    }
}
