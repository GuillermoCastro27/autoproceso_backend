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

    // Eliminar FKs primero
    $table->dropForeign(['empresa_id']);
    $table->dropForeign(['sucursal_id']);

    // Eliminar columnas
    $table->dropColumn(['empresa_id', 'sucursal_id']);
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