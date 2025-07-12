<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarjetas_regalo', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->decimal('valor_inicial', 10, 2);
            $table->decimal('saldo_actual', 10, 2);
            $table->enum('estado', ['activa', 'usada', 'vencida'])->default('activa');
            $table->date('fecha_venta');
            $table->date('fecha_vencimiento')->nullable();
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->timestamps();

            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarjetas_regalo');
    }
};
