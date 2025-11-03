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

    protected $casts = [
        'fecha_hora' => 'datetime',
        'horario_lavado' => 'datetime',
        'servicio_lavado' => 'boolean',
        'lavado_gratis' => 'boolean',
        'impuesto' => 'decimal:2',
        'total' => 'decimal:2',
        'efectivo' => 'decimal:2',
        'tarjeta_credito' => 'decimal:2'
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
        return $this->belongsToMany(Producto::class, 'producto_venta', 'venta_id', 'producto_id')
            ->withTimestamps()
            ->withPivot('cantidad','precio_venta','descuento');
    }

    // ============================================
    // QUERY SCOPES
    // ============================================

    /**
     * Scope para filtrar ventas del día
     */
    public function scopeDelDia($query, $fecha = null)
    {
        return $query->whereDate('fecha_hora', $fecha ?? today());
    }

    /**
     * Scope para filtrar ventas de la semana
     */
    public function scopeDeLaSemana($query)
    {
        return $query->whereBetween('fecha_hora', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Scope para filtrar ventas del mes
     */
    public function scopeDelMes($query, $mes = null, $anio = null)
    {
        return $query->whereMonth('fecha_hora', $mes ?? now()->month)
            ->whereYear('fecha_hora', $anio ?? now()->year);
    }

    /**
     * Scope para cargar todas las relaciones comunes
     */
    public function scopeConRelaciones($query)
    {
        return $query->with([
            'cliente.persona',
            'productos',
            'comprobante',
            'user'
        ]);
    }

    /**
     * Scope para filtrar por medio de pago
     */
    public function scopePorMedioPago($query, $medio)
    {
        return $query->where('medio_pago', $medio);
    }

    /**
     * Scope para filtrar ventas activas
     */
    public function scopeActivas($query)
    {
        return $query->where('estado', 1);
    }

    /**
     * Scope para filtrar ventas con servicio de lavado
     */
    public function scopeConServicioLavado($query)
    {
        return $query->where('servicio_lavado', true);
    }

    /**
     * Scope para filtrar por rango de fechas
     */
    public function scopeEntreFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_hora', [
            $fechaInicio . ' 00:00:00',
            $fechaFin . ' 23:59:59'
        ]);
    }
}