<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $rubros = [
        'Artesanías / Manualidades',
        'Gastronomía / Alimenticio',
        'Textil e Indumentaria',
        'Viveril / Botánica',
        'Carpintería / Herrería / Metalúrgica',
        'Servicios',
        'Producción Agropecuaria / Frutihortícola',
        'Comercio / Gráfica / Diseño',
        'Otro',
    ];

    public function up(): void
    {
        if (! Schema::hasColumn('emprendimiento', 'productosServicios')) {
            Schema::table('emprendimiento', function (Blueprint $table) {
                $table->text('productosServicios')->nullable();
            });
        }

        foreach ($this->rubros as $rubro) {
            if (! DB::table('actividad')->where('especializacion', $rubro)->exists()) {
                DB::table('actividad')->insert(['especializacion' => $rubro]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('emprendimiento', function (Blueprint $table) {
            $table->dropColumn('productosServicios');
        });
    }
};