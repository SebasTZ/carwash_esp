<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVentaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'fecha_hora' => 'required',
            'impuesto' => 'required',
            'total' => 'required|numeric',
            'cliente_id' => 'required|exists:clientes,id',
            'user_id' => 'required|exists:users,id',
            'comprobante_id' => 'required|exists:comprobantes,id',
            'comentarios' => 'nullable|string',
            'medio_pago' => 'required|string|in:efectivo,tarjeta_credito,tarjeta_regalo,lavado_gratis',
            'efectivo' => 'nullable|numeric',
            'tarjeta_regalo_id' => 'nullable|exists:tarjetas_regalo,id',
            'codigo_tarjeta' => 'nullable|string',
            'lavado_gratis' => 'nullable|boolean',
            'tarjeta_credito' => 'nullable|numeric',
            'tarjeta_regalo_codigo' => 'required_if:medio_pago,tarjeta_regalo|string|nullable'
        ];
    }
}