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
        Schema::table('diagnostico_cab', function (Blueprint $table) {
            $table->unsignedBigInteger('tipo_servicio_id')->nullable();
            $table->foreign('tipo_servicio_id')->references('id')->on('tipo_servicio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diagnostico_cab', function (Blueprint $table) {
            //
        });
    }
};
