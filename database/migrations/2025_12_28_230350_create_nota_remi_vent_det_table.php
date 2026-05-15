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
        Schema::create('nota_remi_vent_det', function (Blueprint $table) {
            $table->unsignedBigInteger('nota_remi_vent_id');
            $table->unsignedBigInteger('item_id');
            $table->float('nota_remi_vent_det_cantidad');
            $table->primary(['nota_remi_vent_id','item_id']); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nota_remi_vent_det');
    }
};
