<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ([
            'actividades',
            'emprendedores',
            'emprendimientos',
            'eventos',
            'necesidades',
        ] as $tabla) {
            Schema::dropIfExists($tabla);
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Irreversible a propósito: se restaura desde el respaldo .sql
    }
};