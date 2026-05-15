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
        Schema::table('compra_det', function (Blueprint $table) {
            if (!Schema::hasColumn('compra_det', 'deposito_id')) $table->unsignedBigInteger('deposito_id')->nullable()->after('compra_cab_id');
        });

        Schema::table('ventas_det', function (Blueprint $table) {
            if (!Schema::hasColumn('ventas_det', 'deposito_id')) $table->unsignedBigInteger('deposito_id')->nullable()->after('ventas_cab_id');
        });
    }

    public function down(): void
    {
        Schema::table('compra_det', function (Blueprint $table) {
            if (Schema::hasColumn('compra_det', 'deposito_id')) $table->dropColumn('deposito_id');
        });

        Schema::table('ventas_det', function (Blueprint $table) {
            if (Schema::hasColumn('ventas_det', 'deposito_id')) $table->dropColumn('deposito_id');
        });
    }
};
