<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecuenciaComprobante extends Model
{
    use HasFactory;

    protected $table = 'secuencias_comprobantes';

    protected $fillable = [
        'comprobante_id',
        'ultimo_numero',
    ];

    protected $casts = [
        'ultimo_numero' => 'integer',
    ];

    // Relaciones
    public function comprobante()
    {
        return $this->belongsTo(Comprobante::class);
    }
}
