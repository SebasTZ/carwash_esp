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
        Schema::create('estacionamientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->string('placa', 10);
            $table->string('marca', 50);
            $table->string('modelo', 50);
            $table->string('telefono', 20);
            $table->decimal('tarifa_hora', 8, 2);
            $table->timestamp('hora_entrada');
            $table->timestamp('hora_salida')->nullable();
            $table->decimal('monto_total', 8, 2)->nullable();
            $table->enum('estado', ['ocupado', 'finalizado'])->default('ocupado');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estacionamientos');
    }
};
