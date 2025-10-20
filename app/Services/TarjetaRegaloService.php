<?php

namespace App\Services;

use App\Models\TarjetaRegalo;
use App\Exceptions\TarjetaRegaloException;
use Illuminate\Support\Facades\DB;

class TarjetaRegaloService
{
    /**
     * Valida la tarjeta de regalo y descuenta el saldo
     *
     * @param string $codigo
     * @param float $monto
     * @return TarjetaRegalo
     * @throws TarjetaRegaloException
     */
    public function validarYDescontar(string $codigo, float $monto): TarjetaRegalo
    {
        return DB::transaction(function () use ($codigo, $monto) {
            $tarjeta = TarjetaRegalo::where('codigo', $codigo)
                ->where('estado', 'activa')
                ->lockForUpdate()
                ->first();

            if (!$tarjeta) {
                throw new TarjetaRegaloException('Tarjeta de regalo no válida o no activa');
            }

            if ($tarjeta->saldo_actual < $monto) {
                throw new TarjetaRegaloException(
                    "Saldo insuficiente. Disponible: S/ {$tarjeta->saldo_actual}, Requerido: S/ {$monto}"
                );
            }

            // Descontar saldo
            $tarjeta->saldo_actual -= $monto;

            // Marcar como usada si el saldo llega a 0
            if ($tarjeta->saldo_actual <= 0) {
                $tarjeta->saldo_actual = 0;
                $tarjeta->estado = 'usada';
            }

            $tarjeta->save();

            return $tarjeta;
        });
    }

    /**
     * Revierte el uso de una tarjeta de regalo (anulación de venta)
     *
     * @param int $tarjetaId
     * @param float $monto
     * @return void
     */
    public function revertirUso(int $tarjetaId, float $monto): void
    {
        DB::transaction(function () use ($tarjetaId, $monto) {
            $tarjeta = TarjetaRegalo::lockForUpdate()->find($tarjetaId);

            if ($tarjeta) {
                $tarjeta->saldo_actual += $monto;
                
                // Si estaba usada y ahora tiene saldo, activarla
                if ($tarjeta->estado === 'usada' && $tarjeta->saldo_actual > 0) {
                    $tarjeta->estado = 'activa';
                }

                $tarjeta->save();
            }
        });
    }

    /**
     * Genera un código único para una nueva tarjeta
     *
     * @return string
     */
    public function generarCodigoUnico(): string
    {
        do {
            $codigo = 'TG-' . strtoupper(substr(md5(uniqid()), 0, 10));
        } while (TarjetaRegalo::where('codigo', $codigo)->exists());

        return $codigo;
    }

    /**
     * Crea una nueva tarjeta de regalo
     *
     * @param float $montoInicial
     * @param int|null $clienteId
     * @return TarjetaRegalo
     */
    public function crear(float $montoInicial, ?int $clienteId = null): TarjetaRegalo
    {
        return TarjetaRegalo::create([
            'codigo' => $this->generarCodigoUnico(),
            'monto_inicial' => $montoInicial,
            'saldo_actual' => $montoInicial,
            'cliente_id' => $clienteId,
            'estado' => 'activa',
            'fecha_emision' => now(),
        ]);
    }
}
