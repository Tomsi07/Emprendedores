<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Aseguramos las columnas en emprendedores
        Schema::table('emprendedores', function (Blueprint $table) {
            if (!Schema::hasColumn('emprendedores', 'telefono')) {
                $table->string('telefono')->nullable();
            }
        });

        // Aseguramos las columnas en emprendimientos
        Schema::table('emprendimientos', function (Blueprint $table) {
            if (!Schema::hasColumn('emprendimientos', 'rubro')) {
                $table->string('rubro')->nullable();
            }
            if (!Schema::hasColumn('emprendimientos', 'idEmprendedor')) {
                $table->unsignedBigInteger('idEmprendedor')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Opcional por si hay que dar marcha atrás
    }
};