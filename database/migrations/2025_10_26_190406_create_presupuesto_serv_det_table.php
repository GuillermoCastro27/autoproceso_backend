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
        Schema::create('presupuesto_serv_det', function (Blueprint $table) {
            $table->unsignedBigInteger('presupuesto_serv_cab_id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('tipo_impuesto_id');
            $table->float('pres_serv_det_cantidad');
            $table->float('pres_serv_det_costo');
            $table->integer('pres_serv_det_cantidad_stock');
            $table->primary(['presupuesto_serv_cab_id','item_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presupuesto_serv_det');
    }
};
