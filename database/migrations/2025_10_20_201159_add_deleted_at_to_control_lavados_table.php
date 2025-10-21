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
        Schema::table('control_lavados', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('control_lavados', function (Blueprint $table) {
            // No hacer drop en tests para evitar conflictos con índices
            if (app()->environment() !== 'testing') {
                $table->dropSoftDeletes();
            }
        });
    }
};
