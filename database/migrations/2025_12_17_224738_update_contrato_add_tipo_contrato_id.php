<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contrato_serv_cab', function (Blueprint $table) {

            // ðŸ”¹ Eliminar columna antigua (texto)
            if (Schema::hasColumn('contrato_serv_cab', 'tipo_contrato')) {
                $table->dropColumn('tipo_contrato');
            }

            // ðŸ”¹ Nueva relaciÃ³n
            if (!Schema::hasColumn('contrato_serv_cab', 'tipo_contrato_id')) $table->unsignedBigInteger('tipo_contrato_id')->nullable()->after('id');

        });
    }

    public function down(): void
    {
        Schema::table('contrato_serv_cab', function (Blueprint $table) {

            // ðŸ”„ Quitar FK
            $table->dropColumn('tipo_contrato_id');

            // ðŸ”„ Restaurar columna anterior (por si rollback)
            if (!Schema::hasColumn('contrato_serv_cab', 'tipo_contrato')) $table->string('tipo_contrato')->nullable();
        });
    }
};
