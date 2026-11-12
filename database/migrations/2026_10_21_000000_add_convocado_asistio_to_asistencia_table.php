<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asistencia', function (Blueprint $table) {
            if (! Schema::hasColumn('asistencia', 'convocado')) {
                $table->boolean('convocado')->default(false);
            }
            if (! Schema::hasColumn('asistencia', 'asistio')) {
                $table->boolean('asistio')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('asistencia', function (Blueprint $table) {
            if (Schema::hasColumn('asistencia', 'convocado')) {
                $table->dropColumn('convocado');
            }
            if (Schema::hasColumn('asistencia', 'asistio')) {
                $table->dropColumn('asistio');
            }
        });
    }
};
