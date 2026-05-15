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
            $table->unsignedBigInteger('deposito_id')->nullable()->after('cantidad_stock');
            $table->foreign('deposito_id')->references('id')->on('deposito')->nullOnDelete();
        });

        Schema::table('notas_vent_det', function (Blueprint $table) {
            $table->unsignedBigInteger('deposito_id')->nullable()->after('tipo_impuesto_id');
            $table->foreign('deposito_id')->references('id')->on('deposito')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pedidos_ventas_det', function (Blueprint $table) {
            $table->dropForeign(['deposito_id']);
            $table->dropColumn('deposito_id');
        });

        Schema::table('notas_vent_det', function (Blueprint $table) {
            $table->dropForeign(['deposito_id']);
            $table->dropColumn('deposito_id');
        });
    }
};
