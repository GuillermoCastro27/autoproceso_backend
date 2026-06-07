<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipo_vehiculo', function (Blueprint $table) {
            if (!Schema::hasColumn('tipo_vehiculo', 'tv_uso')) {
                $table->string('tv_uso', 10)->default('SERVICIO')->after('tip_veh_nombre');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tipo_vehiculo', function (Blueprint $table) {
            if (Schema::hasColumn('tipo_vehiculo', 'tv_uso')) {
                $table->dropColumn('tv_uso');
            }
        });
    }
};
