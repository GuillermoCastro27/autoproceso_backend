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
        if (!Schema::hasTable('presupuesto_serv_cab')) return;

        Schema::table('presupuesto_serv_cab', function (Blueprint $table) {
            if (!Schema::hasColumn('presupuesto_serv_cab', 'tipo_vehiculo_id')) $table->unsignedBigInteger('tipo_vehiculo_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presupuesto_serv_cab', function (Blueprint $table) {
            //
        });
    }
};
