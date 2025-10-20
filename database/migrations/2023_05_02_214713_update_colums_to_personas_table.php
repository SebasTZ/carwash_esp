<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite no soporta dropForeign, usamos recreación de tabla
        if (DB::getDriverName() !== 'sqlite') {
            // MySQL/PostgreSQL: Eliminar llave foránea
            Schema::table('personas', function (Blueprint $table) {
                $table->dropForeign(['documento_id']);
                $table->dropColumn('documento_id');
            });

            // Crear una nueva llave foránea
            Schema::table('personas', function (Blueprint $table) {
                $table->foreignId('documento_id')->after('estado')->constrained('documentos')->onDelete('cascade');
            });
        }

        // Crear el campo numero_documento (compatible con todos los drivers)
        Schema::table('personas', function (Blueprint $table) {
            $table->string('numero_documento', 20)->after('documento_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar el campo numero_documento
        Schema::table('personas', function (Blueprint $table) {
            $table->dropColumn('numero_documento');
        });

        // Solo para MySQL/PostgreSQL
        if (DB::getDriverName() !== 'sqlite') {
            // Eliminar la nueva llave foránea
            Schema::table('personas', function (Blueprint $table) {
                $table->dropForeign(['documento_id']);
                $table->dropColumn('documento_id');
            });

            // Crear llave foránea
            Schema::table('personas', function (Blueprint $table) {
                $table->foreignId('documento_id')->after('estado')->unique()->constrained('documentos')->onDelete('cascade');
            });
        }
    }
};
