<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Agregar pais_id a nacionalidad (nullable para no romper registros existentes)
        if (Schema::hasTable('nacionalidad') && !Schema::hasColumn('nacionalidad', 'pais_id')) {
            Schema::table('nacionalidad', function (Blueprint $table) {
                $table->unsignedBigInteger('pais_id')->nullable()->after('nacio_descripcion');
                $table->foreign('pais_id')->references('id')->on('paises')->nullOnDelete();
            });
        }

        // Quitar pais_gentilicio de paises
        if (Schema::hasTable('paises') && Schema::hasColumn('paises', 'pais_gentilicio')) {
            Schema::table('paises', function (Blueprint $table) {
                $table->dropColumn('pais_gentilicio');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('paises') && !Schema::hasColumn('paises', 'pais_gentilicio')) {
            Schema::table('paises', function (Blueprint $table) {
                $table->string('pais_gentilicio', 100)->nullable();
            });
        }

        if (Schema::hasTable('nacionalidad') && Schema::hasColumn('nacionalidad', 'pais_id')) {
            Schema::table('nacionalidad', function (Blueprint $table) {
                $table->dropForeign(['pais_id']);
                $table->dropColumn('pais_id');
            });
        }
    }
};
