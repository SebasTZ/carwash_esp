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

    /**
     * Scope para filtrar clientes activos
     */
    public function scopeActivos($query)
    {
        return $query->whereHas('persona', function($q) {
            $q->where('estado', 1);
        });
    }

    /**
     * Scope para clientes con fidelización activa
     */
    public function scopeConFidelidad($query)
    {
        return $query->where('lavados_acumulados', '>=', 1);
    }

    /**
     * Scope para buscar clientes por término
     */
    public function scopeBuscar($query, $termino)
    {
        return $query->whereHas('persona', function($q) use ($termino) {
            $q->where('razon_social', 'LIKE', "%{$termino}%")
              ->orWhere('direccion', 'LIKE', "%{$termino}%");
        });
    }

    // ============================================
    // ACCESSORS
    // ============================================

    /**
     * Obtiene el nombre completo del cliente
     */
    public function getNombreCompletoAttribute(): string
    {
        return $this->persona->razon_social ?? 'Sin nombre';
    }

    /**
     * Obtiene el progreso de fidelización en porcentaje
     */
    public function getProgresoFidelidadAttribute(): int
    {
        return min(100, ($this->lavados_acumulados / 10) * 100);
    }

    /**
     * Verifica si el cliente puede canjear lavado gratis
     */
    public function getPuedeCanjearLavadoAttribute(): bool
    {
        return $this->lavados_acumulados >= 10;
    }

    protected $fillable = [
        'persona_id',
        'lavados_acumulados',
    ];
}
