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
        Schema::table('nota_remi_com_det', function (Blueprint $table) {
            if (!Schema::hasColumn('nota_remi_com_det', 'deposito_id')) $table->unsignedBigInteger('deposito_id')->nullable()->after('item_id');
        });

        Schema::table('notas_comp_det', function (Blueprint $table) {
            if (!Schema::hasColumn('notas_comp_det', 'deposito_id')) $table->unsignedBigInteger('deposito_id')->nullable()->after('item_id');
        });

        Schema::table('ajuste_det', function (Blueprint $table) {
            if (!Schema::hasColumn('ajuste_det', 'deposito_id')) $table->unsignedBigInteger('deposito_id')->nullable()->after('item_id');
        });
    }

    public function down(): void
    {
        Schema::table('nota_remi_com_det', function (Blueprint $table) {
            $table->dropColumn('deposito_id');
        });

        Schema::table('notas_comp_det', function (Blueprint $table) {
            $table->dropColumn('deposito_id');
        });

        Schema::table('ajuste_det', function (Blueprint $table) {
            $table->dropColumn('deposito_id');
        });
    }
};
