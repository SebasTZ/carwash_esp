<?php

namespace App\Http\Controllers;

use App\Models\Lavador;
use Illuminate\Http\Request;

class LavadorController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:ver-lavador')->only(['index', 'show']);
        $this->middleware('can:crear-lavador')->only(['create', 'store']);
        $this->middleware('can:editar-lavador')->only(['edit', 'update']);
        $this->middleware('can:eliminar-lavador')->only(['destroy']);
    }

    public function index()
    {
        $lavadores = Lavador::paginate(15);
        return view('lavadores.index', compact('lavadores'));
    }

    public function create()
    {
        return view('lavadores.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required',
            'dni' => 'required|unique:lavadores',
            'telefono' => 'nullable',
            'estado' => 'required',
        ]);
        Lavador::create($request->all());
        return redirect()->route('lavadores.index');
    }

    public function edit(Lavador $lavador)
    {
        return view('lavadores.edit', compact('lavador'));
    }

    public function update(Request $request, Lavador $lavador)
    {
        $request->validate([
            'nombre' => 'required',
            'dni' => 'required|unique:lavadores,dni,' . $lavador->id,
            'telefono' => 'nullable',
            'estado' => 'required',
        ]);
        $lavador->update($request->all());
        return redirect()->route('lavadores.index');
    }

    public function destroy(Lavador $lavador)
    {
        $lavador->estado = 'inactivo';
        $lavador->save();
        return redirect()->route('lavadores.index');
    }
}
