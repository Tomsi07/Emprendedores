<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Opcional: Crear un usuario por defecto para probar el login
        User::factory()->create([
            'name' => 'Usuario Admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'), // Contraseña de prueba
        ]);

        // Llamada a los seeders de tu sistema
        $this->call([
            EmprendimientosSeeder::class,
        ]);
    }
}