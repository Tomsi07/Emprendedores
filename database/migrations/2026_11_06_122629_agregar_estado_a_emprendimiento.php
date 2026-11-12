<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('emprendimiento', 'estado')) {
            Schema::table('emprendimiento', function (Blueprint $table) {
                $table->enum('estado', ['Activo', 'Inactivo', 'Suspendido'])
                    ->default('Activo')
                    ->after('domicilio');
            });
        }
    }

    public function down(): void
    {
        Schema::table('emprendimiento', function (Blueprint $table) {
            $table->dropColumn('estado');
        });
    }
};