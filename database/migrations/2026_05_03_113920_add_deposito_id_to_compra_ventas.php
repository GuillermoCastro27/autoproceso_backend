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
            $table->unsignedBigInteger('deposito_id')->nullable()->after('compra_cab_id');
            $table->foreign('deposito_id')->references('id')->on('deposito')->onDelete('set null')->onUpdate('cascade');
        });

        Schema::table('ventas_det', function (Blueprint $table) {
            $table->unsignedBigInteger('deposito_id')->nullable()->after('ventas_cab_id');
            $table->foreign('deposito_id')->references('id')->on('deposito')->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('compra_det', function (Blueprint $table) {
            $table->dropForeign(['deposito_id']);
            $table->dropColumn('deposito_id');
        });

        Schema::table('ventas_det', function (Blueprint $table) {
            $table->dropForeign(['deposito_id']);
            $table->dropColumn('deposito_id');
        });
    }
};
