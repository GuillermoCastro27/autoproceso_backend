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
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('orden_compra_cab_id');
            $table->foreign('orden_compra_cab_id')->references('id')->on('orden_compra_cab')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('proveedor_id')->nullable(); 
            $table->foreign('proveedor_id')->references('id')->on('proveedores')->onDelete('restrict')->onUpdate('cascade');
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
        Schema::dropIfExists('compra_cab');
    }
};
