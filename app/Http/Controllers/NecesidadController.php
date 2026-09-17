<?php

namespace App\Http\Controllers;

use App\Models\Necesidad;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NecesidadController extends Controller
{
    public function index(Request $request)
    {
        $query = Necesidad::with('emprendimiento');

        if ($request->search) {
            $query->where('descripcion', 'like', "%{$request->search}%")
                  ->orWhere('titulo', 'like', "%{$request->search}%")
                  ->orWhereHas('emprendimiento', function ($q) use ($request) {
                      $q->where('nombreEmprendimiento', 'like', "%{$request->search}%");
                  });
        }

        $necesidades = $query->orderBy('idNecesidad', 'desc')->paginate(10)->withQueryString();

        return Inertia::render('necesidades/nec_index', [
            'necesidades' => $necesidades,
            'filters'     => $request->only(['search'])
        ]);
    }

    public function destroy($id)
    {
        Necesidad::findOrFail($id)->delete();
        return redirect()->back()->with('message', 'Necesidad eliminada correctamente.');
    }
}