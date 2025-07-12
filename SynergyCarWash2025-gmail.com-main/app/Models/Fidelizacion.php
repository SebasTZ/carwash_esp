<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fidelizacion extends Model
{
    use HasFactory;

    protected $fillable = ['cliente_id', 'puntos'];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    protected $table = 'fidelizacion';
}
