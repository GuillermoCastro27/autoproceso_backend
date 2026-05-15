<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notas_comp_cab', function (Blueprint $table) {
            if (!Schema::hasColumn('notas_comp_cab', 'nota_comp_timbrado')) $table->string('nota_comp_timbrado', 20)->nullable()->after('nota_comp_observaciones');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notas_comp_cab', function (Blueprint $table) {
            if (Schema::hasColumn('notas_comp_cab', 'nota_comp_timbrado')) $table->dropColumn('nota_comp_timbrado');
        });
    }
};
