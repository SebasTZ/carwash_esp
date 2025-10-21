<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            if (!Schema::hasIndex('ventas', 'idx_ventas_fecha_hora')) {
                $table->index('fecha_hora', 'idx_ventas_fecha_hora');
            }
            if (!Schema::hasIndex('ventas', 'idx_ventas_cliente_id')) {
                $table->index('cliente_id', 'idx_ventas_cliente_id');
            }
            if (!Schema::hasIndex('ventas', 'idx_ventas_estado')) {
                $table->index('estado', 'idx_ventas_estado');
            }
            if (!Schema::hasIndex('ventas', 'idx_ventas_user_id')) {
                $table->index('user_id', 'idx_ventas_user_id');
            }
            if (!Schema::hasIndex('ventas', 'idx_ventas_fecha_estado')) {
                $table->index(['fecha_hora', 'estado'], 'idx_ventas_fecha_estado');
            }
            if (!Schema::hasIndex('ventas', 'idx_ventas_numero_comprobante')) {
                $table->index('numero_comprobante', 'idx_ventas_numero_comprobante');
            }
        });

        Schema::table('compras', function (Blueprint $table) {
            // Índices similares para compras
            $table->index('fecha_hora', 'idx_compras_fecha_hora');
            $table->index('proveedore_id', 'idx_compras_proveedore_id');
            $table->index('estado', 'idx_compras_estado');
            $table->index(['fecha_hora', 'estado'], 'idx_compras_fecha_estado');
        });

        Schema::table('productos', function (Blueprint $table) {
            // Índice para búsquedas por nombre
            $table->index('nombre', 'idx_productos_nombre');
            
            // Índice para filtrar por estado
            $table->index('estado', 'idx_productos_estado');
            
            // Índice para alertas de stock bajo
            $table->index('stock', 'idx_productos_stock');
            
            // Índice compuesto para productos activos con stock
            $table->index(['estado', 'stock'], 'idx_productos_estado_stock');
        });

        Schema::table('clientes', function (Blueprint $table) {
            // Índice para fidelización (lavados acumulados)
            $table->index('lavados_acumulados', 'idx_clientes_lavados');
        });

        Schema::table('stock_movimientos', function (Blueprint $table) {
            // Índice para auditoría por producto
            $table->index('producto_id', 'idx_stock_movimientos_producto');
            
            // Índice para auditoría por usuario
            $table->index('usuario_id', 'idx_stock_movimientos_usuario');
            
            // Índice para filtrar por tipo de movimiento
            $table->index('tipo', 'idx_stock_movimientos_tipo');
            
            // Índice por fecha de creación (auditorías por rango)
            $table->index('created_at', 'idx_stock_movimientos_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropIndex('idx_ventas_fecha_hora');
            $table->dropIndex('idx_ventas_cliente_id');
            $table->dropIndex('idx_ventas_estado');
            $table->dropIndex('idx_ventas_user_id');
            $table->dropIndex('idx_ventas_fecha_estado');
            $table->dropIndex('idx_ventas_numero_comprobante');
        });

        Schema::table('compras', function (Blueprint $table) {
            $table->dropIndex('idx_compras_fecha_hora');
            $table->dropIndex('idx_compras_proveedore_id');
            $table->dropIndex('idx_compras_estado');
            $table->dropIndex('idx_compras_fecha_estado');
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->dropIndex('idx_productos_nombre');
            $table->dropIndex('idx_productos_estado');
            $table->dropIndex('idx_productos_stock');
            $table->dropIndex('idx_productos_estado_stock');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->dropIndex('idx_clientes_lavados');
        });

        Schema::table('stock_movimientos', function (Blueprint $table) {
            $table->dropIndex('idx_stock_movimientos_producto');
            $table->dropIndex('idx_stock_movimientos_usuario');
            $table->dropIndex('idx_stock_movimientos_tipo');
            $table->dropIndex('idx_stock_movimientos_created');
        });
    }
};
