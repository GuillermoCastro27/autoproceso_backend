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
        Schema::create('notas_comp_cab', function (Blueprint $table) {
            $table->id();
            $table->timestamp('nota_comp_intervalo_fecha_vence');
            $table->timestamp('nota_comp_fecha');
            $table->string('nota_comp_estado', 50);
            $table->integer('nota_comp_cant_cuota');
            $table->string('nota_comp_tipo', 20);
            $table->string('nota_comp_observaciones', 200);
            $table->string('nota_comp_condicion_pago', 20);
            $table->unsignedBigInteger('compra_cab_id');
            $table->foreign('compra_cab_id')->references('id')->on('compra_cab')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('empresa')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('sucursal_id');
            $table->foreign('sucursal_id')->references('empresa_id')->on('sucursal')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notas_comp_cab');
    }
};
