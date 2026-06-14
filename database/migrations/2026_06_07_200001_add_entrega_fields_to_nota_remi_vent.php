<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('nota_remi_vent')) return;

        Schema::table('nota_remi_vent', function (Blueprint $table) {
            if (!Schema::hasColumn('nota_remi_vent', 'funcionario_entrega_id')) {
                $table->unsignedBigInteger('funcionario_entrega_id')->nullable();
            }
            if (!Schema::hasColumn('nota_remi_vent', 'tipo_vehiculo_det_id')) {
                $table->unsignedBigInteger('tipo_vehiculo_det_id')->nullable();
            }
            if (!Schema::hasColumn('nota_remi_vent', 'timbrado_id')) {
                $table->unsignedBigInteger('timbrado_id')->nullable();
            }
            if (!Schema::hasColumn('nota_remi_vent', 'nota_remi_vent_nro_comprobante')) {
                $table->unsignedInteger('nota_remi_vent_nro_comprobante')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('nota_remi_vent')) return;

        Schema::table('nota_remi_vent', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('nota_remi_vent', 'funcionario_entrega_id')      ? 'funcionario_entrega_id'             : null,
                Schema::hasColumn('nota_remi_vent', 'tipo_vehiculo_det_id')        ? 'tipo_vehiculo_det_id'               : null,
                Schema::hasColumn('nota_remi_vent', 'timbrado_id')                 ? 'timbrado_id'                        : null,
                Schema::hasColumn('nota_remi_vent', 'nota_remi_vent_nro_comprobante') ? 'nota_remi_vent_nro_comprobante'  : null,
            ]));
        });
    }
};
