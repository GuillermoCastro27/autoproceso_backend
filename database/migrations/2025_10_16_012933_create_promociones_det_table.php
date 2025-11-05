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
        Schema::create('promociones_det', function (Blueprint $table) {
            $table->unsignedBigInteger('promociones_cab_id');
            $table->foreign('promociones_cab_id')
                  ->references('id')->on('promociones_cab')
                  ->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('item_id');
            $table->foreign('item_id')
                  ->references('id')->on('items')
                  ->onDelete('restrict')->onUpdate('cascade');
            
            $table->unsignedBigInteger('tipo_impuesto_id');
            $table->foreign('tipo_impuesto_id')
                  ->references('id')->on('tipo_impuesto')
                  ->onDelete('restrict')->onUpdate('cascade');

            $table->float('prom_det_cantidad');
            $table->float('prom_det_costo');

            $table->primary(['promociones_cab_id', 'item_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promociones_det');
    }
};
