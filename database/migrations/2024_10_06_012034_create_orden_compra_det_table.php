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
        Schema::create('orden_compra_det', function (Blueprint $table) {
            $table->unsignedBigInteger('orden_compra_cab_id');
            $table->unsignedBigInteger('item_id');
            $table->float('orden_compra_det_cantidad');
            $table->primary(['orden_compra_cab_id','item_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_compra_det');
    }
};
