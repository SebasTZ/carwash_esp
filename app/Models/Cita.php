<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'fecha',
        'hora',
        'posicion_cola',
        'estado',
        'notas',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    // Relationship with Cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    // Get the next available queue position for a given date
    public static function getNextQueuePosition($fecha)
    {
        $maxPosition = self::where('fecha', $fecha)->max('posicion_cola') ?? 0;
        return $maxPosition + 1;
    }
}
