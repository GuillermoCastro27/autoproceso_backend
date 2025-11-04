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
        Schema::create('contrato_serv_det', function (Blueprint $table) {
            // 🔹 Relación con cabecera
            $table->unsignedBigInteger('contrato_serv_cab_id');
            $table->foreign('contrato_serv_cab_id')
                  ->references('id')->on('contrato_serv_cab')
                  ->onDelete('restrict')->onUpdate('cascade');

            // 🔹 Relación con ítems
            $table->unsignedBigInteger('item_id');
            $table->foreign('item_id')
                  ->references('id')->on('items')
                  ->onDelete('restrict')->onUpdate('cascade');

            // 🔹 Relación con tipo de impuesto
            $table->unsignedBigInteger('tipo_impuesto_id');
            $table->foreign('tipo_impuesto_id')
                  ->references('id')->on('tipo_impuesto')
                  ->onDelete('restrict')->onUpdate('cascade');

            // 🔹 Datos principales
            $table->decimal('contrato_serv_det_cantidad', 15, 2);
            $table->decimal('contrato_serv_det_costo', 15, 2);
            $table->integer('contrato_serv_det_cantidad_stock')->default(0);

            // 🔹 Clave primaria compuesta
            $table->primary(['contrato_serv_cab_id', 'item_id']);

            // 🔹 Timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contrato_serv_det');
    }
};
