<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mantenimiento extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'placa',
        'modelo',
        'tipo_vehiculo',
        'fecha_ingreso',
        'fecha_entrega_estimada',
        'fecha_entrega_real',
        'tipo_servicio',
        'descripcion_trabajo',
        'observaciones',
        'costo_estimado',
        'costo_final',
        'mecanico_responsable',
        'estado',
        'pagado',
        'venta_id',
    ];

    protected $casts = [
        'fecha_ingreso' => 'datetime',
        'fecha_entrega_estimada' => 'datetime',
        'fecha_entrega_real' => 'datetime',
        'pagado' => 'boolean',
    ];

    /**
     * Relación con el cliente al que pertenece este mantenimiento
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Relación con la venta asociada (si existe)
     */
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }
}
