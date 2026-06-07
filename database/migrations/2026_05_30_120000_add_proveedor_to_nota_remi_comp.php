<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('nota_remi_comp') && !Schema::hasColumn('nota_remi_comp', 'proveedor_id')) {
            Schema::table('nota_remi_comp', function (Blueprint $table) {
                $table->unsignedBigInteger('proveedor_id')->nullable()->after('sucursal_id');
                $table->foreign('proveedor_id')->references('id')->on('proveedores');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('nota_remi_comp') && Schema::hasColumn('nota_remi_comp', 'proveedor_id')) {
            Schema::table('nota_remi_comp', function (Blueprint $table) {
                $table->dropForeign(['proveedor_id']);
                $table->dropColumn('proveedor_id');
            });
        }
    }
};
