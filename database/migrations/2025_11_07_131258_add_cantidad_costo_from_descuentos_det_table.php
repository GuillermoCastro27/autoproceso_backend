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
        Schema::table('descuentos_det', function (Blueprint $table) {
            $table->unsignedBigInteger('tipo_impuesto_id')->nullable();
            $table->foreign('tipo_impuesto_id')
                  ->references('id')->on('tipo_impuesto')
                  ->onDelete('restrict')->onUpdate('cascade');

            $table->float('desc_det_cantidad')->nullable();
            $table->float('desc_det_costo')->nullable();
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
