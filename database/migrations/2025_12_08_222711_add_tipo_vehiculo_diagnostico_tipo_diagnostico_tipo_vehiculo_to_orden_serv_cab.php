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
            if (!Schema::hasColumn('orden_serv_cab', 'diagnostico_cab_id')) $table->unsignedBigInteger('diagnostico_cab_id')->nullable()->after('presupuesto_serv_cab_id');
            if (!Schema::hasColumn('orden_serv_cab', 'tipo_diagnostico_id')) $table->unsignedBigInteger('tipo_diagnostico_id')->nullable()->after('diagnostico_cab_id');
            if (!Schema::hasColumn('orden_serv_cab', 'tipo_vehiculo_id')) $table->unsignedBigInteger('tipo_vehiculo_id')->nullable()->after('clientes_id');

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
