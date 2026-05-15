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
            $table->unsignedBigInteger('deposito_id')->nullable()->after('item_id');
            $table->foreign('deposito_id')->references('id')->on('deposito')->nullOnDelete();
        });

        Schema::table('notas_comp_det', function (Blueprint $table) {
            $table->unsignedBigInteger('deposito_id')->nullable()->after('item_id');
            $table->foreign('deposito_id')->references('id')->on('deposito')->nullOnDelete();
        });

        Schema::table('ajuste_det', function (Blueprint $table) {
            $table->unsignedBigInteger('deposito_id')->nullable()->after('item_id');
            $table->foreign('deposito_id')->references('id')->on('deposito')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('nota_remi_com_det', function (Blueprint $table) {
            $table->dropForeign(['deposito_id']);
            $table->dropColumn('deposito_id');
        });

        Schema::table('notas_comp_det', function (Blueprint $table) {
            $table->dropForeign(['deposito_id']);
            $table->dropColumn('deposito_id');
        });

        Schema::table('ajuste_det', function (Blueprint $table) {
            $table->dropForeign(['deposito_id']);
            $table->dropColumn('deposito_id');
        });
    }
};
