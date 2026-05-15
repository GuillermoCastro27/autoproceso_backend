<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('descuentos_det')) return;

        Schema::create('descuentos_det', function (Blueprint $table) {
            $table->unsignedBigInteger('descuentos_cab_id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('tipo_impuesto_id')->nullable();
            $table->float('desc_det_cantidad')->nullable();
            $table->float('desc_det_costo')->nullable();
            $table->primary(['descuentos_cab_id', 'item_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('descuentos_det');
    }
};
