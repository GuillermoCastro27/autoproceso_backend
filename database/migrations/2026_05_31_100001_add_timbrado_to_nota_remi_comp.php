<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('nota_remi_comp')) {
            Schema::table('nota_remi_comp', function (Blueprint $table) {
                if (!Schema::hasColumn('nota_remi_comp', 'timbrado_id')) {
                    $table->unsignedBigInteger('timbrado_id')->nullable()->after('tipo');
                    $table->foreign('timbrado_id')->references('id')->on('timbrado');
                }
                if (!Schema::hasColumn('nota_remi_comp', 'nota_remi_nro_comp')) {
                    $table->unsignedInteger('nota_remi_nro_comp')->nullable()->after('timbrado_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('nota_remi_comp')) {
            Schema::table('nota_remi_comp', function (Blueprint $table) {
                if (Schema::hasColumn('nota_remi_comp', 'timbrado_id')) {
                    $table->dropForeign(['timbrado_id']);
                    $table->dropColumn('timbrado_id');
                }
                if (Schema::hasColumn('nota_remi_comp', 'nota_remi_nro_comp'))
                    $table->dropColumn('nota_remi_nro_comp');
            });
        }
    }
};
