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
            $table->unsignedBigInteger('item_id');
            $table->float('notas_comp_det_cantidad');
            $table->float('notas_comp_det_costo');
            $table->unsignedBigInteger('tipo_impuesto_id');
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
