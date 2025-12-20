<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contrato_serv_cab', function (Blueprint $table) {

            // 🔹 Eliminar columna antigua (texto)
            if (Schema::hasColumn('contrato_serv_cab', 'tipo_contrato')) {
                $table->dropColumn('tipo_contrato');
            }

            // 🔹 Nueva relación
            $table->unsignedBigInteger('tipo_contrato_id')->nullable()->after('id');

            $table->foreign('tipo_contrato_id')
                ->references('id')
                ->on('tipo_contrato')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('contrato_serv_cab', function (Blueprint $table) {

            // 🔄 Quitar FK
            $table->dropForeign(['tipo_contrato_id']);
            $table->dropColumn('tipo_contrato_id');

            // 🔄 Restaurar columna anterior (por si rollback)
            $table->string('tipo_contrato')->nullable();
        });
    }
};
