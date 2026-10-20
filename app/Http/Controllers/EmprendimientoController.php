<?php

namespace App\Http\Controllers;

use App\Models\Emprendimiento;
use App\Models\Emprendedor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmprendimientoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $emprendimientos = Emprendimiento::with(['emprendedor', 'actividades', 'necesidades'])
            ->when($search, function ($query, $search) {
                $query->where('nombreEmprendimiento', 'like', "%{$search}%")
                      ->orWhere('rubro', 'like', "%{$search}%");
            })
            ->latest('idEmprendimiento')
            ->paginate(10)
            ->withQueryString();

        $emprendedores = Emprendedor::all();

        return Inertia::render('emprendedores/index', [
            'emprendimientos' => $emprendimientos,
            'emprendedores'   => $emprendedores,
            'filters'         => ['search' => $search],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombreEmprendedor'    => 'required|string|max:255',
            'telefono'             => 'nullable|string|max:255',
            'email'                => 'nullable|email|max:255',
            'nombreEmprendimiento' => 'required|string|max:255',
            'rubro'                => 'nullable|string|max:255',
            'estado'               => 'required|string|in:Activo,Inactivo,Suspendido',
            'actividades'          => 'nullable|string',
            'necesidades'          => 'nullable|string',
        ]);

        $emprendedor = Emprendedor::updateOrCreate(
            ['nombreEmprendedor' => trim($validated['nombreEmprendedor'])],
            [
                'apellido' => '',
                'telefono' => $validated['telefono'] ?? 'Sin teléfono',
                'email'    => $validated['email'] ?? ('sin_email_' . uniqid() . '@ejemplo.com'),
            ]
        );

        $emprendimiento = Emprendimiento::create([
            'idEmprendedor'        => $emprendedor->getKey(),
            'nombreEmprendimiento' => $validated['nombreEmprendimiento'],
            'rubro'                => $validated['rubro'] ?? null,
            'estado'               => $validated['estado'] ?? 'Activo',
        ]);

        if (!empty(trim($validated['actividades'] ?? ''))) {
            $emprendimiento->actividades()->create([
                'nombreActividad' => trim($validated['actividades']),
                'estado'          => 'Pendiente'
            ]);
        }

        if (!empty(trim($validated['necesidades'] ?? ''))) {
            $emprendimiento->necesidades()->create([
                'titulo' => trim($validated['necesidades']),
                'tipo'   => 'General',
                'estado' => 'Pendiente'
            ]);
        }

        return redirect()->back()->with('success', 'Emprendimiento guardado con éxito.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nombreEmprendedor'    => 'required|string|max:255',
            'telefono'             => 'nullable|string|max:255',
            'email'                => 'nullable|email|max:255',
            'nombreEmprendimiento' => 'required|string|max:255',
            'rubro'                => 'nullable|string|max:255',
            'estado'               => 'required|string|in:Activo,Inactivo,Suspendido',
            'actividades'          => 'nullable|string',
            'necesidades'          => 'nullable|string',
        ]);

        $emprendimiento = Emprendimiento::findOrFail($id);

        $emprendedor = Emprendedor::updateOrCreate(
            ['nombreEmprendedor' => trim($validated['nombreEmprendedor'])],
            [
                'apellido' => '',
                'telefono' => $validated['telefono'] ?? 'Sin teléfono',
                'email'    => $validated['email'] ?? ($emprendimiento->emprendedor?->email ?? ('sin_email_' . uniqid() . '@ejemplo.com')),
            ]
        );

        $emprendimiento->update([
            'idEmprendedor'        => $emprendedor->getKey(),
            'nombreEmprendimiento' => $validated['nombreEmprendimiento'],
            'rubro'                => $validated['rubro'] ?? null,
            'estado'               => $validated['estado'] ?? 'Activo',
        ]);

        // Actualizar Actividades
        $emprendimiento->actividades()->delete();
        if (!empty(trim($validated['actividades'] ?? ''))) {
            $emprendimiento->actividades()->create([
                'nombreActividad' => trim($validated['actividades']),
                'estado'          => 'Pendiente'
            ]);
        }

        // Actualizar Necesidades
        $emprendimiento->necesidades()->delete();
        if (!empty(trim($validated['necesidades'] ?? ''))) {
            $emprendimiento->necesidades()->create([
                'titulo' => trim($validated['necesidades']),
                'tipo'   => 'General',
                'estado' => 'Pendiente'
            ]);
        }

        return redirect()->back()->with('success', 'Emprendimiento actualizado con éxito.');
    }

    public function destroy($id)
    {
        $emprendimiento = Emprendimiento::findOrFail($id);
        $emprendedorId = $emprendimiento->idEmprendedor;

        $emprendimiento->actividades()->delete();
        $emprendimiento->necesidades()->delete();

        if (method_exists($emprendimiento, 'forceDelete')) {
            $emprendimiento->forceDelete();
        } else {
            $emprendimiento->delete();
        }

        if ($emprendedorId) {
            $tieneOtros = Emprendimiento::where('idEmprendedor', $emprendedorId)->exists();
            if (!$tieneOtros) {
                $emprendedor = Emprendedor::find($emprendedorId);
                if ($emprendedor) {
                    if (method_exists($emprendedor, 'forceDelete')) {
                        $emprendedor->forceDelete();
                    } else {
                        $emprendedor->delete();
                    }
                }
            }
        }

        return redirect()->back()->with('success', 'Emprendimiento y emprendedor eliminados correctamente.');
    }
}