<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('insumos_det');
        Schema::dropIfExists('insumos_cab');
        Schema::dropIfExists('insumos_utilizados');

        Schema::create('insumos_cab', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('orden_serv_cab_id');
            $table->date('ins_cab_fecha_registro');
            $table->string('ins_cab_estado', 20)->default('PENDIENTE');
            $table->timestamps();

            $table->foreign('orden_serv_cab_id')->references('id')->on('orden_serv_cab');
        });

        Schema::create('insumos_det', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('insumos_cab_id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('tipo_impuesto_id');
            $table->decimal('ins_det_cantidad', 12, 2);
            $table->decimal('ins_det_costo',    14, 2);
            $table->unsignedBigInteger('marca_id')->nullable();
            $table->unsignedBigInteger('modelo_id')->nullable();
            $table->timestamps();

            $table->foreign('insumos_cab_id')->references('id')->on('insumos_cab')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('tipo_impuesto_id')->references('id')->on('tipo_impuesto');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insumos_det');
        Schema::dropIfExists('insumos_cab');
    }
};
