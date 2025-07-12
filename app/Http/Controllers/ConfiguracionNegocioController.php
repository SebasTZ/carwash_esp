<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionNegocio;
use Illuminate\Http\Request;

class ConfiguracionNegocioController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-configuracion|editar-configuracion', ['only' => ['edit']]);
        $this->middleware('permission:editar-configuracion', ['only' => ['update']]);
    }

    public function edit()
    {
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
        $request->validate([
            'nombre_negocio' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
        ]);

        $configuracion = ConfiguracionNegocio::first();
        $configuracion->update($request->all());

        return redirect()->route('configuracion.edit')
            ->with('success', 'Configuración actualizada correctamente');
    }
}
