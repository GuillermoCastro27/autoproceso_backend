<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('compra_cab') && !Schema::hasColumn('compra_cab', 'comp_fecha_emision')) {
            Schema::table('compra_cab', function (Blueprint $table) {
                $table->date('comp_fecha_emision')->nullable()->after('comp_fecha');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('compra_cab') && Schema::hasColumn('compra_cab', 'comp_fecha_emision')) {
            Schema::table('compra_cab', function (Blueprint $table) {
                $table->dropColumn('comp_fecha_emision');
            });
        }
    }
};
