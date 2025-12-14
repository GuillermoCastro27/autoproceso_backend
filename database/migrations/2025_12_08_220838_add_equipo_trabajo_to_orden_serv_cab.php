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
            $table->unsignedBigInteger('equipo_trabajo_id')->nullable();
            $table->foreign('equipo_trabajo_id')->references('id')->on('equipo_trabajo');
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
