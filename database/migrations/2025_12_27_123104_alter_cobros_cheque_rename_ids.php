<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cobros_cheque', function (Blueprint $table) {

            // ðŸ”¥ eliminar FK primero

            // ðŸ” renombrar columna
            if (Schema::hasColumn('cobros_cheque', 'entidad_emisora_id') && !Schema::hasColumn('cobros_cheque', 'entidad_emisora_cheque_id')) $table->renameColumn('entidad_emisora_id', 'entidad_emisora_cheque_id');
        });

        Schema::table('cobros_cheque', function (Blueprint $table) {

            // ðŸ”— recrear FK con el nuevo nombre
        });
    }

    public function down(): void
    {
        Schema::table('cobros_cheque', function (Blueprint $table) {


            $table->renameColumn('entidad_emisora_cheque_id', 'entidad_emisora_id');
        });

        Schema::table('cobros_cheque', function (Blueprint $table) {

        });
    }
};
