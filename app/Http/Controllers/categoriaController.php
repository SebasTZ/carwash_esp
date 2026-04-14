<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCaracteristicaRequest;
use App\Http\Requests\UpdateCategoriaRequest;
use App\Models\Caracteristica;
use App\Models\Categoria;
use Exception;
use Illuminate\Support\Facades\DB;

class categoriaController extends Controller
{


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categorias = Categoria::with('caracteristica')->latest()->paginate(15);

        return view('categoria.index', ['categorias' => $categorias]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('categoria.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCaracteristicaRequest $request)
    {
        try {
            DB::beginTransaction();
            $caracteristica = Caracteristica::create($request->validated());
            $caracteristica->categoria()->create([
                'caracteristica_id' => $caracteristica->id
            ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error al registrar la categoría');
        }

        return redirect()->route('categorias.index')->with('success', 'Categoría registrada');
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
    public function edit(Categoria $categoria)
    {
        return view('categoria.edit', ['categoria' => $categoria]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoriaRequest $request, Categoria $categoria)
    {
        Caracteristica::where('id', $categoria->caracteristica->id)
            ->update($request->validated());

        return redirect()->route('categorias.index')->with('success', 'Categoría editada');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Categoria $categoria)
    {
        if ($categoria->caracteristica->estado == 1) {
            Caracteristica::where('id', $categoria->caracteristica->id)
                ->update(['estado' => 0]);
            $message = 'Categoría eliminada correctamente';
        } else {
            $message = 'La categoría ya está eliminada';
        }

        return redirect()->route('categorias.index')->with('success', $message);
    }

    /**
     * Restore the specified resource.
     */
    public function restore(Categoria $categoria)
    {
        if ($categoria->caracteristica->estado == 0) {
            Caracteristica::where('id', $categoria->caracteristica->id)
                ->update(['estado' => 1]);
            $message = 'Categoría restaurada correctamente';
        } else {
            $message = 'La categoría ya está activa';
        }

        return redirect()->route('categorias.index')->with('success', $message);
    }
}
