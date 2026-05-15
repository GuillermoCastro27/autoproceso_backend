<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('cobros_tarjeta', function (Blueprint $table) {

            // â— Primero eliminar FKs

            // ðŸ” Renombrar columnas
            $table->renameColumn('entidad_emisora_id', 'entidad_emisora_tarjeta_id');
            $table->renameColumn('marca_tarjeta_id', 'marca_tarjeta_tarjeta_id');
            $table->renameColumn('entidad_adherida_id', 'entidad_adherida_tarjeta_id');
        });

        Schema::table('cobros_tarjeta', function (Blueprint $table) {

            // ðŸ”— Volver a crear FKs


        });
    }

    public function down(): void
    {
        Schema::table('cobros_tarjeta', function (Blueprint $table) {


            $table->renameColumn('entidad_emisora_tarjeta_id', 'entidad_emisora_id');
            $table->renameColumn('marca_tarjeta_tarjeta_id', 'marca_tarjeta_id');
            $table->renameColumn('entidad_adherida_tarjeta_id', 'entidad_adherida_id');
        });

        Schema::table('cobros_tarjeta', function (Blueprint $table) {



        });
    }
};
