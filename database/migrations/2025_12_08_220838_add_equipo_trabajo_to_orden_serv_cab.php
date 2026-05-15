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
            if (!Schema::hasColumn('orden_serv_cab', 'equipo_trabajo_id')) $table->unsignedBigInteger('equipo_trabajo_id')->nullable();
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
