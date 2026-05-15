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
        Schema::create('compra_cab', function (Blueprint $table) {
            $table->id();
            $table->dateTime('comp_intervalo_fecha_vence')->nullable();
            $table->timestamp('comp_fecha');
            $table->string('comp_estado', 50);
            $table->string('comp_cant_cuota')->nullable();
            $table->string('condicion_pago', 20)->nullable(); 
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('orden_compra_cab_id');
            $table->unsignedBigInteger('proveedor_id')->nullable(); 
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
        Schema::dropIfExists('compra_cab');
    }
};
