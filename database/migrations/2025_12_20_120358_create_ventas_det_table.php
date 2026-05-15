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
        Schema::create('ventas_det', function (Blueprint $table) {

            // FK a cabecera de venta
            $table->unsignedBigInteger('ventas_cab_id');

            // Item vendido
            $table->unsignedBigInteger('item_id');

            // Datos del detalle
            $table->float('vent_det_cantidad');
            $table->float('vent_det_precio');

            // Impuesto
            $table->unsignedBigInteger('tipo_impuesto_id');

            // PK compuesta (una lÃ­nea por item)
            $table->primary(['ventas_cab_id', 'item_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas_det');
    }
};
