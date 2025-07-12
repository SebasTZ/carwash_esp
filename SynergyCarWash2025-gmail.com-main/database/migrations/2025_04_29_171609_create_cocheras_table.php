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
        Schema::create('cocheras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->string('placa', 20);
            $table->string('modelo', 100);
            $table->string('color', 50);
            $table->string('tipo_vehiculo', 50);
            $table->dateTime('fecha_ingreso');
            $table->dateTime('fecha_salida')->nullable();
            $table->string('ubicacion', 50)->nullable(); // Número o posición en la cochera
            $table->decimal('tarifa_hora', 10, 2);
            $table->decimal('tarifa_dia', 10, 2)->nullable();
            $table->decimal('monto_total', 10, 2)->nullable();
            $table->text('observaciones')->nullable();
            $table->string('estado', 20)->default('activo'); // activo, finalizado, cancelado
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cocheras');
    }
};
