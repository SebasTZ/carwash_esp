<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoComision extends Model
{
    use HasFactory;

    protected $table = 'pagos_comisiones';

    protected $fillable = [
        'lavador_id',
        'monto_pagado',
        'desde',
        'hasta',
        'observacion',
        'fecha_pago',
    ];

    public function lavador()
    {
        return $this->belongsTo(Lavador::class);
    }
}
