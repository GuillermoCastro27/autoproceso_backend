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
        Schema::create('recep_det', function (Blueprint $table) {
            $table->unsignedBigInteger('recep_cab_id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('tipo_impuesto_id');
            $table->float('recep_det_cantidad');
            $table->float('recep_det_costo');
            $table->integer('recep_det_cantidad_stock');
            $table->primary(['recep_cab_id','item_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recep_det');
    }
};
