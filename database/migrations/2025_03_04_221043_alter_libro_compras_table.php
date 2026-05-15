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
        Schema::table('libro_compras', function (Blueprint $table) {
            if (!Schema::hasColumn('libro_compras', 'prov_razon_social')) $table->string('prov_razon_social', 255)->nullable()->after('proveedor_id');
            if (!Schema::hasColumn('libro_compras', 'prov_ruc')) $table->string('prov_ruc', 20)->nullable()->after('prov_razon_social');
            if (!Schema::hasColumn('libro_compras', 'tip_imp_nom')) $table->string('tip_imp_nom', 100)->nullable()->after('tipo_impuesto_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
