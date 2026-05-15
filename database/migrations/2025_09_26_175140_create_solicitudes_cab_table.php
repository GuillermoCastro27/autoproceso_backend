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
        Schema::create('solicitudes_cab', function (Blueprint $table) {
            $table->id();
            $table->string('soli_cab_observaciones',100);
            $table->timestamp('soli_cab_fecha');
            $table->timestamp('soli_cab_fecha_estimada');
            $table->string('soli_cab_prioridad');
            $table->string('soli_cab_estado',50);
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('clientes_id')->nullable(); 
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('sucursal_id');
            $table->unsignedBigInteger('tipo_servicio_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitudes_cab');
    }
};
