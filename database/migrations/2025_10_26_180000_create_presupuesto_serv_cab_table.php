<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('presupuesto_serv_cab')) return;

        Schema::create('presupuesto_serv_cab', function (Blueprint $table) {
            $table->id();
            $table->string('pres_serv_cab_observaciones', 500)->nullable();
            $table->timestamp('pres_serv_cab_fecha')->nullable();
            $table->timestamp('pres_serv_cab_fecha_vence')->nullable();
            $table->string('pres_serv_cab_estado', 50)->nullable();
            $table->unsignedBigInteger('funcionario_id')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('sucursal_id')->nullable();
            $table->unsignedBigInteger('diagnostico_cab_id')->nullable();
            $table->unsignedBigInteger('tipo_vehiculo_id')->nullable();
            $table->unsignedBigInteger('tipo_servicio_id')->nullable();
            $table->unsignedBigInteger('promociones_cab_id')->nullable();
            $table->unsignedBigInteger('descuentos_cab_id')->nullable();
            $table->unsignedBigInteger('clientes_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presupuesto_serv_cab');
    }
};
