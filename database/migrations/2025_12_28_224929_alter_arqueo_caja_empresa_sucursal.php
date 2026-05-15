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
            if (Schema::hasColumn('arqueo_caja', 'empresa_id')) $table->dropColumn('empresa_id');
            if (Schema::hasColumn('arqueo_caja', 'sucursal_id')) $table->dropColumn('sucursal_id');
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
