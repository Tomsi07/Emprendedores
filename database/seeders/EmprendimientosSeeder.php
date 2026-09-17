<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Emprendedor;
use App\Models\Emprendimiento;
use App\Models\Actividad;
use App\Models\Necesidad;
use Illuminate\Support\Facades\DB;

class EmprendimientosSeeder extends Seeder
{
    public function run(): void
    {
        $csvFile = database_path('seeders/emprendimientos.csv');

        if (!file_exists($csvFile)) {
            $this->command->error("El archivo CSV no existe en: {$csvFile}");
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Actividad::truncate();
        Necesidad::truncate();
        Emprendimiento::truncate();
        Emprendedor::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $handle = fopen($csvFile, 'r');
        
        // Omitir la primera fila de encabezados
        fgetcsv($handle, 1000, ';');

        while (($data = fgetcsv($handle, 1000, ';')) !== FALSE) {
            
            $nombreRaw      = $data[1] ?? null; // Ej: "Guerini Soledad" o "Claudio (Otivia)"
            $actividadComer = $data[2] ?? null; // Ej: "Maquillaje" o "Artesanias: Merceria"
            $necesidadesTxt = $data[3] ?? null;
            $eventoTxt      = $data[4] ?? null;
            $telefono       = $data[6] ?? null;
            $email          = $data[7] ?? null;

            if (!empty(trim($nombreRaw))) {

                $nombrePersona = trim($nombreRaw);
                $nombreMarca   = null;

                // 1. Si viene con marca entre paréntesis: "Persona (Marca)"
                if (preg_match('/^(.*?)\s*\((.*?)\)$/', $nombreRaw, $matches)) {
                    $nombrePersona = trim($matches[1]);
                    $nombreMarca   = trim($matches[2]);
                } else {
                    // 2. Si NO viene paréntesis, usar el Rubro/Actividad como nombre del emprendimiento
                    if (!empty(trim($actividadComer))) {
                        $nombreMarca = trim($actividadComer);
                    } else {
                        $nombreMarca = 'Emprendimiento de ' . $nombrePersona;
                    }
                }

                $emailLimpio = !empty(trim($email)) ? trim($email) : 'sin_email_' . uniqid() . '@ejemplo.com';
                
                // Crear Emprendedor (Persona)
                $emprendedor = Emprendedor::firstOrCreate(
                    ['email' => $emailLimpio],
                    [
                        'nombreEmprendedor' => $nombrePersona,
                        'apellido'          => '',
                        'telefono'          => !empty(trim($telefono)) ? trim($telefono) : 'Sin teléfono'
                    ]
                );

                // Determinar Rubro corto
                $rubroExtraido = !empty($actividadComer) ? trim(explode(':', explode('-', $actividadComer)[0])[0]) : 'General';

                // Crear Emprendimiento (Marca / Proyecto)
                $emprendimiento = Emprendimiento::create([
                    'idEmprendedor'        => $emprendedor->idEmprendedor,
                    'nombreEmprendimiento' => $nombreMarca, // Ej: "Maquillaje" o "Otivia"
                    'rubro'                => $rubroExtraido,
                    'estado'               => 'Activo',
                ]);

                // Crear Actividad
                if (!empty(trim($actividadComer))) {
                    Actividad::create([
                        'idEmprendimiento' => $emprendimiento->idEmprendimiento,
                        'nombreActividad'  => trim($actividadComer),
                        'descripcion'      => !empty(trim($eventoTxt)) ? 'Evento: ' . trim($eventoTxt) : 'Actividad principal',
                        'estado'           => 'Pendiente'
                    ]);
                }

                // Crear Necesidad
                if (!empty(trim($necesidadesTxt))) {
                    Necesidad::create([
                        'idEmprendimiento' => $emprendimiento->idEmprendimiento,
                        'titulo'           => 'Requerimientos iniciales',
                        'tipo'             => 'General',
                        'descripcion'      => trim($necesidadesTxt),
                        'prioridad'        => 'Media',
                        'estado'           => 'Pendiente'
                    ]);
                }
            }
        }

        fclose($handle);
        $this->command->info('Base de datos actualizada con nombres y marcas separados.');
    }
}