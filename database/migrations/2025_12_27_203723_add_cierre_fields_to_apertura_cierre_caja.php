<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('apertura_cierre_caja', function (Blueprint $table) {

            // 🕒 Datos de cierre
            $table->timestamp('fecha_cierre')->nullable();
            $table->unsignedBigInteger('user_cierre_id')->nullable();

            // 💰 Totales de cierre
            $table->decimal('monto_efectivo_cierre', 14, 2)->nullable();
            $table->decimal('monto_tarjeta_cierre', 14, 2)->nullable();
            $table->decimal('monto_cheque_cierre', 14, 2)->nullable();

            // 🔐 FK usuario cierre
            $table->foreign('user_cierre_id')
                  ->references('id')
                  ->on('users');
        });
    }

    public function down(): void
    {
        Schema::table('apertura_cierre_caja', function (Blueprint $table) {

            $table->dropForeign(['user_cierre_id']);

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