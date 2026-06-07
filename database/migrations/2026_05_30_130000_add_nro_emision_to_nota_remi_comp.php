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
                if (!Schema::hasColumn('nota_remi_comp', 'nota_remi_nro'))
                    $table->string('nota_remi_nro', 15)->nullable()->after('proveedor_id');
                if (!Schema::hasColumn('nota_remi_comp', 'nota_remi_fecha_emision'))
                    $table->date('nota_remi_fecha_emision')->nullable()->after('nota_remi_nro');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('nota_remi_comp')) {
            Schema::table('nota_remi_comp', function (Blueprint $table) {
                if (Schema::hasColumn('nota_remi_comp', 'nota_remi_nro'))
                    $table->dropColumn('nota_remi_nro');
                if (Schema::hasColumn('nota_remi_comp', 'nota_remi_fecha_emision'))
                    $table->dropColumn('nota_remi_fecha_emision');
            });
        }
    }
};
