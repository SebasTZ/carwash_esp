<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $fillable = [
        'fecha_hora',
        'impuesto',
        'numero_comprobante',
        'total',
        'cliente_id',
        'user_id',
        'comprobante_id',
        'comentarios', // Nuevo campo
        'medio_pago', // Nuevo campo
        'efectivo', // Nuevo campo
        'tarjeta_credito', // Renombrado de billetera digital a tarjeta de crédito
        'servicio_lavado', // Nuevo campo
        'horario_lavado', // Nuevo campo
        'tarjeta_regalo_id', // Asegurarse de incluir si se usa en la lógica
        'lavado_gratis' // Asegurarse de incluir si se usa en la lógica
    ];

    public function cliente(){
        return $this->belongsTo(Cliente::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function comprobante(){
        return $this->belongsTo(Comprobante::class);
    }

    public static function generarNumeroComprobante($comprobante_id)
    {
        $comprobante = Comprobante::find($comprobante_id);
        $ultimaVenta = self::where('comprobante_id', $comprobante->id)->latest()->first();
        $ultimoNumero = $ultimaVenta ? intval(substr($ultimaVenta->numero_comprobante, 1)) : 0;
        $nuevoNumero = $ultimoNumero + 1;
        return $comprobante->serie . str_pad($nuevoNumero, 4, '0', STR_PAD_LEFT);
    }

    public function productos(){
        return $this->belongsToMany(Producto::class)->withTimestamps()
        ->withPivot('cantidad','precio_venta','descuento');
    }
}