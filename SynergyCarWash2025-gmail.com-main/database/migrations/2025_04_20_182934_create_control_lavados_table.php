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
Schema::create('control_lavados', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('venta_id');
    $table->unsignedBigInteger('cliente_id');
    $table->string('lavador_nombre', 100)->nullable();
    $table->timestamp('hora_llegada');
    $table->dateTime('horario_estimado');
    $table->dateTime('inicio_lavado')->nullable();
    $table->dateTime('fin_lavado')->nullable();
    $table->dateTime('inicio_interior')->nullable();
    $table->dateTime('fin_interior')->nullable();
    $table->dateTime('hora_final')->nullable();
    $table->integer('tiempo_total')->nullable();
    $table->string('estado', 20)->default('En espera');
    $table->timestamps();

    $table->foreign('venta_id')->references('id')->on('ventas')->onDelete('cascade');
    $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
    // Eliminado foreign de lavador_id
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('control_lavados');
    }
};
