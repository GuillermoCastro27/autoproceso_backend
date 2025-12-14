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
        Schema::table('orden_serv_cab', function (Blueprint $table) {
            $table->unsignedBigInteger('diagnostico_cab_id')->nullable()->after('presupuesto_serv_cab_id');
            $table->unsignedBigInteger('tipo_diagnostico_id')->nullable()->after('diagnostico_cab_id');
            $table->unsignedBigInteger('tipo_vehiculo_id')->nullable()->after('clientes_id');

            $table->foreign('diagnostico_cab_id')->references('id')->on('diagnostico_cab');
            $table->foreign('tipo_diagnostico_id')->references('id')->on('tipo_diagnostico');
            $table->foreign('tipo_vehiculo_id')->references('id')->on('tipo_vehiculo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orden_serv_cab', function (Blueprint $table) {
            //
        });
    }
};
