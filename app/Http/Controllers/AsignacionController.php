<?php

namespace App\Http\Controllers;

use App\Models\Emprendimiento;
use App\Models\Evento;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AsignacionController extends Controller
{
    // Eventos agrupados por año (el más reciente arriba)
    public function index()
    {
        $grupos = Evento::orderByDesc('fecha')
            ->get(['idEvento', 'nombreEvento', 'fecha', 'lugar'])
            ->groupBy(fn ($e) => $e->fecha ? substr($e->fecha, 0, 4) : 'Sin fecha')
            ->map(fn ($eventos, $anio) => [
                'anio'    => (string) $anio,
                'eventos' => $eventos->values(),
            ])
            ->values();

        return Inertia::render('asignaciones/index', ['grupos' => $grupos]);
    }

    // Detalle del evento + todos los emprendimientos con sus checklists
    public function show(Evento $evento)
    {
        $marcados = $evento->emprendimientos()
            ->withPivot('convocado', 'asistio')
            ->get()
            ->keyBy('idEmprendimiento');

        $emprendimientos = Emprendimiento::orderBy('nombreEmprendimiento')
            ->get()
            ->map(function ($e) use ($marcados) {
                $m = $marcados->get($e->idEmprendimiento);

                return [
                    'idEmprendimiento'     => $e->idEmprendimiento,
                    'nombreEmprendimiento' => $e->nombreEmprendimiento,
                    'rubro'                => $e->rubro ?? null,
                    'convocado'            => (bool) ($m?->pivot->convocado ?? false),
                    'asistio'              => (bool) ($m?->pivot->asistio ?? false),
                ];
            });

        return Inertia::render('asignaciones/show', [
            'evento' => $evento->only([
                'idEvento', 'nombreEvento', 'ciudad', 'lugar', 'fecha', 'tipoDeEvento',
            ]),
            'emprendimientos' => $emprendimientos,
        ]);
    }

    // Marca o desmarca "convocado" / "asistió" para un emprendimiento
    public function update(Request $request, Evento $evento, Emprendimiento $emprendimiento)
    {
        $datos = $request->validate([
            'convocado' => ['required', 'boolean'],
            'asistio'   => ['required', 'boolean'],
        ]);

        if (! $datos['convocado'] && ! $datos['asistio']) {
            $evento->emprendimientos()->detach($emprendimiento->getKey());
        } else {
            $evento->emprendimientos()->syncWithoutDetaching([
                $emprendimiento->getKey() => $datos,
            ]);
        }

        return back();
    }
}