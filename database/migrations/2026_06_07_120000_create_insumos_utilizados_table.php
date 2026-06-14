<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('insumos_utilizados')) return;

        Schema::create('insumos_utilizados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('orden_serv_cab_id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('tipo_impuesto_id');
            $table->decimal('ins_util_cantidad', 12, 2);
            $table->decimal('ins_util_costo',    14, 2);
            $table->timestamps();

            $table->foreign('orden_serv_cab_id')->references('id')->on('orden_serv_cab');
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('tipo_impuesto_id')->references('id')->on('tipo_impuesto');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insumos_utilizados');
    }
};
