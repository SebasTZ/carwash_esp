<?php

namespace App\Services;

use App\Models\Comprobante;
use App\Models\SecuenciaComprobante;
use Illuminate\Support\Facades\DB;

class ComprobanteService
{
    /**
     * Genera el siguiente número de comprobante de forma segura
     *
     * @param int $comprobanteId
     * @return string
     */
    public function generarSiguienteNumero(int $comprobanteId): string
    {
        return DB::transaction(function () use ($comprobanteId) {
            $comprobante = Comprobante::findOrFail($comprobanteId);

            // Intentar usar tabla de secuencias (si existe)
            try {
                $secuencia = SecuenciaComprobante::firstOrCreate(
                    ['comprobante_id' => $comprobanteId],
                    ['ultimo_numero' => 0]
                );

                $secuencia->lockForUpdate();
                $secuencia->increment('ultimo_numero');

                $numero = $secuencia->ultimo_numero;
            } catch (\Exception $e) {
                // Fallback al método antiguo con lock
                $ultimaVenta = \App\Models\Venta::where('comprobante_id', $comprobanteId)
                    ->lockForUpdate()
                    ->latest('id')
                    ->first();

                $numero = $ultimaVenta 
                    ? intval(substr($ultimaVenta->numero_comprobante, strlen($comprobante->serie))) + 1
                    : 1;
            }

            return $comprobante->serie . str_pad($numero, 4, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Obtiene el último número de comprobante
     *
     * @param int $comprobanteId
     * @return string|null
     */
    public function obtenerUltimoNumero(int $comprobanteId): ?string
    {
        $ultimaVenta = \App\Models\Venta::where('comprobante_id', $comprobanteId)
            ->latest('id')
            ->first();

        return $ultimaVenta?->numero_comprobante;
    }
}
