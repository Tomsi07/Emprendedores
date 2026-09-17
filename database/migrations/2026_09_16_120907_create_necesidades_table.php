<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('necesidades', function (Blueprint $table) {
            $table->id('idNecesidad');
            $table->unsignedBigInteger('idEmprendimiento');
            $table->string('titulo');
            $table->string('tipo'); // Ej: Financiamiento, Maquinaria, Capacitación
            $table->text('descripcion')->nullable();
            $table->enum('prioridad', ['Baja', 'Media', 'Alta'])->default('Media');
            $table->enum('estado', ['Pendiente', 'En gestion', 'Resuelta'])->default('Pendiente');
            $table->timestamps();

            $table->foreign('idEmprendimiento')
                  ->references('idEmprendimiento')
                  ->on('emprendimientos')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('necesidades');
    }
};