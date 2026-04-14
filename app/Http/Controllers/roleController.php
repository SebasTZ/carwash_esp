<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class roleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Paginar roles y cargar permisos
        $roles = Role::with('permissions')->paginate(15);
        
        // Transformar la colección manteniendo la paginación
        /** @var \Illuminate\Pagination\LengthAwarePaginator $roles */
        $roles->getCollection()->transform(function($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->toArray(),
                'permissions_count' => $role->permissions->count()
            ];
        });

        return view('role.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Solo enviar los nombres de los permisos como array de strings
        $permisos = Permission::pluck('name')->toArray();
        return view('role.create', compact('permisos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permission' => 'required'
        ]);

        try {
            DB::beginTransaction();
            $rol = Role::create(['name' => $request->name]);
            $rol->syncPermissions($request->permission);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error al registrar el rol');
        }

        return redirect()->route('roles.index')->with('success', 'Rol registrado');
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
    public function edit(Role $role)
    {
        // Solo enviar los nombres de los permisos como array de strings
        $permisos = Permission::pluck('name')->toArray();
        return view('role.edit', compact('role', 'permisos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'permission' => 'required'
        ]);

        try {
            DB::beginTransaction();
            $role->update(['name' => $request->name]);
            $role->syncPermissions($request->permission);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error al actualizar el rol');
        }

        return redirect()->route('roles.index')->with('success', 'Rol editado');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Rol eliminado');
    }
}
