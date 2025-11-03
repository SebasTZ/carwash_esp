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
        Schema::table('estacionamientos', function (Blueprint $table) {
            $table->boolean('pagado_adelantado')->default(false)->after('estado');
            $table->decimal('monto_pagado_adelantado', 8, 2)->nullable()->after('pagado_adelantado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estacionamientos', function (Blueprint $table) {
            $table->dropColumn(['pagado_adelantado', 'monto_pagado_adelantado']);
        });
    }
};
