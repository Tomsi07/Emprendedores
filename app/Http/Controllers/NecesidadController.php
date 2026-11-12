<?php

namespace App\Http\Controllers;

use App\Models\Necesidad;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NecesidadController extends Controller
{
    public function index()
    {
        // Se envían todas las necesidades; la búsqueda y la paginación se hacen en el navegador
        $necesidades = Necesidad::with('emprendimiento:idEmprendimiento,nombreEmprendimiento')
            ->orderByDesc('idNecesidad')
            ->get(['idNecesidad', 'idEmprendimiento', 'descripcion'])
            ->map(fn ($n) => [
                'idNecesidad'          => $n->idNecesidad,
                'descripcion'          => $n->descripcion,
                'nombreEmprendimiento' => $n->emprendimiento?->nombreEmprendimiento,
            ])
            ->values();

        return Inertia::render('necesidades/nec_index', [
            'necesidades' => $necesidades,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'descripcion' => 'required|string|max:255',
        ]);

        $necesidad = Necesidad::findOrFail($id);
        $necesidad->update(['descripcion' => trim($validated['descripcion'])]);

        return redirect()->back()->with('success', 'Necesidad actualizada con éxito.');
    }

    public function destroy($id)
    {
        $necesidad = Necesidad::findOrFail($id);
        $necesidad->delete();

        return redirect()->back()->with('success', 'Necesidad eliminada con éxito.');
    }
}