<?php

namespace App\Http\Controllers;

use App\Models\Lavador;
use Illuminate\Http\Request;

class LavadorController extends Controller
{

    public function index()
    {
        $this->authorizePermission('ver-lavador');

        $lavadores = Lavador::paginate(15);
        return view('lavadores.index', compact('lavadores'));
    }

    public function create()
    {
        $this->authorizePermission('crear-lavador');

        return view('lavadores.create');
    }

    public function store(Request $request)
    {
        $this->authorizePermission('crear-lavador');

        $request->validate([
            'nombre' => 'required',
            'dni' => 'required|unique:lavadores',
            'telefono' => 'nullable',
            'estado' => 'required',
        ]);
        Lavador::create($request->only(['nombre', 'dni', 'telefono', 'estado']));
        return redirect()->route('lavadores.index');
    }

    public function edit(Lavador $lavador)
    {
        $this->authorizePermission('editar-lavador');

        return view('lavadores.edit', compact('lavador'));
    }

    public function update(Request $request, Lavador $lavador)
    {
        $this->authorizePermission('editar-lavador');

        $request->validate([
            'nombre' => 'required',
            'dni' => 'required|unique:lavadores,dni,' . $lavador->id,
            'telefono' => 'nullable',
            'estado' => 'required',
        ]);
        $lavador->update($request->only(['nombre', 'dni', 'telefono', 'estado']));
        return redirect()->route('lavadores.index');
    }

    public function destroy(Lavador $lavador)
    {
        $this->authorizePermission('eliminar-lavador');

        $lavador->estado = 'inactivo';
        $lavador->save();
        return redirect()->route('lavadores.index');
    }
}
