<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('crear-venta');
    }

    public function rules(): array
    {
        return [
            'impuesto'            => 'nullable|numeric|min:0',
            'total'               => 'required|numeric|min:0',
            'cliente_id'          => 'required|exists:clientes,id',
            'comprobante_id'      => 'required|exists:comprobantes,id',
            'comentarios'         => 'nullable|string',
            'medio_pago'          => 'required|string|in:efectivo,tarjeta_credito,tarjeta_regalo,lavado_gratis',
            'efectivo'            => 'nullable|numeric|min:0',
            'tarjeta_credito'     => 'nullable|numeric|min:0',
            'tarjeta_regalo_id'   => [
                Rule::requiredIf(fn () => $this->input('medio_pago') === 'tarjeta_regalo' && !$this->filled('tarjeta_regalo_codigo')),
                'nullable',
                'exists:tarjetas_regalo,id',
            ],
            'tarjeta_regalo_codigo' => [
                Rule::requiredIf(fn () => $this->input('medio_pago') === 'tarjeta_regalo' && !$this->filled('tarjeta_regalo_id')),
                'nullable',
                'string',
            ],
            'codigo_tarjeta'      => 'nullable|string',
            'lavado_gratis'       => 'nullable|boolean',
            'servicio_lavado'     => 'nullable|boolean',
            'arrayidproducto'     => 'required|array|min:1',
            'arrayidproducto.*'   => 'required|exists:productos,id',
            'arraycantidad'       => 'required|array|min:1',
            'arraycantidad.*'     => 'required|integer|min:1',
            'arrayprecioventa'    => 'required|array|min:1',
            'arrayprecioventa.*'  => 'required|numeric|min:0',
            'arraydescuento'      => 'nullable|array',
            'arraydescuento.*'    => 'nullable|numeric|min:0',
            'horario_lavado'      => [
                Rule::requiredIf(fn() => (bool) $this->input('servicio_lavado')),
                'nullable',
                'date_format:Y-m-d\TH:i',
            ],
            'con_igv'             => 'nullable|boolean',
        ];
    }
}
