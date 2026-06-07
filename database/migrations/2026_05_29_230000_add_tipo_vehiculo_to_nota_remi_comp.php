<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('nota_remi_comp') && !Schema::hasColumn('nota_remi_comp', 'tipo_vehiculo')) {
            Schema::table('nota_remi_comp', function (Blueprint $table) {
                $table->string('tipo_vehiculo', 20)->nullable()->after('vehiculo_nro');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('nota_remi_comp') && Schema::hasColumn('nota_remi_comp', 'tipo_vehiculo')) {
            Schema::table('nota_remi_comp', function (Blueprint $table) {
                $table->dropColumn('tipo_vehiculo');
            });
        }
    }
};
