<?php

namespace App\Http\Controllers;

use App\Models\Emprendimiento;
use App\Models\Evento;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

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
    
    public function show(Evento $evento)
    {
        $marcados = $evento->emprendimientos()
            ->withPivot('convocado', 'asistio')
            ->get()
            ->keyBy('idEmprendimiento');

        // Rubro: actividades.especializacion (varios se unen con coma)
        $rubros = DB::table('emprendimiento_actividad as ea')
            ->join('actividades as a', 'a.idActividad', '=', 'ea.idActividad')
            ->select('ea.idEmprendimiento', 'a.especializacion')
            ->get()
            ->groupBy('idEmprendimiento')
            ->map(fn ($filas) => $filas->pluck('especializacion')->filter()->unique()->implode(', '));

        // Responsables y sus datos de contacto
        $personas = DB::table('emprendedor_emprendimiento as ee')
            ->join('emprendedores as p', 'p.idEmprendedor', '=', 'ee.idEmprendedor')
            ->select('ee.idEmprendimiento', 'p.nombreEmprendedor', 'p.apellido', 'p.contacto', 'p.email')
            ->get()
            ->groupBy('idEmprendimiento');

        $emprendimientos = Emprendimiento::orderBy('nombreEmprendimiento')
            ->get()
            ->map(function ($e) use ($marcados, $rubros, $personas) {
                $m = $marcados->get($e->idEmprendimiento);
                $gente = $personas->get($e->idEmprendimiento, collect());

                return [
                    'idEmprendimiento'     => $e->idEmprendimiento,
                    'nombreEmprendimiento' => $e->nombreEmprendimiento,
                    'rubro'                => $rubros->get($e->idEmprendimiento) ?: null,
                    'responsables'         => $gente
                        ->map(fn ($p) => trim($p->nombreEmprendedor . ' ' . $p->apellido))
                        ->filter()->values()->all(),
                    'contacto'             => collect([$e->contacto])
                        ->merge($gente->flatMap(fn ($p) => [$p->contacto, $p->email]))
                        ->filter()->unique()->values()->all(),
                    'convocado'            => (bool) ($m?->pivot->convocado ?? false),
                    'asistio'              => (bool) ($m?->pivot->asistio ?? false),
                ];
            });

        return Inertia::render('asignaciones/show', [
            'evento' => $evento->only([
                'idEvento', 'nombreEvento', 'ciudad', 'lugar', 'fecha', 'fechaFin', 'tipoDeEvento',
            ]),
            'emprendimientos' => $emprendimientos,
        ]);
    }

    // Marca o desmarca "convocado" / "asistió" para un emprendimiento (mutuamente excluyentes)
    public function update(Request $request, Evento $evento, Emprendimiento $emprendimiento)
    {
        $datos = $request->validate([
            'convocado' => ['required', 'boolean'],
            'asistio'   => ['required', 'boolean'],
        ]);

        if ($datos['convocado'] && $datos['asistio']) {
            abort(422, 'Un emprendimiento no puede estar marcado como convocado y asistió al mismo tiempo.');
        }

        if (! $datos['convocado'] && ! $datos['asistio']) {
            $evento->emprendimientos()->detach($emprendimiento->getKey());
        } else {
            $evento->emprendimientos()->syncWithoutDetaching([
                $emprendimiento->getKey() => $datos,
            ]);
        }

        if ($request->expectsJson()) {
            return response()->noContent();
        }

        return back();
    }

    public function updateMasivo(Request $request, int $idEvento)
    {
        abort_unless(
            DB::table('eventos')->where('idEvento', $idEvento)->exists(),
            404
        );

        $datos = $request->validate([
            'campo' => ['required', 'in:convocado,asistio'],
            'valor' => ['required', 'boolean'],
            'ids'   => ['required', 'array', 'max:20000'],
            'ids.*' => ['integer'],
        ]);

        $campo = $datos['campo'];
        $otro  = $campo === 'convocado' ? 'asistio' : 'convocado';
        $valor = (bool) $datos['valor'];

        DB::transaction(function () use ($datos, $idEvento, $campo, $otro, $valor) {
            foreach (array_chunk(array_unique($datos['ids']), 1000) as $lote) {
                $validos = DB::table('emprendimientos')
                    ->whereIn('idEmprendimiento', $lote)
                    ->pluck('idEmprendimiento')
                    ->all();

                if (empty($validos)) {
                    continue;
                }

                $existentes = DB::table('asistencia')
                    ->where('idEvento', $idEvento)
                    ->whereIn('idEmprendimiento', $validos)
                    ->pluck('idEmprendimiento')
                    ->all();

                if (!empty($existentes)) {
                    $cambios = [$campo => $valor];
                    if ($valor) {
                        $cambios[$otro] = false;
                    }

                    DB::table('asistencia')
                        ->where('idEvento', $idEvento)
                        ->whereIn('idEmprendimiento', $existentes)
                        ->update($cambios);

                    DB::table('asistencia')
                        ->where('idEvento', $idEvento)
                        ->whereIn('idEmprendimiento', $existentes)
                        ->where('convocado', false)
                        ->where('asistio', false)
                        ->delete();
                }

                $nuevos = array_values(array_diff($validos, $existentes));

                if (!empty($nuevos) && $valor) {
                    DB::table('asistencia')->insert(
                        array_map(fn ($id) => [
                            'idEvento'         => $idEvento,
                            'idEmprendimiento' => $id,
                            $campo             => true,
                            $otro              => false,
                        ], $nuevos)
                    );
                }
            }
        });

        return response()->noContent();
    }

    public function exportar(int $idEvento)
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(120);
 
        $evento = DB::table('eventos')->where('idEvento', $idEvento)->first();
        abort_unless($evento, 404);
 
        $asistentes = $this->asistentesDelEvento($idEvento);
        abort_if($asistentes->isEmpty(), 422, 'Todavía no hay asistentes marcados en este evento.');
 
        $porRubro = $asistentes
            ->groupBy(fn ($a) => $a->rubro ?: 'Sin rubro')
            ->map(fn ($items) => $items->sortBy(fn ($a) => mb_strtolower($a->nombreEmprendimiento))->values())
            ->sortBy(fn ($items, $rubro) => $rubro === 'Sin rubro' ? 'zzzz' : mb_strtolower($rubro));
 
        $exportadoEl = now()->timezone('America/Argentina/Buenos_Aires');
 
        $pdf = Pdf::loadView('pdf.asistentes', [
            'evento'      => $evento,
            'porRubro'    => $porRubro,
            'total'       => $asistentes->count(),
            'exportadoEl' => $exportadoEl,
        ])->setPaper('a4', 'portrait');
 
        return $pdf->download(
            'asistentes-' . Str::slug($evento->nombreEvento) . '-' . $exportadoEl->format('Y-m-d') . '.pdf'
        );
    }

    private function asistentesDelEvento(int $idEvento)
    {
        $asistieron = fn ($q) => $q->select('idEmprendimiento')
            ->from('asistencia')
            ->where('idEvento', $idEvento)
            ->where('asistio', true);
 
        $emprendimientos = DB::table('emprendimientos')
            ->whereIn('idEmprendimiento', $asistieron)
            ->select('idEmprendimiento', 'nombreEmprendimiento')
            ->get();
 
        $rubros = DB::table('emprendimiento_actividad as ea')
            ->join('actividades as ac', 'ac.idActividad', '=', 'ea.idActividad')
            ->whereIn('ea.idEmprendimiento', $asistieron)
            ->select('ea.idEmprendimiento', 'ac.especializacion')
            ->get()
            ->groupBy('idEmprendimiento');
 
        $responsables = DB::table('emprendedor_emprendimiento as ee')
            ->join('emprendedores as r', 'r.idEmprendedor', '=', 'ee.idEmprendedor')
            ->whereIn('ee.idEmprendimiento', $asistieron)
            ->orderBy('r.apellido')
            ->orderBy('r.nombreEmprendedor')
            ->select('ee.idEmprendimiento', 'r.nombreEmprendedor', 'r.apellido', 'r.contacto', 'r.email')
            ->get()
            ->groupBy('idEmprendimiento');
 
        return $emprendimientos->map(function ($e) use ($rubros, $responsables) {
            $especializaciones = ($rubros[$e->idEmprendimiento] ?? collect())
                ->pluck('especializacion')
                ->filter()
                ->unique()
                ->sort()
                ->values();
 
            $personas = $responsables[$e->idEmprendimiento] ?? collect();
 
            return (object) [
                'idEmprendimiento'     => $e->idEmprendimiento,
                'nombreEmprendimiento' => $e->nombreEmprendimiento,
                'rubro'                => $especializaciones->implode(', ') ?: null,
                'responsables'         => $personas
                    ->map(fn ($p) => trim($p->nombreEmprendedor . ' ' . $p->apellido))
                    ->filter()
                    ->implode(', '),
                'contacto'             => $personas
                    ->map(fn ($p) => collect([$p->contacto, $p->email])->filter()->implode(' / ') ?: '-')
                    ->all(),
            ];
        });
    }
}