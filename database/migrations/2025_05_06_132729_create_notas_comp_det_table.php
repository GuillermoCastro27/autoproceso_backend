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
        Schema::create('notas_comp_det', function (Blueprint $table) {
            $table->unsignedBigInteger('notas_comp_cab_id');
            $table->foreign('notas_comp_cab_id')->references('id')->on('notas_comp_cab')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('item_id');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('restrict')->onUpdate('cascade');
            $table->float('notas_comp_det_cantidad');
            $table->float('notas_comp_det_costo');
            $table->unsignedBigInteger('tipo_impuesto_id');
            $table->foreign('tipo_impuesto_id')->references('id')->on('tipo_impuesto')->onDelete('restrict')->onUpdate('cascade');
            $table->primary(['notas_comp_cab_id','item_id']); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notas_comp_det');
    }
};
