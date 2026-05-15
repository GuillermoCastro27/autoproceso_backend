<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('apertura_cierre_caja', function (Blueprint $table) {

            // ðŸ•’ Datos de cierre
            if (!Schema::hasColumn('apertura_cierre_caja', 'fecha_cierre')) $table->timestamp('fecha_cierre')->nullable();
            if (!Schema::hasColumn('apertura_cierre_caja', 'user_cierre_id')) $table->unsignedBigInteger('user_cierre_id')->nullable();

            // ðŸ’° Totales de cierre
            if (!Schema::hasColumn('apertura_cierre_caja', 'monto_efectivo_cierre')) $table->decimal('monto_efectivo_cierre', 14, 2)->nullable();
            if (!Schema::hasColumn('apertura_cierre_caja', 'monto_tarjeta_cierre')) $table->decimal('monto_tarjeta_cierre', 14, 2)->nullable();
            if (!Schema::hasColumn('apertura_cierre_caja', 'monto_cheque_cierre')) $table->decimal('monto_cheque_cierre', 14, 2)->nullable();

            // ðŸ” FK usuario cierre
        });
    }

    public function down(): void
    {
        Schema::table('apertura_cierre_caja', function (Blueprint $table) {


            $table->dropColumn([
                'fecha_cierre',
                'user_cierre_id',
                'monto_efectivo_cierre',
                'monto_tarjeta_cierre',
                'monto_cheque_cierre'
            ]);
        });
    }
};
