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
        Schema::table('pedidos_ventas_det', function (Blueprint $table) {
            if (!Schema::hasColumn('pedidos_ventas_det', 'deposito_id')) $table->unsignedBigInteger('deposito_id')->nullable()->after('cantidad_stock');
        });

        Schema::table('notas_vent_det', function (Blueprint $table) {
            if (!Schema::hasColumn('notas_vent_det', 'deposito_id')) $table->unsignedBigInteger('deposito_id')->nullable()->after('tipo_impuesto_id');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos_ventas_det', function (Blueprint $table) {
            if (Schema::hasColumn('pedidos_ventas_det', 'deposito_id')) $table->dropColumn('deposito_id');
        });

        Schema::table('notas_vent_det', function (Blueprint $table) {
            if (Schema::hasColumn('notas_vent_det', 'deposito_id')) $table->dropColumn('deposito_id');
        });
    }
};
