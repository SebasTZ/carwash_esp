<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('pagos_comisiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lavador_id')->constrained('lavadores');
            $table->decimal('monto_pagado', 8, 2);
            $table->date('desde');
            $table->date('hasta');
            $table->text('observacion')->nullable();
            $table->date('fecha_pago');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pagos_comisiones');
    }
};
