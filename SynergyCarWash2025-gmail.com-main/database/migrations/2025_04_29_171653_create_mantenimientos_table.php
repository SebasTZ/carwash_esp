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
        Schema::create('mantenimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->string('placa', 20);
            $table->string('modelo', 100);
            $table->string('tipo_vehiculo', 50);
            $table->dateTime('fecha_ingreso');
            $table->dateTime('fecha_entrega_estimada')->nullable();
            $table->dateTime('fecha_entrega_real')->nullable();
            $table->string('tipo_servicio', 100);
            $table->text('descripcion_trabajo');
            $table->text('observaciones')->nullable();
            $table->decimal('costo_estimado', 10, 2)->nullable();
            $table->decimal('costo_final', 10, 2)->nullable();
            $table->string('mecanico_responsable', 100)->nullable();
            $table->string('estado', 20)->default('recibido'); // recibido, en_proceso, terminado, entregado
            $table->boolean('pagado')->default(false);
            $table->foreignId('venta_id')->nullable()->constrained('ventas')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mantenimientos');
    }
};
