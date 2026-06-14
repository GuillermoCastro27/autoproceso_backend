<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nota_remi_comp', function (Blueprint $table) {
            if (!Schema::hasColumn('nota_remi_comp', 'conductor_id')) {
                $table->unsignedBigInteger('conductor_id')->nullable()->after('funcionario_id');
                $table->foreign('conductor_id')->references('id')->on('funcionario')->nullOnDelete();
            }
            if (!Schema::hasColumn('nota_remi_comp', 'tipo_vehiculo_det_id')) {
                $table->unsignedBigInteger('tipo_vehiculo_det_id')->nullable()->after('conductor_id');
                $table->foreign('tipo_vehiculo_det_id')->references('id')->on('tipo_vehiculo_det')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('nota_remi_comp', function (Blueprint $table) {
            if (Schema::hasColumn('nota_remi_comp', 'conductor_id')) {
                $table->dropForeign(['conductor_id']);
                $table->dropColumn('conductor_id');
            }
            if (Schema::hasColumn('nota_remi_comp', 'tipo_vehiculo_det_id')) {
                $table->dropForeign(['tipo_vehiculo_det_id']);
                $table->dropColumn('tipo_vehiculo_det_id');
            }
        });
    }
};
