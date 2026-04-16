<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class profileController extends Controller
{


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizePermission('ver-perfil');

        $user = User::find(Auth::user()->id);
        return view('profile.index', compact('user'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $profile)
    {
        $this->authorizePermission('editar-perfil');

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $profile->id,
            'password' => 'nullable|min:8',
        ]);

        $data = [
            'name'  => $request->input('name'),
            'email' => $request->input('email'),
        ];
        if (!empty($request->input('password'))) {
            $data['password'] = Hash::make($request->input('password'));
        }

        $profile->update($data);

        return redirect()->route('profile.index')->with('success', 'Cambios guardados');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
