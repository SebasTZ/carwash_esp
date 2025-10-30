<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('tipos_vehiculo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->decimal('comision', 8, 2)->default(0);
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();
        });
    }

    public function down()
    {
        // En SQLite primero eliminar la tabla que depende (control_lavados)
        // antes de eliminar tipos_vehiculo
        if (config('database.default') === 'sqlite') {
            Schema::dropIfExists('control_lavados');
        }
        Schema::dropIfExists('tipos_vehiculo');
    }
};
