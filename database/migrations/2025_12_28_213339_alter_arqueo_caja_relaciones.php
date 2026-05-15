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
             | A TRAVÃ‰S de apertura_cierre_caja
             |--------------------------------------------------
             */

            // ðŸ”¥ Eliminamos duplicaciones si existen
            if (Schema::hasColumn('arqueo_caja', 'empresa_id')) {
                $table->dropColumn('empresa_id');
            }

            if (Schema::hasColumn('arqueo_caja', 'sucursal_id')) {
                $table->dropColumn('sucursal_id');
            }

            // âœ… RelaciÃ³n REAL con apertura/cierre

            // âœ… Usuario que realiza el arqueo
        });
    }

    public function down(): void
    {
        Schema::table('arqueo_caja', function (Blueprint $table) {


            // solo para rollback
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('sucursal_id')->nullable();
        });
    }
};
