<?php

namespace App\Http\Controllers;

use App\Models\Emprendimiento;
use App\Models\Emprendedor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmprendimientoController extends Controller
{
    public function index(Request $request)
    {
        $query = Emprendimiento::with('emprendedor');

        if ($request->search) {
            $query->where('nombreEmprendimiento', 'like', "%{$request->search}%")
                  ->orWhere('rubro', 'like', "%{$request->search}%");
        }

        // Paginación de 10 registros por página
        $emprendimientos = $query->orderBy('idEmprendimiento', 'desc')->paginate(10)->withQueryString();

        return Inertia::render('emprendedores/index', [
            'emprendimientos' => $emprendimientos,
            'emprendedores'   => Emprendedor::select('idEmprendedor', 'nombreEmprendedor', 'apellido')->get(),
            'filters'         => $request->only(['search'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'idEmprendedor'          => 'required|exists:emprendedores,idEmprendedor',
            'nombreEmprendimiento'   => 'required|string|max:150',
            'rubro'                  => 'nullable|string|max:100',
            'estado'                 => 'required|string|max:50',
            'descripcion'            => 'nullable|string'
        ]);

        Emprendimiento::create($validated);

        return redirect()->back()->with('message', 'Emprendimiento creado exitosamente.');
    }

    public function destroy($id)
    {
        Emprendimiento::findOrFail($id)->delete();
        return redirect()->back()->with('message', 'Emprendimiento eliminado correctamente.');
    }
}