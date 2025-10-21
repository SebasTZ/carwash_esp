<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditoriaLavador extends Model
{
    use HasFactory;
    
    protected $table = 'auditoria_lavadores';
    protected $fillable = [
        'control_lavado_id',
        'lavador_id_anterior',
        'lavador_id_nuevo',
        'usuario_id',
        'motivo',
        'fecha_cambio',
    ];

    public function controlLavado()
    {
        return $this->belongsTo(ControlLavado::class);
    }
    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
    public function lavadorAnterior()
    {
        return $this->belongsTo(Lavador::class, 'lavador_id_anterior');
    }
    public function lavadorNuevo()
    {
        return $this->belongsTo(Lavador::class, 'lavador_id_nuevo');
    }
}
