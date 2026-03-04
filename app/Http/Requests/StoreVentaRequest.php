<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVentaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
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
            'tarjeta_regalo_id'   => 'nullable|exists:tarjetas_regalo,id',
            'tarjeta_regalo_codigo' => 'required_if:medio_pago,tarjeta_regalo|nullable|string',
            'codigo_tarjeta'      => 'nullable|string',
            'lavado_gratis'       => 'nullable|boolean',
            'servicio_lavado'     => 'nullable|boolean',
            'horario_lavado'      => [
                Rule::requiredIf(fn() => (bool) $this->input('servicio_lavado')),
                'nullable',
                'date_format:Y-m-d\TH:i',
            ],
            'con_igv'             => 'nullable|boolean',
        ];
    }
}
