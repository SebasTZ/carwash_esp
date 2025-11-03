<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estacionamiento extends Model
{
    use HasFactory;

    protected $table = 'estacionamientos';

    protected $fillable = [
        'cliente_id',
        'placa',
        'marca', 
        'modelo',
        'numero_documento',
        'telefono',
        'tarifa_hora',
        'hora_entrada',
        'hora_salida',
        'monto_total',
        'estado',
        'pagado_adelantado',
        'monto_pagado_adelantado'
    ];

    protected $casts = [
        'hora_entrada' => 'datetime',
        'hora_salida' => 'datetime',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function calcularMontoTotal()
    {
        if (!$this->hora_salida) {
            return null;
        }

        $horasEstacionado = $this->hora_entrada->diffInHours($this->hora_salida, true);
        return $this->tarifa_hora * ceil($horasEstacionado);
    }
}
