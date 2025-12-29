<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('arqueo_caja', function (Blueprint $table) {

            // 🔴 eliminar unique de arqueo_nro (si existe)
            if (Schema::hasColumn('arqueo_caja', 'arqueo_nro')) {
                $table->dropUnique(['arqueo_nro']);
                $table->dropColumn('arqueo_nro');
            }

            // 🔴 eliminar columnas de totales
            $table->dropColumn([
                'total_efectivo',
                'total_cheque',
                'total_tarjeta',
                'total_general'
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('arqueo_caja', function (Blueprint $table) {

            // 🔙 restaurar arqueo_nro
            $table->string('arqueo_nro')->unique();

            // 🔙 restaurar totales
            $table->decimal('total_efectivo', 15, 2)->default(0);
            $table->decimal('total_cheque', 15, 2)->default(0);
            $table->decimal('total_tarjeta', 15, 2)->default(0);
            $table->decimal('total_general', 15, 2)->default(0);
        });
    }
};
