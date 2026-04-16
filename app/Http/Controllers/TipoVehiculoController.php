<?php

namespace App\Http\Controllers;

use App\Models\TipoVehiculo;
use Illuminate\Http\Request;

class TipoVehiculoController extends Controller
{

    public function index()
    {
        $this->authorizePermission('ver-tipo-vehiculo');

        $tipos = TipoVehiculo::paginate(15);
        return view('tipos_vehiculo.index', compact('tipos'));
    }

    public function create()
    {
        $this->authorizePermission('crear-tipo-vehiculo');

        return view('tipos_vehiculo.create');
    }

    public function store(Request $request)
    {
        $this->authorizePermission('crear-tipo-vehiculo');

        $request->validate([
            'nombre' => 'required',
            'comision' => 'required|numeric',
            'estado' => 'required',
        ]);
        TipoVehiculo::create($request->only(['nombre', 'comision', 'estado']));
        return redirect()->route('tipos_vehiculo.index');
    }

    public function edit(TipoVehiculo $tipoVehiculo)
    {
        $this->authorizePermission('editar-tipo-vehiculo');

        return view('tipos_vehiculo.edit', compact('tipoVehiculo'));
    }

    public function update(Request $request, TipoVehiculo $tipoVehiculo)
    {
        $this->authorizePermission('editar-tipo-vehiculo');

        $request->validate([
            'nombre' => 'required',
            'comision' => 'required|numeric',
            'estado' => 'required',
        ]);
        $tipoVehiculo->update($request->only(['nombre', 'comision', 'estado']));
        return redirect()->route('tipos_vehiculo.index');
    }
}
