<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        Schema::table('control_lavados', function (Blueprint $table) {
            $table->foreignId('lavador_id')->nullable()->constrained('lavadores');
            $table->foreignId('tipo_vehiculo_id')->nullable()->constrained('tipos_vehiculo');
        });
    }

    public function down()
    {
        Schema::table('control_lavados', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['lavador_id']);
                $table->dropForeign(['tipo_vehiculo_id']);
            }
            $table->dropColumn(['lavador_id', 'tipo_vehiculo_id']);
        });
    }
};
