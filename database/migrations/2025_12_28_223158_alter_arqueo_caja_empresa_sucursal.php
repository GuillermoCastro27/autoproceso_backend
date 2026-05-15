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
        Schema::table('arqueo_caja', function (Blueprint $table) {
            if (!Schema::hasColumn('arqueo_caja', 'sucursal_id')) $table->unsignedBigInteger('sucursal_id')->nullable();
            if (!Schema::hasColumn('arqueo_caja', 'empresa_id')) $table->unsignedBigInteger('empresa_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};