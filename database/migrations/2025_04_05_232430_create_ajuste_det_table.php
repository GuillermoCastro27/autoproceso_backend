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
        Schema::create('ajuste_det', function (Blueprint $table) {
            $table->unsignedBigInteger('ajuste_cab_id');
            $table->foreign('ajuste_cab_id')->references('id')->on('ajuste_cab')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('item_id');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('restrict')->onUpdate('cascade');
            $table->integer('cantidad_stock');
            $table->float('ajus_det_cantidad');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ajuste_det');
    }
};
