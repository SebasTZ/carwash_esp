<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('fidelizacion', 'lavados_acumulados')) {
            Schema::table('fidelizacion', function (Blueprint $table) {
                $table->unsignedInteger('lavados_acumulados')->default(0)->after('puntos');
            });
        }

        if (!Schema::hasColumn('fidelizacion', 'fecha_canje')) {
            Schema::table('fidelizacion', function (Blueprint $table) {
                $table->timestamp('fecha_canje')->nullable()->after('lavados_acumulados');
            });
        }

        if (!Schema::hasColumn('fidelizacion', 'tipo')) {
            Schema::table('fidelizacion', function (Blueprint $table) {
                $table->string('tipo', 50)->nullable()->after('fecha_canje');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('fidelizacion', 'tipo')) {
            Schema::table('fidelizacion', function (Blueprint $table) {
                $table->dropColumn('tipo');
            });
        }

        if (Schema::hasColumn('fidelizacion', 'fecha_canje')) {
            Schema::table('fidelizacion', function (Blueprint $table) {
                $table->dropColumn('fecha_canje');
            });
        }

        if (Schema::hasColumn('fidelizacion', 'lavados_acumulados')) {
            Schema::table('fidelizacion', function (Blueprint $table) {
                $table->dropColumn('lavados_acumulados');
            });
        }
    }
};
