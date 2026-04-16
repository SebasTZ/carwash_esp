<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCaracteristicaRequest;
use App\Http\Requests\UpdatePresentacioneRequest;
use App\Models\Caracteristica;
use App\Models\Presentacione;
use Exception;
use Illuminate\Support\Facades\DB;

class presentacioneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAnyPermission(['ver-presentacion', 'crear-presentacion', 'editar-presentacion', 'eliminar-presentacion']);

        $presentaciones = Presentacione::with('caracteristica')->latest()->paginate(15);
        return view('presentacione.index', compact('presentaciones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorizePermission('crear-presentacion');

        return view('presentacione.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCaracteristicaRequest $request)
    {
        $this->authorizePermission('crear-presentacion');

        try {
            DB::beginTransaction();
            $caracteristica = Caracteristica::create($request->validated());
            $caracteristica->presentacione()->create([
                'caracteristica_id' => $caracteristica->id
            ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error al registrar la presentación');
        }

        return redirect()->route('presentaciones.index')->with('success', 'Presentación registrada');
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
    public function edit(Presentacione $presentacione)
    {
        $this->authorizePermission('editar-presentacion');

        return view('presentacione.edit',compact('presentacione'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePresentacioneRequest $request, Presentacione $presentacione)
    {
        $this->authorizePermission('editar-presentacion');

        Caracteristica::where('id', $presentacione->caracteristica->id)
            ->update($request->validated());

        return redirect()->route('presentaciones.index')->with('success', 'Presentación editada');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Presentacione $presentacione)
    {
        $this->authorizePermission('eliminar-presentacion');

        if ($presentacione->caracteristica->estado == 1) {
            Caracteristica::where('id', $presentacione->caracteristica->id)
                ->update(['estado' => 0]);
            $message = 'Presentación eliminada';
        } else {
            Caracteristica::where('id', $presentacione->caracteristica->id)
                ->update(['estado' => 1]);
            $message = 'Presentación restaurada';
        }

        return redirect()->route('presentaciones.index')->with('success', $message);
    }
}
