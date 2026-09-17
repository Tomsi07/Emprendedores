<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UsuarioController extends Controller
{
    public function index()
    {
        return Inertia::render('SuperAdmin/Usuarios/Index', [
            'usuarios' => User::select('id', 'name', 'email', 'role')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('SuperAdmin/Usuarios/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:user,admin,superadmin',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()->route('superadmin.usuarios.index')->with('status', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario)
    {
        return Inertia::render('SuperAdmin/Usuarios/Edit', [
            'usuario' => $usuario->only('id', 'name', 'email', 'role'),
        ]);
    }

    public function update(Request $request, User $usuario)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $usuario->id,
            'password' => 'nullable|string|min:8',
            'role' => 'required|string|in:user,admin,superadmin',
        ]);

        $usuario->name = $validated['name'];
        $usuario->email = $validated['email'];
        $usuario->role = $validated['role'];

        if (!empty($validated['password'])) {
            $usuario->password = Hash::make($validated['password']);
        }

        $usuario->save();

        return redirect()->route('superadmin.usuarios.index')->with('status', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $usuario)
    {
        $usuario->delete();

        return redirect()->route('superadmin.usuarios.index')->with('status', 'Usuario eliminado.');
    }
}