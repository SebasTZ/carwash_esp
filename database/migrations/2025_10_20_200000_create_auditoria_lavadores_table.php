<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateAuditoriaLavadoresTable extends Migration
{
    public function up()
    {
        Schema::create('auditoria_lavadores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('control_lavado_id');
            $table->unsignedBigInteger('lavador_id_anterior')->nullable();
            $table->unsignedBigInteger('lavador_id_nuevo');
            $table->unsignedBigInteger('usuario_id');
            $table->string('motivo')->nullable();
            $table->timestamp('fecha_cambio')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('control_lavado_id')->references('id')->on('control_lavados');
            $table->foreign('lavador_id_anterior')->references('id')->on('lavadores');
            $table->foreign('lavador_id_nuevo')->references('id')->on('lavadores');
            $table->foreign('usuario_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('auditoria_lavadores');
    }
}
