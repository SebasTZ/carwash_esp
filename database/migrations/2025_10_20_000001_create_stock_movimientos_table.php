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
        Schema::create('stock_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->enum('tipo', ['venta', 'compra', 'ajuste', 'devolucion']);
            $table->integer('cantidad'); // Positivo o negativo según el tipo
            $table->integer('stock_anterior');
            $table->integer('stock_nuevo');
            $table->string('referencia')->comment('Venta #0001, Compra #0005, etc');
            $table->foreignId('usuario_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Índices para consultas frecuentes
            $table->index('producto_id');
            $table->index('tipo');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movimientos');
    }
};
