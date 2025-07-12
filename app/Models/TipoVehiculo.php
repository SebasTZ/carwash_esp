<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ControlLavado;

class TipoVehiculo extends Model
{
    use HasFactory;

    protected $table = 'tipos_vehiculo';

    protected $fillable = [
        'nombre',
        'comision',
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
        return $this->hasMany(ControlLavado::class, 'tipo_vehiculo_id');
    }
}
