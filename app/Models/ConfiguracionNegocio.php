<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracionNegocio extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre_negocio',
        'direccion',
        'telefono',
        'logo'
    ];
}
