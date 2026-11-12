<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class EventoController extends Controller
{
    // Tiene que coincidir con la lista de la pantalla (eve_index.vue)
    private const TIPOS = ['Capacitación', 'Encuentro', 'Exposición', 'Feria'];

    public function index()
    {
        // Se envían todos los eventos; la búsqueda y la paginación se hacen en el navegador
        $eventos = Evento::query()
            ->orderByDesc('fecha')
            ->orderByDesc('idEvento')
            ->get(['idEvento', 'nombreEvento', 'ciudad', 'lugar', 'fecha', 'tipoDeEvento'])
            ->map(fn ($e) => [
                'idEvento'     => $e->idEvento,
                'nombreEvento' => $e->nombreEvento,
                'ciudad'       => $e->ciudad,
                'lugar'        => $e->lugar,
                'tipoDeEvento' => $e->tipoDeEvento,
                'fecha'        => $e->fecha ? substr((string) $e->fecha, 0, 10) : null,
            ])
            ->values();

        return Inertia::render('eventos/eve_index', [
            'eventos' => $eventos,
        ]);
    }

    public function store(Request $request)
    {
        Evento::create($this->validar($request));

        return redirect()->back()->with('success', 'Evento registrado con éxito.');
    }

    public function update(Request $request, $id)
    {
        $evento = Evento::findOrFail($id);
        $evento->update($this->validar($request));

        return redirect()->back()->with('success', 'Evento actualizado con éxito.');
    }

    public function destroy($id)
    {
        $evento = Evento::findOrFail($id);
        $evento->delete();

        return redirect()->back()->with('success', 'Evento eliminado correctamente.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombreEvento' => 'required|string|max:255',
            'ciudad'       => 'required|string|max:255',
            'lugar'        => 'required|string|max:255',
            'tipoDeEvento' => ['required', 'string', Rule::in(self::TIPOS)],
            'fecha'        => 'required|date',
        ]);
    }
}