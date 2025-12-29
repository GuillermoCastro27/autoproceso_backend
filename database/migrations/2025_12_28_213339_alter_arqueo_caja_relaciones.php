<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('arqueo_caja', function (Blueprint $table) {

            /*
             |--------------------------------------------------
             | El arqueo SE RELACIONA con empresa y sucursal
             | A TRAVÉS de apertura_cierre_caja
             |--------------------------------------------------
             */

            // 🔥 Eliminamos duplicaciones si existen
            if (Schema::hasColumn('arqueo_caja', 'empresa_id')) {
                $table->dropColumn('empresa_id');
            }

            if (Schema::hasColumn('arqueo_caja', 'sucursal_id')) {
                $table->dropColumn('sucursal_id');
            }

            // ✅ Relación REAL con apertura/cierre
            $table->foreign('apertura_cierre_caja_id')
                  ->references('id')
                  ->on('apertura_cierre_caja')
                  ->onDelete('restrict');

            // ✅ Usuario que realiza el arqueo
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('arqueo_caja', function (Blueprint $table) {

            $table->dropForeign(['apertura_cierre_caja_id']);
            $table->dropForeign(['user_id']);

            // solo para rollback
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('sucursal_id')->nullable();
        });
    }
};
