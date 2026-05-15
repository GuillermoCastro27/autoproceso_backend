<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos_detalles', function (Blueprint $table) {
            $table->unsignedBigInteger('deposito_id')->nullable()->after('item_id');
        });

        Schema::table('solicitudes_det', function (Blueprint $table) {
            $table->unsignedBigInteger('deposito_id')->nullable()->after('item_id');
        });

        Schema::table('orden_compra_det', function (Blueprint $table) {
            $table->unsignedBigInteger('deposito_id')->nullable()->after('item_id');
        });

        // presupuestos_detalles: drop composite PK, add auto-increment id, add deposito_id
        DB::statement('ALTER TABLE presupuestos_detalles DROP CONSTRAINT presupuestos_detalles_pkey');
        DB::statement('ALTER TABLE presupuestos_detalles ADD COLUMN id BIGSERIAL PRIMARY KEY');
        Schema::table('presupuestos_detalles', function (Blueprint $table) {
            $table->unsignedBigInteger('deposito_id')->nullable()->after('item_id');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos_detalles', function (Blueprint $table) {
            $table->dropForeign(['deposito_id']);
            $table->dropColumn('deposito_id');
        });

        Schema::table('solicitudes_det', function (Blueprint $table) {
            $table->dropForeign(['deposito_id']);
            $table->dropColumn('deposito_id');
        });

        Schema::table('orden_compra_det', function (Blueprint $table) {
            $table->dropForeign(['deposito_id']);
            $table->dropColumn('deposito_id');
        });

        Schema::table('presupuestos_detalles', function (Blueprint $table) {
            $table->dropForeign(['deposito_id']);
            $table->dropColumn('deposito_id');
            $table->dropColumn('id');
        });
        DB::statement('ALTER TABLE presupuestos_detalles ADD PRIMARY KEY (presupuesto_id, item_id)');
    }
};
