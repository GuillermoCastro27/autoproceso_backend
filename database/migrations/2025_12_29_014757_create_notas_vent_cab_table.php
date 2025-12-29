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
        Schema::create('notas_vent_cab', function (Blueprint $table) {
            $table->id();
            $table->timestamp('nota_vent_intervalo_fecha_vence');
            $table->timestamp('nota_vent_fecha');
            $table->string('nota_vent_estado', 50);
            $table->integer('nota_vent_cant_cuota');
            $table->string('nota_vent_tipo', 20);
            $table->string('nota_vent_observaciones', 200);
            $table->string('nota_vene_condicion_pago', 20);
            $table->unsignedBigInteger('clientes_id');
            $table->foreign('clientes_id')->references('id')->on('clientes')->onDelete('restrict');
            $table->unsignedBigInteger('ventas_cab_id');
            $table->foreign('ventas_cab_id')->references('id')->on('ventas_cab')->onDelete('restrict');
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
        Schema::dropIfExists('notas_vent_cab');
    }
};
