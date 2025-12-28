<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cobros_cheque', function (Blueprint $table) {

            // 🔥 eliminar FK primero
            $table->dropForeign(['entidad_emisora_id']);

            // 🔁 renombrar columna
            $table->renameColumn('entidad_emisora_id', 'entidad_emisora_cheque_id');
        });

        Schema::table('cobros_cheque', function (Blueprint $table) {

            // 🔗 recrear FK con el nuevo nombre
            $table->foreign('entidad_emisora_cheque_id')
                ->references('id')
                ->on('entidad_emisora');
        });
    }

    public function down(): void
    {
        Schema::table('cobros_cheque', function (Blueprint $table) {

            $table->dropForeign(['entidad_emisora_cheque_id']);

            $table->renameColumn('entidad_emisora_cheque_id', 'entidad_emisora_id');
        });

        Schema::table('cobros_cheque', function (Blueprint $table) {

            $table->foreign('entidad_emisora_id')
                ->references('id')
                ->on('entidad_emisora');
        });
    }
};
