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
        Schema::create('orden_compra_cab', function (Blueprint $table) {
            $table->id();
            $table->timestamp('ord_comp_intervalo_fecha_vence');
            $table->timestamp('ord_comp_fecha');
            $table->string('ord_comp_estado', 50);
            $table->integer('ord_comp_cant_cuota');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('presupuesto_id');
            $table->unsignedBigInteger('proveedor_id');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('sucursal_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_compra_cab');
    }
};
