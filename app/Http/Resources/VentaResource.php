<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VentaResource extends JsonResource
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
            'numero_comprobante' => $this->numero_comprobante,
            'fecha_hora' => $this->fecha_hora->format('Y-m-d H:i:s'),
            'fecha_formateada' => $this->fecha_hora->format('d/m/Y H:i'),
            'total' => (float) $this->total,
            'impuesto' => (float) $this->impuesto,
            'medio_pago' => $this->medio_pago,
            'servicio_lavado' => (bool) $this->servicio_lavado,
            'lavado_gratis' => (bool) $this->lavado_gratis,
            'horario_lavado' => $this->horario_lavado?->format('Y-m-d H:i:s'),
            'estado' => $this->estado,
            'comentarios' => $this->comentarios,
            
            // Relaciones
            'cliente' => new ClienteResource($this->whenLoaded('cliente')),
            'comprobante' => [
                'id' => $this->comprobante->id ?? null,
                'tipo' => $this->comprobante->tipo_comprobante ?? null,
            ],
            'usuario' => [
                'id' => $this->user->id ?? null,
                'nombre' => $this->user->name ?? null,
            ],
            'productos' => ProductoResource::collection($this->whenLoaded('productos')),
            
            // Metadatos
            'cantidad_productos' => $this->when(
                $this->relationLoaded('productos'),
                fn () => $this->productos->sum('pivot.cantidad')
            ),
            'tiene_control_lavado' => $this->relationLoaded('controlLavado') 
                ? $this->controlLavado !== null 
                : null,
            
            // Timestamps
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
