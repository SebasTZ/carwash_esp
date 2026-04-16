<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCaracteristicaRequest;
use App\Http\Requests\UpdateMarcaRequest;
use App\Models\Caracteristica;
use App\Models\Marca;
use Exception;
use Illuminate\Support\Facades\DB;

class marcaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAnyPermission(['ver-marca', 'crear-marca', 'editar-marca', 'eliminar-marca']);

        $marcas = Marca::with('caracteristica')->latest()->paginate(15);
        return view('marca.index',compact('marcas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorizePermission('crear-marca');

        return view('marca.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCaracteristicaRequest $request)
    {
        $this->authorizePermission('crear-marca');

        try {
            DB::beginTransaction();
            $caracteristica = Caracteristica::create($request->validated());
            $caracteristica->marca()->create([
                'caracteristica_id' => $caracteristica->id
            ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error al registrar la marca');
        }

        return redirect()->route('marcas.index')->with('success', 'Marca registrada');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Marca $marca)
    {
        $this->authorizePermission('editar-marca');

        return view('marca.edit',compact('marca'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMarcaRequest $request, Marca $marca)
    {
        $this->authorizePermission('editar-marca');

        Caracteristica::where('id', $marca->caracteristica->id)
            ->update($request->validated());

        return redirect()->route('marcas.index')->with('success', 'Marca editada');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Marca $marca)
    {
        $this->authorizePermission('eliminar-marca');

        if ($marca->caracteristica->estado == 1) {
            Caracteristica::where('id', $marca->caracteristica->id)
                ->update(['estado' => 0]);
            $message = 'Marca eliminada';
        } else {
            Caracteristica::where('id', $marca->caracteristica->id)
                ->update(['estado' => 1]);
            $message = 'Marca restaurada';
        }

        return redirect()->route('marcas.index')->with('success', $message);
    }
}
