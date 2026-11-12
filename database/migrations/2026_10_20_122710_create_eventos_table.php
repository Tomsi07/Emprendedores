<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('eventos')) {
            return;
        }

        Schema::create('eventos', function (Blueprint $table) {
            $table->id('idEvento');
            $table->string('nombreEvento');
            $table->string('lugar');
            $table->date('fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};