<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos_ventas_det', function (Blueprint $table) {
            if (!Schema::hasColumn('pedidos_ventas_det', 'marca_id')) {
                $table->unsignedBigInteger('marca_id')->nullable()->after('deposito_id');
                $table->foreign('marca_id')->references('id')->on('marca')->onDelete('set null');
            }
            if (!Schema::hasColumn('pedidos_ventas_det', 'modelo_id')) {
                $table->unsignedBigInteger('modelo_id')->nullable()->after('marca_id');
                $table->foreign('modelo_id')->references('id')->on('modelo')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pedidos_ventas_det', function (Blueprint $table) {
            if (Schema::hasColumn('pedidos_ventas_det', 'modelo_id')) {
                $table->dropForeign(['modelo_id']);
                $table->dropColumn('modelo_id');
            }
            if (Schema::hasColumn('pedidos_ventas_det', 'marca_id')) {
                $table->dropForeign(['marca_id']);
                $table->dropColumn('marca_id');
            }
        });
    }
};
