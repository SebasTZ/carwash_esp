<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePersonaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $routeName = $this->route()?->getName();

        $permission = match ($routeName) {
            'clientes.store' => 'crear-cliente',
            'proveedores.store' => 'crear-proveedor',
            default => null,
        };

        return $permission !== null && (bool) $this->user()?->can($permission);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'razon_social' => 'required|max:80',
            'direccion' => 'required|max:80',
            'tipo_persona' => 'required|string',
            'telefono' => 'required|string|max:20',
            'documento_id' => 'required|integer|exists:documentos,id',
            'numero_documento' => 'required|max:20|unique:personas,numero_documento'
        ];
    }
}
