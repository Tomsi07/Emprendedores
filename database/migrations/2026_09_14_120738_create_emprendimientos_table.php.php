<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
     Schema::create('emprendimientos', function (Blueprint $table) {
    $table->id('idEmprendimiento');
    $table->foreignId('idEmprendedor')->constrained('emprendedores', 'idEmprendedor')->onDelete('cascade');
    $table->string('nombreEmprendimiento');
    $table->string('rubro')->nullable();
    $table->string('estado')->default('Activo');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emprendimientos');
    }
};