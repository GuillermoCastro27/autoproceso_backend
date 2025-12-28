<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('arqueo_caja', function (Blueprint $table) {
            // Primero eliminar el unique
            $table->dropUnique(['arqueo_nro']);

            // Luego eliminar la columna
            $table->dropColumn('arqueo_nro');
        });
    }

    public function down(): void
    {
        Schema::table('arqueo_caja', function (Blueprint $table) {
            $table->string('arqueo_nro')->unique();
        });
    }
};
