<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\AuthorizationAuditService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class userController extends Controller
{
    public function __construct(private AuthorizationAuditService $authorizationAuditService)
    {
        $this->authorizeResource(User::class, 'user');
    }

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

            $rolesAntes = $user->getRoleNames()->values()->all();
            $user->syncRoles([$request->input('role')]);
            $rolesDespues = $user->getRoleNames()->values()->all();

            $this->authorizationAuditService->logUserRolesSynced(
                auth()->user(),
                $user,
                $rolesAntes,
                $rolesDespues,
                'created'
            );

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
    public function show(User $user)
    {
        return redirect()->route('users.edit', $user);
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

            $rolesAntes = $user->getRoleNames()->values()->all();
            $user->update($data);
            $user->syncRoles([$request->input('role')]);
            $rolesDespues = $user->getRoleNames()->values()->all();

            $this->authorizationAuditService->logUserRolesSynced(
                auth()->user(),
                $user,
                $rolesAntes,
                $rolesDespues,
                'updated'
            );

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
        $rolesEliminados = $user->getRoleNames()->values()->all();
        $user->syncRoles([]);

        $this->authorizationAuditService->logUserDeletedWithRoles(auth()->user(), $user, $rolesEliminados);

        $user->delete();

        return redirect()->route('users.index')->with('success','Usuario eliminado');
    }
}
