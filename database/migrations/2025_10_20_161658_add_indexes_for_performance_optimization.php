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
            // Skip indexes on FK columns - they already have implicit indexes from foreignId()
            // cliente_id, user_id, comprobante_id are all FKs
            if (!Schema::hasIndex('ventas', 'idx_ventas_estado')) {
                $table->index('estado', 'idx_ventas_estado');
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
            if (!Schema::hasIndex('compras', 'idx_compras_fecha_hora')) {
                $table->index('fecha_hora', 'idx_compras_fecha_hora');
            }
            // Skip índices en FKs (comprobante_id, proveedore_id) - ya existen implícitamente
            if (!Schema::hasIndex('compras', 'idx_compras_estado')) {
                $table->index('estado', 'idx_compras_estado');
            }
            if (!Schema::hasIndex('compras', 'idx_compras_fecha_estado')) {
                $table->index(['fecha_hora', 'estado'], 'idx_compras_fecha_estado');
            }
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
            // Skip producto_id - it's a FK
            // Skip usuario_id - it's a FK
            
            // Índice para filtrar por tipo de movimiento
            if (!Schema::hasIndex('stock_movimientos', 'idx_stock_movimientos_tipo')) {
                $table->index('tipo', 'idx_stock_movimientos_tipo');
            }
            
            // Índice por fecha de creación (auditorías por rango)
            if (!Schema::hasIndex('stock_movimientos', 'idx_stock_movimientos_created')) {
                $table->index('created_at', 'idx_stock_movimientos_created');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            if (Schema::hasIndex('ventas', 'idx_ventas_fecha_hora')) {
                $table->dropIndex('idx_ventas_fecha_hora');
            }
            // NOT dropping FK indexes
            if (Schema::hasIndex('ventas', 'idx_ventas_estado')) {
                $table->dropIndex('idx_ventas_estado');
            }
            if (Schema::hasIndex('ventas', 'idx_ventas_fecha_estado')) {
                $table->dropIndex('idx_ventas_fecha_estado');
            }
            if (Schema::hasIndex('ventas', 'idx_ventas_numero_comprobante')) {
                $table->dropIndex('idx_ventas_numero_comprobante');
            }
        });

        Schema::table('compras', function (Blueprint $table) {
            if (Schema::hasIndex('compras', 'idx_compras_fecha_hora')) {
                $table->dropIndex('idx_compras_fecha_hora');
            }
            // NOT dropping FK indexes
            if (Schema::hasIndex('compras', 'idx_compras_estado')) {
                $table->dropIndex('idx_compras_estado');
            }
            if (Schema::hasIndex('compras', 'idx_compras_fecha_estado')) {
                $table->dropIndex('idx_compras_fecha_estado');
            }
        });

        Schema::table('productos', function (Blueprint $table) {
            if (Schema::hasIndex('productos', 'idx_productos_nombre')) {
                $table->dropIndex('idx_productos_nombre');
            }
            if (Schema::hasIndex('productos', 'idx_productos_estado')) {
                $table->dropIndex('idx_productos_estado');
            }
            if (Schema::hasIndex('productos', 'idx_productos_stock')) {
                $table->dropIndex('idx_productos_stock');
            }
            if (Schema::hasIndex('productos', 'idx_productos_estado_stock')) {
                $table->dropIndex('idx_productos_estado_stock');
            }
        });

        Schema::table('clientes', function (Blueprint $table) {
            if (Schema::hasIndex('clientes', 'idx_clientes_lavados')) {
                $table->dropIndex('idx_clientes_lavados');
            }
        });

        Schema::table('stock_movimientos', function (Blueprint $table) {
            // NOT dropping FK indexes (producto_id, usuario_id)
            if (Schema::hasIndex('stock_movimientos', 'idx_stock_movimientos_tipo')) {
                $table->dropIndex('idx_stock_movimientos_tipo');
            }
            if (Schema::hasIndex('stock_movimientos', 'idx_stock_movimientos_created')) {
                $table->dropIndex('idx_stock_movimientos_created');
            }
        });
    }
};
