<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cochera extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'placa',
        'modelo',
        'color',
        'tipo_vehiculo',
        'fecha_ingreso',
        'fecha_salida',
        'ubicacion',
        'tarifa_hora',
        'tarifa_dia',
        'monto_total',
        'observaciones',
        'estado',
    ];

    protected $casts = [
        'fecha_ingreso' => 'datetime',
        'fecha_salida' => 'datetime',
    ];

    /**
     * Relación con el cliente al que pertenece este registro de cochera
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Calcula el monto a pagar basado en el tiempo de estancia
     */
    public function calcularMonto()
    {
        if (!$this->fecha_salida) {
            $hasta = now();
        } else {
            $hasta = $this->fecha_salida;
        }

        $horas = $this->fecha_ingreso->diffInHours($hasta);
        $dias = floor($horas / 24);
        $horasRestantes = $horas % 24;

        $monto = 0;
        
        if ($dias > 0 && $this->tarifa_dia) {
            $monto += $dias * $this->tarifa_dia;
        }
        
        $monto += $horasRestantes * $this->tarifa_hora;
        
        return $monto;
    }
}
