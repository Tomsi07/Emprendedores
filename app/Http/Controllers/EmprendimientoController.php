<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Emprendedor;
use App\Models\Emprendimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class EmprendimientoController extends Controller
{
    public function index()
    {
        $emprendimientos = Emprendimiento::with(['emprendedores', 'actividades', 'necesidades'])
            ->orderByDesc('idEmprendimiento')
            ->get()
            ->map(function ($e) {
                $responsable = $e->emprendedores->first();
                $necesidad = $e->necesidades
                    ->whereNull('idEvento')
                    ->sortBy('idNecesidad')
                    ->first();

                return [
                    'idEmprendimiento'     => $e->idEmprendimiento,
                    'nombreEmprendimiento' => $e->nombreEmprendimiento,
                    'estado'               => $e->estado,
                    'rubro'                => $e->actividades->first()?->especializacion,
                    'productosServicios'   => $e->productosServicios,
                    'necesidades'          => $necesidad?->descripcion,
                    'emprendedor'          => $responsable ? [
                        'nombreEmprendedor' => $responsable->nombreEmprendedor,
                        'apellido'          => $responsable->apellido,
                        'telefono'          => $responsable->contacto,
                        'email'             => $responsable->email,
                    ] : null,
                ];
            })
            ->values();

        return Inertia::render('emprendedores/index', [
            'emprendimientos' => $emprendimientos,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validar($request);

        DB::transaction(function () use ($validated) {
            $emprendedor = $this->guardarResponsable($validated);

            $emprendimiento = Emprendimiento::create([
                'nombreEmprendimiento' => $validated['nombreEmprendimiento'],
                'estado'               => $validated['estado'],
                'productosServicios'   => $this->limpiar($validated['actividades'] ?? null),
            ]);

            $emprendimiento->emprendedores()->attach($emprendedor->getKey());
            $this->vincularRubro($emprendimiento, $validated['rubro'] ?? null);
            $this->guardarNecesidad($emprendimiento, $validated['necesidades'] ?? '');
        });

        return redirect()->back()->with('success', 'Emprendimiento guardado con éxito.');
    }

    public function update(Request $request, $id)
    {
        $validated = $this->validar($request);
        $emprendimiento = Emprendimiento::findOrFail($id);

        DB::transaction(function () use ($validated, $emprendimiento) {
            $emprendedor = $this->guardarResponsable($validated);

            $emprendimiento->update([
                'nombreEmprendimiento' => $validated['nombreEmprendimiento'],
                'estado'               => $validated['estado'],
                'productosServicios'   => $this->limpiar($validated['actividades'] ?? null),
            ]);

            $anterior = $emprendimiento->emprendedores()->first();
            if ($anterior && $anterior->getKey() !== $emprendedor->getKey()) {
                $emprendimiento->emprendedores()->detach($anterior->getKey());
            }
            $emprendimiento->emprendedores()->syncWithoutDetaching([$emprendedor->getKey()]);

            $this->vincularRubro($emprendimiento, $validated['rubro'] ?? null);
            $this->guardarNecesidad($emprendimiento, $validated['necesidades'] ?? '');
        });

        return redirect()->back()->with('success', 'Emprendimiento actualizado con éxito.');
    }

    public function destroy($id)
    {
        $emprendimiento = Emprendimiento::findOrFail($id);

        DB::transaction(function () use ($emprendimiento) {
            $responsables = $emprendimiento->emprendedores()->pluck('emprendedor.idEmprendedor');

            $emprendimiento->actividades()->detach();
            $emprendimiento->emprendedores()->detach();
            $emprendimiento->necesidades()->delete();
            DB::table('asistencia')
                ->where('idEmprendimiento', $emprendimiento->idEmprendimiento)
                ->delete();

            $emprendimiento->delete();

            foreach ($responsables as $idEmprendedor) {
                $tieneOtros = DB::table('emprendedor_emprendimiento')
                    ->where('idEmprendedor', $idEmprendedor)
                    ->exists();

                if (!$tieneOtros) {
                    Emprendedor::whereKey($idEmprendedor)->delete();
                }
            }
        });

        return redirect()->back()->with('success', 'Emprendimiento eliminado correctamente.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombreEmprendedor'    => 'required|string|max:255',
            'telefono'             => 'nullable|string|max:255',
            'email'                => 'nullable|email|max:255',
            'nombreEmprendimiento' => 'required|string|max:255',
            'rubro'                => 'nullable|string|max:255',
            'estado'               => 'required|string|in:Activo,Inactivo,Suspendido',
            'actividades'          => 'nullable|string|max:1000',
            'necesidades'          => 'nullable|string|max:255',
        ]);
    }

    private function limpiar(?string $texto): ?string
    {
        $texto = trim($texto ?? '');

        return $texto === '' ? null : $texto;
    }

    private function vincularRubro(Emprendimiento $emprendimiento, ?string $rubro): void
    {
        if (!$rubro) {
            $emprendimiento->actividades()->sync([]);
            return;
        }

        $actividad = Actividad::firstOrCreate(['especializacion' => $rubro]);
        $emprendimiento->actividades()->sync([$actividad->getKey()]);
    }

    private function guardarResponsable(array $v): Emprendedor
    {
        $emprendedor = Emprendedor::firstOrNew([
            'nombreEmprendedor' => trim($v['nombreEmprendedor']),
        ]);
        $esNuevo = !$emprendedor->exists;

        if ($esNuevo) {
            $emprendedor->apellido = '';
        }

        if (!empty($v['telefono'])) {
            $emprendedor->contacto = $v['telefono'];
        } elseif ($esNuevo) {
            $emprendedor->contacto = 'Sin teléfono';
        }

        if (!empty($v['email'])) {
            $emprendedor->email = $v['email'];
        } elseif ($esNuevo) {
            $emprendedor->email = 'sin_email_' . uniqid() . '@ejemplo.com';
        }

        $emprendedor->save();

        return $emprendedor;
    }

    private function guardarNecesidad(Emprendimiento $emprendimiento, string $texto): void
    {
        $texto = trim($texto);

        $general = $emprendimiento->necesidades()
            ->whereNull('idEvento')
            ->orderBy('idNecesidad')
            ->first();

        if ($texto === '') {
            $general?->delete();
            return;
        }

        if ($general) {
            $general->update(['descripcion' => $texto]);
        } else {
            $emprendimiento->necesidades()->create(['descripcion' => $texto]);
        }
    }
}