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
            $table->unsignedBigInteger('user_id');
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
        Schema::dropIfExists('notas_comp_cab');
    }
};
