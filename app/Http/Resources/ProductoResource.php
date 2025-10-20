<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'stock' => (int) $this->stock,
            'stock_minimo' => (int) ($this->stock_minimo ?? 10),
            'stock_status' => $this->stock_status, // Accessor del modelo
            'stock_status_color' => $this->stock_status_color, // Accessor del modelo
            'precio_compra' => (float) $this->precio_compra,
            'precio_venta' => (float) $this->precio_venta,
            'es_servicio_lavado' => (bool) $this->es_servicio_lavado,
            'estado' => (int) $this->estado,
            'imagen' => $this->imagen,
            
            // Relaciones
            'marca' => [
                'id' => $this->marca->caracteristica->id ?? null,
                'nombre' => $this->marca->caracteristica->nombre ?? null,
            ],
            'presentacion' => [
                'id' => $this->presentacione->caracteristica->id ?? null,
                'nombre' => $this->presentacione->caracteristica->nombre ?? null,
            ],
            'categorias' => $this->whenLoaded('categorias', function () {
                return $this->categorias->map(function ($categoria) {
                    return [
                        'id' => $categoria->caracteristica->id ?? null,
                        'nombre' => $categoria->caracteristica->nombre ?? null,
                    ];
                });
            }),
            
            // Si es pivot de venta
            'cantidad' => $this->when(isset($this->pivot), $this->pivot->cantidad ?? null),
            'precio_venta_actual' => $this->when(isset($this->pivot), $this->pivot->precio_venta ?? null),
            'descuento' => $this->when(isset($this->pivot), $this->pivot->descuento ?? null),
            'subtotal' => $this->when(
                isset($this->pivot),
                fn () => ($this->pivot->precio_venta * $this->pivot->cantidad) - ($this->pivot->descuento ?? 0)
            ),
            
            // Timestamps
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
