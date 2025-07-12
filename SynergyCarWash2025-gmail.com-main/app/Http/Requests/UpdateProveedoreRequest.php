<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProveedoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'razon_social' => 'required|string|max:80',
            'direccion' => 'required|string|max:80',
            'tipo_persona' => 'required|string|max:20',
            'telefono' => 'nullable|string|max:20',
            'documento_id' => 'required|exists:documentos,id',
            'numero_documento' => 'required|string|max:20',
        ];
    }
}