<?php

namespace App\Http\Controllers;

use App\Models\Emprendedor;
use App\Models\Emprendimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class EmprendimientoController extends Controller
{
    public function index()
    {
        $emprendimientos = Emprendimiento::with(['emprendedor', 'necesidades'])
            ->orderByDesc('idEmprendimiento')
            ->get()
            ->map(function ($e) {
                $responsable = $e->emprendedor;
                $necesidad = $e->necesidades
                    ->whereNull('idEvento')
                    ->sortBy('idNecesidad')
                    ->first();

                return [
                    'idEmprendimiento'     => $e->idEmprendimiento,
                    'nombreEmprendimiento' => $e->nombreEmprendimiento,
                    'estado'               => $e->estado,
                    'rubro'                => $e->rubro,
                    'productosServicios'   => $e->productosServicios,
                    'necesidades'          => $necesidad?->descripcion,
                    'emprendedor'          => $responsable ? [
                        'idEmprendedor'     => $responsable->idEmprendedor,
                        'nombreEmprendedor' => $responsable->nombreEmprendedor,
                        'apellido'          => $responsable->apellido ?? '',
                        'telefono'          => $responsable->telefono, // Apunta directo a la columna telefono
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

            Emprendimiento::create([
                'idEmprendedor'        => $emprendedor->getKey(),
                'nombreEmprendimiento' => $validated['nombreEmprendimiento'],
                'estado'               => $validated['estado'],
                'rubro'                => $validated['rubro'] ?? null,
                'productosServicios'   => $this->limpiar($validated['actividades'] ?? null),
            ]);
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
                'idEmprendedor'        => $emprendedor->getKey(),
                'nombreEmprendimiento' => $validated['nombreEmprendimiento'],
                'estado'               => $validated['estado'],
                'rubro'                => $validated['rubro'] ?? null,
                'productosServicios'   => $this->limpiar($validated['actividades'] ?? null),
            ]);
        });

        return redirect()->back()->with('success', 'Emprendimiento actualizado con éxito.');
    }

    public function destroy($id)
    {
        $emprendimiento = Emprendimiento::findOrFail($id);

        DB::transaction(function () use ($emprendimiento) {
            $emprendimiento->necesidades()->delete();
            DB::table('asistencia')
                ->where('idEmprendimiento', $emprendimiento->idEmprendimiento)
                ->delete();

            $emprendimiento->delete();
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

    private function guardarResponsable(array $v): Emprendedor
    {
        $emprendedor = Emprendedor::firstOrNew([
            'nombreEmprendedor' => trim($v['nombreEmprendedor']),
        ]);
        
        if (!$emprendedor->exists) {
            $emprendedor->apellido = '';
        }

        $emprendedor->telefono = !empty($v['telefono']) ? $v['telefono'] : 'Sin teléfono';
        $emprendedor->email = !empty($v['email']) ? $v['email'] : 'sin_email_' . uniqid() . '@ejemplo.com';
        $emprendedor->save();

        return $emprendedor;
    }
}