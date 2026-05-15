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
            $table->unsignedBigInteger('clientes_id')->nullable(); 
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('sucursal_id');
            $table->unsignedBigInteger('solicitudes_cab_id');
            $table->unsignedBigInteger('tipo_servicio_id');
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
