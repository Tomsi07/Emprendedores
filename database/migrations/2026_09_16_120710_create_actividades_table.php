<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actividades', function (Blueprint $table) {
            $table->id('idActividad');
            $table->unsignedBigInteger('idEmprendimiento');
            $table->string('nombreActividad');
            $table->text('descripcion')->nullable();
            $table->date('fechaInicio')->nullable();
            $table->date('fechaFin')->nullable();
            $table->enum('estado', ['Pendiente', 'En proceso', 'Finalizada'])->default('Pendiente');
            $table->timestamps();

            $table->foreign('idEmprendimiento')
                  ->references('idEmprendimiento')
                  ->on('emprendimientos')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actividades');
    }
};