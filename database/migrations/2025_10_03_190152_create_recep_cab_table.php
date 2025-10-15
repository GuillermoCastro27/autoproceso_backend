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
        Schema::create('recep_cab', function (Blueprint $table) {
            $table->id();
            $table->string('recep_cab_observaciones',100);
            $table->timestamp('recep_cab_fecha');
            $table->timestamp('recep_cab_fecha_estimada');
            $table->string('recep_cab_kilometraje');
            $table->string('recep_cab_nivel_combustible');
            $table->string('recep_cab_estado',50);
            $table->string('recep_cab_prioridad',50);
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('clientes_id')->nullable(); 
            $table->foreign('clientes_id')->references('id')->on('clientes')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('empresa')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('sucursal_id');
            $table->foreign('sucursal_id')->references('empresa_id')->on('sucursal')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('solicitudes_cab_id');
            $table->foreign('solicitudes_cab_id')->references('id')->on('solicitudes_cab')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('tipo_servicio_id');
            $table->foreign('tipo_servicio_id')->references('id')->on('tipo_servicio')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recep_cab');
    }
};
