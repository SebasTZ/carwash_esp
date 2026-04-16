<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            $duplicados = DB::table('fidelizacion')
                ->select('cliente_id')
                ->groupBy('cliente_id')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('cliente_id');

            foreach ($duplicados as $clienteId) {
                $registros = DB::table('fidelizacion')
                    ->where('cliente_id', $clienteId)
                    ->orderByDesc(DB::raw('COALESCE(fecha_canje, updated_at, created_at)'))
                    ->orderByDesc('id')
                    ->get();

                $registroPrincipal = $registros->first();

                if ($registroPrincipal === null) {
                    continue;
                }

                $puntos = (float) $registros->max('puntos');
                $lavadosAcumulados = (int) $registros->max('lavados_acumulados');
                $fechaCanje = $registros
                    ->pluck('fecha_canje')
                    ->filter()
                    ->sort()
                    ->last();

                $tipo = $registros
                    ->pluck('tipo')
                    ->filter(fn ($value) => $value !== null && $value !== '')
                    ->first();

                DB::table('fidelizacion')
                    ->where('id', $registroPrincipal->id)
                    ->update([
                        'puntos' => $puntos,
                        'lavados_acumulados' => $lavadosAcumulados,
                        'fecha_canje' => $fechaCanje,
                        'tipo' => $tipo,
                        'updated_at' => now(),
                    ]);

                DB::table('fidelizacion')
                    ->where('cliente_id', $clienteId)
                    ->where('id', '!=', $registroPrincipal->id)
                    ->delete();
            }
        });

        if (!Schema::hasIndex('fidelizacion', 'fidelizacion_cliente_id_unique')) {
            Schema::table('fidelizacion', function (Blueprint $table): void {
                $table->unique('cliente_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('fidelizacion', 'fidelizacion_cliente_id_unique')) {
            Schema::table('fidelizacion', function (Blueprint $table): void {
                $table->dropUnique('fidelizacion_cliente_id_unique');
            });
        }
    }
};
