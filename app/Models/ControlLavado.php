<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ControlLavado extends Model
{
    use HasFactory;

    protected $table = 'control_lavados';

    protected $fillable = [
        'venta_id',
        'cliente_id',
        'lavador_id', // nuevo campo
        'tipo_vehiculo_id', // nuevo campo
        'hora_llegada',
        'horario_estimado',
        'inicio_lavado',
        'fin_lavado',
        'inicio_interior',
        'fin_interior',
        'hora_final',
        'tiempo_total',
        'estado',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function lavador()
    {
        return $this->belongsTo(Lavador::class, 'lavador_id');
    }

    public function tipoVehiculo()
    {
        return $this->belongsTo(TipoVehiculo::class, 'tipo_vehiculo_id');
    }

    public function pagosComisiones()
    {
        return $this->hasMany(\App\Models\PagoComision::class, 'lavador_id', 'lavador_id');
    }

    public function auditoriaLavadores()
    {
        return $this->hasMany(\App\Models\AuditoriaLavador::class, 'control_lavado_id');
    }
}
