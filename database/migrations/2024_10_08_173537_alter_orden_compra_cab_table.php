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
        Schema::table('orden_compra_cab', function (Blueprint $table) {
            // Eliminar la relaciÃ³n y la columna proveedor_id
            if (Schema::hasColumn('orden_compra_cab', 'proveedor_id')) $table->dropColumn('proveedor_id');
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
