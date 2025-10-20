<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClienteResource extends JsonResource
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
            'tipo_persona' => $this->persona->tipo_persona ?? null,
            'razon_social' => $this->persona->razon_social ?? null,
            'direccion' => $this->persona->direccion ?? null,
            'tipo_documento' => $this->documento->tipo_documento ?? null,
            'numero_documento' => $this->documento->numero_documento ?? null,
            'lavados_acumulados' => (int) $this->lavados_acumulados,
            'estado' => (int) $this->estado,
            
            // Accessors del modelo
            'nombre_completo' => $this->nombre_completo,
            'progreso_fidelidad' => $this->progreso_fidelidad,
            'puede_canjear_lavado' => $this->puede_canjear_lavado,
            
            // Fidelización
            'fidelizacion' => $this->when($this->relationLoaded('fidelizacion'), [
                'puntos' => $this->fidelizacion->puntos ?? 0,
                'lavados_acumulados' => $this->lavados_acumulados,
                'lavados_faltantes' => max(0, 10 - $this->lavados_acumulados),
            ]),
            
            // Estadísticas (solo si se solicitan)
            'total_compras' => $this->when(
                $this->relationLoaded('ventas'),
                fn () => $this->ventas->where('estado', 1)->sum('total')
            ),
            'cantidad_compras' => $this->when(
                $this->relationLoaded('ventas'),
                fn () => $this->ventas->where('estado', 1)->count()
            ),
            'ultima_compra' => $this->when(
                $this->relationLoaded('ventas'),
                fn () => $this->ventas->where('estado', 1)->sortByDesc('fecha_hora')->first()?->fecha_hora?->format('Y-m-d H:i:s')
            ),
            
            // Timestamps
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
