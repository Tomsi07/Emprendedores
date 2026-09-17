<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActividadController extends Controller
{
    public function index(Request $request)
    {
        $query = Actividad::with('emprendimiento');

        if ($request->search) {
            $query->where('nombreActividad', 'like', "%{$request->search}%")
                  ->orWhereHas('emprendimiento', function ($q) use ($request) {
                      $q->where('nombreEmprendimiento', 'like', "%{$request->search}%");
                  });
        }

        $actividades = $query->orderBy('idActividad', 'desc')->paginate(10)->withQueryString();

        return Inertia::render('actividades/act_index', [
            'actividades' => $actividades,
            'filters'     => $request->only(['search'])
        ]);
    }

    public function destroy($id)
    {
        Actividad::findOrFail($id)->delete();
        return redirect()->back()->with('message', 'Actividad eliminada correctamente.');
    }
}