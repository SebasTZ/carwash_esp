<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonaRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Models\Cliente;
use App\Models\Documento;
use App\Models\Persona;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class clienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAnyPermission(['ver-cliente', 'crear-cliente', 'editar-cliente', 'eliminar-cliente']);

        $clientes = Cliente::with('persona.documento')->paginate(15);
        return view('cliente.index', compact('clientes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorizePermission('crear-cliente');

        $documentos = Documento::all();
        return view('cliente.create', compact('documentos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePersonaRequest $request)
    {
        $this->authorizePermission('crear-cliente');

        try {
            DB::beginTransaction();
            $persona = Persona::create($request->validated());
            $persona->cliente()->create([
                'persona_id' => $persona->id
            ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->route('clientes.create')->withErrors(['error' => 'Error al registrar el cliente']);
        }

        return redirect()->route('clientes.index')->with('success', 'Cliente registrado');
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
    public function edit(Cliente $cliente)
    {
        $this->authorizePermission('editar-cliente');

        $cliente->load('persona.documento');
        $documentos = Documento::all();
        return view('cliente.edit', compact('cliente', 'documentos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClienteRequest $request, Cliente $cliente)
    {
        $this->authorizePermission('editar-cliente');

        try {
            DB::beginTransaction();

            Persona::where('id', $cliente->persona->id)
                ->update($request->validated());

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error al actualizar el cliente');
        }

        return redirect()->route('clientes.index')->with('success', 'Cliente editado');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cliente $cliente)
    {
        $this->authorizePermission('eliminar-cliente');

        $persona = $cliente->persona;
        if ($persona->estado == 1) {
            $persona->update(['estado' => 0]);
            $message = 'Cliente eliminado';
        } else {
            $persona->update(['estado' => 1]);
            $message = 'Cliente restaurado';
        }

        return redirect()->route('clientes.index')->with('success', $message);
    }

    public function fidelizacion(Cliente $cliente)
    {
        $this->authorizePermission('ver-fidelizacion');

        return view('cliente.fidelizacion', compact('cliente'));
    }
}
