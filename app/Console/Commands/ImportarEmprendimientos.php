<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportarEmprendimientos extends Command
{
    protected $signature = 'importar:emprendimientos {archivo=emprendimientos.csv}';

    protected $description = 'Importa emprendedores, emprendimientos y actividades desde un CSV';

    public function handle()
    {
        $ruta = storage_path('app/' . $this->argument('archivo'));

        if (!file_exists($ruta)) {
            $this->error("El archivo no se encuentra en: {$ruta}");
            return 1;
        }

        $this->info("Iniciando la importación desde: {$ruta}...");

        $handle = fopen($ruta, 'r');
        fgetcsv($handle, 2000, ';'); // título general
        fgetcsv($handle, 2000, ';'); // cabecera

        $insertados = 0;

        DB::beginTransaction();
        try {
            while (($datos = fgetcsv($handle, 2000, ';')) !== false) {
                // Pasar a UTF-8 si el CSV viene de Excel en otra codificación
                $datos = array_map(function ($v) {
                    return mb_check_encoding($v, 'UTF-8') ? $v : mb_convert_encoding($v, 'UTF-8', 'Windows-1252');
                }, $datos);

                if (!isset($datos[1]) || trim($datos[1]) === '') {
                    continue;
                }

                $nombre    = trim($datos[1]);
                $actividad = trim($datos[2] ?? '');
                $telefono  = trim($datos[6] ?? '');
                $email     = trim($datos[7] ?? '');

                // 1. Emprendedor
                $idEmprendedor = DB::table('emprendedor')
                    ->where('nombreEmprendedor', $nombre)
                    ->value('idEmprendedor');

                if (!$idEmprendedor) {
                    $idEmprendedor = DB::table('emprendedor')->insertGetId([
                        'nombreEmprendedor' => $nombre,
                        'apellido'          => '',
                        'contacto'          => $telefono ?: null,
                        'email'             => $email ?: null,
                    ]);
                }

                // 2. Emprendimiento (si hay paréntesis, usa lo de adentro como nombre)
                $nombreEmp = $nombre;
                if (preg_match('/\((.*?)\)/', $nombre, $m)) {
                    $nombreEmp = trim($m[1]);
                }

                $idEmprendimiento = DB::table('emprendimiento')
                    ->where('nombreEmprendimiento', $nombreEmp)
                    ->value('idEmprendimiento');

                if (!$idEmprendimiento) {
                    $idEmprendimiento = DB::table('emprendimiento')->insertGetId([
                        'nombreEmprendimiento' => $nombreEmp,
                        'contacto'             => $telefono ?: null,
                    ]);
                }

                // 3. Vínculo N:N emprendedor <-> emprendimiento
                DB::table('emprendedor_emprendimiento')->insertOrIgnore([
                    'idEmprendedor'    => $idEmprendedor,
                    'idEmprendimiento' => $idEmprendimiento,
                ]);

                // 4. Actividad + vínculo N:N con el emprendimiento
                if ($actividad !== '') {
                    $idActividad = DB::table('actividad')
                        ->where('especializacion', $actividad)
                        ->value('idActividad');

                    if (!$idActividad) {
                        $idActividad = DB::table('actividad')->insertGetId([
                            'especializacion' => $actividad,
                        ]);
                    }

                    DB::table('emprendimiento_actividad')->insertOrIgnore([
                        'idEmprendimiento' => $idEmprendimiento,
                        'idActividad'      => $idActividad,
                    ]);
                }

                // Necesidades: no se importan porque la tabla 'necesidad'
                // exige un idEvento que el CSV no trae.

                $insertados++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($handle);
            $this->error('Falló la importación, no se guardó nada: ' . $e->getMessage());
            return 1;
        }

        fclose($handle);
        $this->info("¡Listo! Se procesaron {$insertados} filas.");
        return 0;
    }
}