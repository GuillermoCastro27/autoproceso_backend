<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('notas_comp_cab') && !Schema::hasColumn('notas_comp_cab', 'nota_comp_nro_nota')) {
            Schema::table('notas_comp_cab', function (Blueprint $table) {
                $table->string('nota_comp_nro_nota', 15)->nullable()->after('nota_comp_timbrado');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('notas_comp_cab') && Schema::hasColumn('notas_comp_cab', 'nota_comp_nro_nota')) {
            Schema::table('notas_comp_cab', function (Blueprint $table) {
                $table->dropColumn('nota_comp_nro_nota');
            });
        }
    }
};
