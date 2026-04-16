<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionNegocio;
use Illuminate\Http\Request;

class ConfiguracionNegocioController extends Controller
{

    public function edit()
    {
        $this->authorizePermission('ver-configuracion');

        $configuracion = ConfiguracionNegocio::first();
        if (!$configuracion) {
            $configuracion = ConfiguracionNegocio::create([
                'nombre_negocio' => 'Empresa',
                'direccion' => 'Dirección',
                'telefono' => 'Teléfono'
            ]);
        }
        return view('configuracion.edit', compact('configuracion'));
    }

    public function update(Request $request)
    {
        $this->authorizePermission('editar-configuracion');

        $request->validate([
            'nombre_negocio' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
        ]);

        $configuracion = ConfiguracionNegocio::first();
        $configuracion->update($request->only(['nombre_negocio', 'direccion', 'telefono']));

        return redirect()->route('configuracion.edit')
            ->with('success', 'Configuración actualizada correctamente');
    }
}
