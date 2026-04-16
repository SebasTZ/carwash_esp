<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCaracteristicaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $routeName = $this->route()?->getName();

        $permission = match ($routeName) {
            'categorias.store' => 'crear-categoria',
            'marcas.store' => 'crear-marca',
            'presentaciones.store' => 'crear-presentacion',
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
            'nombre' => 'required|max:60|unique:caracteristicas,nombre',
            'descripcion' => 'nullable|max:255'
        ];
    }
}
