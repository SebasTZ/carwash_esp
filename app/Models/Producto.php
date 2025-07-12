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
