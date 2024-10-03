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
        Schema::create('presupuestos_detalles', function (Blueprint $table) {
            $table->unsignedBigInteger('presupuesto_id');
            $table->unsignedBigInteger('item_id');
            $table->foreign('presupuesto_id')->references('id')->on('presupuestos')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('restrict')->onUpdate('cascade');
            $table->integer('det_costo');
            $table->double('det_cantidad');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presupuestos_detalles');
    }
};
