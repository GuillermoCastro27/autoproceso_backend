<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('recaudaciones_depositar')) return;

        Schema::create('recaudaciones_depositar', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('apertura_cierre_caja_id');
            $table->string('reca_dep_met_pago', 50);          // EFECTIVO, CHEQUE, TARJETA...
            $table->string('reca_dep_estado', 20);             // PENDIENTE | DEPOSITADO | ANULADO
            $table->timestamp('reca_dep_fecha');
            $table->string('reca_dep_obs', 500)->nullable();

            $table->timestamps();

            $table->foreign('apertura_cierre_caja_id')
                  ->references('id')
                  ->on('apertura_cierre_caja');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recaudaciones_depositar');
    }
};
