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
        if (!Schema::hasTable('descuentos_det')) return;

        Schema::table('descuentos_det', function (Blueprint $table) {
            if (!Schema::hasColumn('descuentos_det', 'tipo_impuesto_id')) $table->unsignedBigInteger('tipo_impuesto_id')->nullable();

            if (!Schema::hasColumn('descuentos_det', 'desc_det_cantidad')) $table->float('desc_det_cantidad')->nullable();
            if (!Schema::hasColumn('descuentos_det', 'desc_det_costo')) $table->float('desc_det_costo')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promociones_det', function (Blueprint $table) {
            //
        });
    }
};
