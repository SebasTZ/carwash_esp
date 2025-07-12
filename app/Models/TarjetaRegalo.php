<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TarjetaRegalo extends Model
{
    use HasFactory;

    protected $table = 'tarjetas_regalo';

    protected $fillable = [
        'codigo',
        'valor_inicial',
        'saldo_actual',
        'estado',
        'fecha_venta',
        'fecha_vencimiento',
        'cliente_id',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
    public function scopeConSaldo($query)
    {
        return $query->where('saldo_actual', '>', 0);
    }
    public function scopeUsadas($query)
    {
        return $query->where('estado', 'usada');
    }
    public function scopeVencidas($query)
    {
        return $query->where('estado', 'vencida');
    }
}
