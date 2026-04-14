<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class userController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Cargar usuarios con sus roles
        $users = User::with('roles')->paginate(15);
        return view('user.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();
        return view('user.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = User::create([
                'name'   => $request->input('name'),
                'email'  => $request->input('email'),
                'estado' => $request->input('estado'),
                'password' => Hash::make($request->input('password')),
            ]);
            $user->assignRole($request->input('role'));

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error al registrar el usuario');
        }

        return redirect()->route('users.index')->with('success', 'Usuario registrado');
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
    public function edit(User $user)
    {
        $roles = Role::all();
        return view('user.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            DB::beginTransaction();

            $data = [
                'name'   => $request->input('name'),
                'email'  => $request->input('email'),
                'estado' => $request->input('estado'),
            ];
            if (!empty($request->input('password'))) {
                $data['password'] = Hash::make($request->input('password'));
            }
            $user->update($data);
            $user->syncRoles([$request->input('role')]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error al actualizar el usuario');
        }

        return redirect()->route('users.index')->with('success', 'Usuario editado');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $rolUser = $user->getRoleNames()->first();
        $user->removeRole($rolUser);
        $user->delete();

        return redirect()->route('users.index')->with('success','Usuario eliminado');
    }
}
