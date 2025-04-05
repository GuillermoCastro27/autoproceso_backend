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
            // Agregar las nuevas columnas
            $table->string('prov_razon_social', 255)->nullable()->after('proveedor_id');
            $table->string('prov_ruc', 20)->nullable()->after('prov_razon_social');
            $table->string('tip_imp_nom', 100)->nullable()->after('tipo_impuesto_id');
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
