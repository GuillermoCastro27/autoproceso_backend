<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orden_compra_cab') && Schema::hasColumn('orden_compra_cab', 'presupuesto_id')) {
            DB::statement('ALTER TABLE orden_compra_cab ALTER COLUMN presupuesto_id DROP NOT NULL');
        }

        if (Schema::hasTable('orden_compra_cab') && !Schema::hasColumn('orden_compra_cab', 'pedido_id')) {
            Schema::table('orden_compra_cab', function (Blueprint $t) {
                $t->unsignedBigInteger('pedido_id')->nullable()->after('presupuesto_id');
                $t->foreign('pedido_id')->references('id')->on('pedidos')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orden_compra_cab') && Schema::hasColumn('orden_compra_cab', 'pedido_id')) {
            Schema::table('orden_compra_cab', function (Blueprint $t) {
                $t->dropForeign(['pedido_id']);
                $t->dropColumn('pedido_id');
            });
        }
    }
};
