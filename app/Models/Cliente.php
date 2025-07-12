<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    public function persona(){
        return $this->belongsTo(Persona::class);
    }

    public function ventas(){
        return $this->hasMany(Venta::class);
    }

    public function fidelizacion()
    {
        return $this->hasOne(Fidelizacion::class);
    }

    public function scopeFrecuentes($query, $minLavados = 5)
    {
        return $query->where('lavados_acumulados', '>=', $minLavados);
    }

    protected $fillable = [
        'persona_id',
        'lavados_acumulados',
    ];
}
