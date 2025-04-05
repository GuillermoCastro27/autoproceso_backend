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
        Schema::create('nota_remi_com_det', function (Blueprint $table) {
            $table->unsignedBigInteger('nota_remi_comp_id');
            $table->foreign('nota_remi_comp_id')->references('id')->on('nota_remi_comp')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('item_id');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('restrict')->onUpdate('cascade');
            $table->float('nota_remi_com_det_cantidad');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nota_remi_com_det');
    }
};
