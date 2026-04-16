<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonaRequest;
use App\Http\Requests\UpdateProveedoreRequest;
use App\Models\Documento;
use App\Models\Persona;
use App\Models\Proveedore;
use Exception;
use Illuminate\Support\Facades\DB;

class proveedorController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAnyPermission(['ver-proveedor', 'crear-proveedor', 'editar-proveedor', 'eliminar-proveedor']);

        $proveedores = Proveedore::with('persona.documento')->paginate(15);
        return view('proveedore.index', compact('proveedores'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorizePermission('crear-proveedor');

        $documentos = Documento::all();
        return view('proveedore.create', compact('documentos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePersonaRequest $request)
    {
        $this->authorizePermission('crear-proveedor');

        try {
            DB::beginTransaction();
            $persona = Persona::create($request->validated());
            $persona->proveedore()->create([
                'persona_id' => $persona->id
            ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error al registrar el proveedor');
        }

        return redirect()->route('proveedores.index')->with('success', 'Proveedor registrado');
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
    public function edit(Proveedore $proveedore)
    {
        $this->authorizePermission('editar-proveedor');

        $proveedore->load('persona.documento');
        $documentos = Documento::all();
        return view('proveedore.edit', compact('proveedore', 'documentos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProveedoreRequest $request, Proveedore $proveedore)
    {
        $this->authorizePermission('editar-proveedor');

        try {
            DB::beginTransaction();

            Persona::where('id', $proveedore->persona->id)
                ->update($request->validated());

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error al actualizar el proveedor');
        }

        return redirect()->route('proveedores.index')->with('success', 'Proveedor editado');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Proveedore $proveedore)
    {
        $this->authorizePermission('eliminar-proveedor');

        $persona = $proveedore->persona;
        if ($persona->estado == 1) {
            $persona->update(['estado' => 0]);
            $message = 'Proveedor eliminado';
        } else {
            $persona->update(['estado' => 1]);
            $message = 'Proveedor restaurado';
        }

        return redirect()->route('proveedores.index')->with('success', $message);
    }
}