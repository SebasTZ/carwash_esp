<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->unsignedBigInteger('tarjeta_regalo_id')->nullable()->after('horario_lavado');
            $table->boolean('lavado_gratis')->default(false)->after('tarjeta_regalo_id');
            $table->foreign('tarjeta_regalo_id')->references('id')->on('tarjetas_regalo')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign(['tarjeta_regalo_id']);
            $table->dropColumn(['tarjeta_regalo_id', 'lavado_gratis']);
        });
    }
};
