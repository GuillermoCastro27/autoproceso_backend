<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('cobros_tarjeta', function (Blueprint $table) {

            // ❗ Primero eliminar FKs
            $table->dropForeign(['entidad_emisora_id']);
            $table->dropForeign(['marca_tarjeta_id']);
            $table->dropForeign(['entidad_adherida_id']);

            // 🔁 Renombrar columnas
            $table->renameColumn('entidad_emisora_id', 'entidad_emisora_tarjeta_id');
            $table->renameColumn('marca_tarjeta_id', 'marca_tarjeta_tarjeta_id');
            $table->renameColumn('entidad_adherida_id', 'entidad_adherida_tarjeta_id');
        });

        Schema::table('cobros_tarjeta', function (Blueprint $table) {

            // 🔗 Volver a crear FKs
            $table->foreign('entidad_emisora_tarjeta_id')
                ->references('id')->on('entidad_emisora');

            $table->foreign('marca_tarjeta_tarjeta_id')
                ->references('id')->on('marca_tarjeta');

            $table->foreign('entidad_adherida_tarjeta_id')
                ->references('id')->on('entidad_adherida');
        });
    }

    public function down(): void
    {
        Schema::table('cobros_tarjeta', function (Blueprint $table) {

            $table->dropForeign(['entidad_emisora_tarjeta_id']);
            $table->dropForeign(['marca_tarjeta_tarjeta_id']);
            $table->dropForeign(['entidad_adherida_tarjeta_id']);

            $table->renameColumn('entidad_emisora_tarjeta_id', 'entidad_emisora_id');
            $table->renameColumn('marca_tarjeta_tarjeta_id', 'marca_tarjeta_id');
            $table->renameColumn('entidad_adherida_tarjeta_id', 'entidad_adherida_id');
        });

        Schema::table('cobros_tarjeta', function (Blueprint $table) {

            $table->foreign('entidad_emisora_id')
                ->references('id')->on('entidad_emisora');

            $table->foreign('marca_tarjeta_id')
                ->references('id')->on('marca_tarjeta');

            $table->foreign('entidad_adherida_id')
                ->references('id')->on('entidad_adherida');
        });
    }
};
