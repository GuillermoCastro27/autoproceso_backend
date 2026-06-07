<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('compra_cab') && !Schema::hasColumn('compra_cab', 'comp_nro_factura')) {
            Schema::table('compra_cab', function (Blueprint $table) {
                $table->string('comp_nro_factura', 15)->nullable()->after('comp_timbrado');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('compra_cab') && Schema::hasColumn('compra_cab', 'comp_nro_factura')) {
            Schema::table('compra_cab', function (Blueprint $table) {
                $table->dropColumn('comp_nro_factura');
            });
        }
    }
};
