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
        Schema::create('cobros_det', function (Blueprint $table) {
             $table->unsignedBigInteger('cobros_cab_id');
            $table->foreign('cobros_cab_id')
                ->references('id')->on('cobros_cab')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            // Item vendido
            $table->unsignedBigInteger('item_id');
            $table->foreign('item_id')
                ->references('id')->on('items')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            // Datos del detalle
            $table->float('cob_det_cantidad');
            $table->float('cob_det_precio');

            // Impuesto
            $table->unsignedBigInteger('tipo_impuesto_id');
            $table->foreign('tipo_impuesto_id')
                ->references('id')->on('tipo_impuesto')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            // PK compuesta (una línea por item)
            $table->primary(['cobros_cab_id', 'item_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cobros_det');
    }
};
