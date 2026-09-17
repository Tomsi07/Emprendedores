<?php

namespace App\Http\Controllers;

use App\Models\Emprendedor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmprendedorController extends Controller
{
    public function index()
    {
        $emprendedores = Emprendedor::with('emprendimientos')
            ->orderBy('idEmprendedor', 'desc')
            ->get();

        return Inertia::render('pages/emprendedores/index', [
            'emprendedores' => $emprendedores,
            'flash' => [
                'message' => session('message')
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombreEmprendedor' => 'required|string|max:100',
            'apellido'          => 'required|string|max:100',
            'dni'               => 'nullable|string|max:20',
            'contacto'          => 'nullable|string|max:100',
            'email'             => 'nullable|email|max:150',
            'domicilio'         => 'nullable|string|max:255',
            'formalizacion'     => 'nullable|string|max:50',
        ]);

        Emprendedor::create($validated);

        return redirect()->back()->with('message', 'Emprendedor registrado exitosamente.');
    }

    public function destroy($id)
    {
        $emprendedor = Emprendedor::findOrFail($id);
        $emprendedor->delete();

        return redirect()->back()->with('message', 'Emprendedor eliminado correctamente.');
    }
}