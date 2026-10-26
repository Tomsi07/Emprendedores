<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $eventos = Evento::when($search, function ($query, $search) {
                $query->where('nombreEvento', 'like', "%{$search}%")
                      ->orWhere('lugar', 'like', "%{$search}%");
            })
            ->latest('idEvento')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('eventos/eve_index', [
            'eventos' => $eventos,
            'filters' => ['search' => $search],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombreEvento' => 'required|string|max:255',
            'lugar'        => 'required|string|max:255',
            'fecha'        => 'required|date',
        ]);

        Evento::create($validated);

        return redirect()->back()->with('success', 'Evento registrado con éxito.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nombreEvento' => 'required|string|max:255',
            'lugar'        => 'required|string|max:255',
            'fecha'        => 'required|date',
        ]);

        $evento = Evento::findOrFail($id);
        $evento->update($validated);

        return redirect()->back()->with('success', 'Evento actualizado con éxito.');
    }

    public function destroy($id)
    {
        $evento = Evento::findOrFail($id);
        $evento->delete();

        return redirect()->back()->with('success', 'Evento eliminado correctamente.');
    }
}