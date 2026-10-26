<?php

namespace App\Http\Controllers;

use App\Models\Necesidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class NecesidadController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $necesidades = Necesidad::with('emprendimiento')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    // Columnas posibles en la tabla necesidades
                    $posiblesColumnas = ['titulo', 'descripcion', 'detalle', 'tipo'];
                    $primera = true;

                    foreach ($posiblesColumnas as $columna) {
                        if (Schema::hasColumn('necesidades', $columna)) {
                            if ($primera) {
                                $q->where($columna, 'like', "%{$search}%");
                                $primera = false;
                            } else {
                                $q->orWhere($columna, 'like', "%{$search}%");
                            }
                        }
                    }
                });
            })
            ->latest('idNecesidad')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('necesidades/nec_index', [
            'necesidades' => $necesidades,
            'filters'     => ['search' => $search],
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
        ]);

        $necesidad = Necesidad::findOrFail($id);
        
        $necesidad->update([
            'titulo' => $validated['titulo'],
        ]);

        return redirect()->back()->with('success', 'Necesidad actualizada con éxito.');
    }

    public function destroy($id)
    {
        $necesidad = Necesidad::findOrFail($id);
        $necesidad->delete();

        return redirect()->back()->with('success', 'Necesidad eliminada con éxito.');
    }
}