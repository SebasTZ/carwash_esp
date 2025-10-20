<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'fecha_vencimiento',
        'marca_id',
        'presentacione_id',
        'img_path',
        'es_servicio_lavado',
        'precio_venta',
        'stock'
    ];

    public function compras()
    {
        return $this->belongsToMany(Compra::class)->withTimestamps()
            ->withPivot('cantidad', 'precio_compra', 'precio_venta');
    }

    public function ventas()
    {
        return $this->belongsToMany(Venta::class)->withTimestamps()
            ->withPivot('cantidad', 'precio_venta', 'descuento');
    }

    public function categorias()
    {
        return $this->belongsToMany(Categoria::class)->withTimestamps();
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class);
    }

    public function presentacione()
    {
        return $this->belongsTo(Presentacione::class);
    }

    // ============================================
    // QUERY SCOPES
    // ============================================

    /**
     * Scope para filtrar productos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 1);
    }

    /**
     * Scope para filtrar productos con stock disponible
     */
    public function scopeConStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    /**
     * Scope para filtrar productos que NO son servicios de lavado
     */
    public function scopeNoServicio($query)
    {
        return $query->where('es_servicio_lavado', false);
    }

    /**
     * Scope para filtrar solo servicios de lavado
     */
    public function scopeServiciosLavado($query)
    {
        return $query->where('es_servicio_lavado', true);
    }

    /**
     * Scope para productos con stock bajo
     */
    public function scopeStockBajo($query, $limite = 10)
    {
        return $query->where('stock', '<=', $limite)
            ->where('stock', '>', 0)
            ->where('es_servicio_lavado', false);
    }

    /**
     * Scope para buscar productos por nombre o código
     */
    public function scopeBuscar($query, $termino)
    {
        return $query->where(function($q) use ($termino) {
            $q->where('nombre', 'LIKE', "%{$termino}%")
              ->orWhere('codigo', 'LIKE', "%{$termino}%");
        });
    }

    // ============================================
    // ACCESSORS & MUTATORS
    // ============================================

    /**
     * Obtiene el estado del stock
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->es_servicio_lavado) {
            return 'servicio';
        }
        if ($this->stock <= 0) {
            return 'agotado';
        }
        if ($this->stock <= ($this->stock_minimo ?? 10)) {
            return 'bajo';
        }
        return 'disponible';
    }

    /**
     * Obtiene el color del badge según el estado del stock
     */
    public function getStockStatusColorAttribute(): string
    {
        return match($this->stock_status) {
            'agotado' => 'danger',
            'bajo' => 'warning',
            'disponible' => 'success',
            'servicio' => 'info',
            default => 'secondary',
        };
    }

    // ============================================
    // MÉTODOS AUXILIARES
    // ============================================

    public function handleUploadImage($image)
    {
        $file = $image;
        $name = time() . $file->getClientOriginalName();
        //$file->move(public_path() . '/img/productos/', $name);
        Storage::putFileAs('/public/productos/',$file,$name,'public');

        return $name;
    }

    /**
     * Devuelve el stock del producto.
     * Si es servicio de lavado, retorna null (stock ilimitado).
     * Si no, retorna el stock real (debe implementarse según la lógica actual del sistema).
     */
    public function getStock()
    {
        if ($this->es_servicio_lavado) {
            return null; // o 'ilimitado'
        }
        // Lógica de stock real aquí (placeholder):
        // return $this->stock;
        return 0;
    }
}
