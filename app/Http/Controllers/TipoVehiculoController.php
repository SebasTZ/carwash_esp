<?php

namespace App\Http\Controllers;

use App\Models\TipoVehiculo;
use Illuminate\Http\Request;

class TipoVehiculoController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:ver-tipo-vehiculo')->only(['index', 'show']);
        $this->middleware('can:crear-tipo-vehiculo')->only(['create', 'store']);
        $this->middleware('can:editar-tipo-vehiculo')->only(['edit', 'update']);
        $this->middleware('can:eliminar-tipo-vehiculo')->only(['destroy']);
    }

    public function index()
    {
        $tipos = TipoVehiculo::all();
        return view('tipos_vehiculo.index', compact('tipos'));
    }

    public function create()
    {
        return view('tipos_vehiculo.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required',
            'comision' => 'required|numeric',
            'estado' => 'required',
        ]);
        TipoVehiculo::create($request->all());
        return redirect()->route('tipos_vehiculo.index');
    }

    public function edit(TipoVehiculo $tipoVehiculo)
    {
        return view('tipos_vehiculo.edit', compact('tipoVehiculo'));
    }

    public function update(Request $request, TipoVehiculo $tipoVehiculo)
    {
        $request->validate([
            'nombre' => 'required',
            'comision' => 'required|numeric',
            'estado' => 'required',
        ]);
        $tipoVehiculo->update($request->all());
        return redirect()->route('tipos_vehiculo.index');
    }
}
