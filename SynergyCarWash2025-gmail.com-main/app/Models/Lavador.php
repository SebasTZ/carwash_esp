<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lavador extends Model
{
    use HasFactory;

    protected $table = 'lavadores';

    protected $fillable = [
        'nombre',
        'dni',
        'telefono',
        'estado',
    ];

    /**
     * Get the route key name for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'id';
    }

    public function lavados()
    {
        // Relación con ControlLavado
        return $this->hasMany(ControlLavado::class, 'lavador_id');
    }

    public function pagosComisiones()
    {
        return $this->hasMany(PagoComision::class, 'lavador_id');
    }
}
