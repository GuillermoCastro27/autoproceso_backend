<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pedidos_detalles')) {
            Schema::table('pedidos_detalles', function (Blueprint $table) {
                if (!Schema::hasColumn('pedidos_detalles', 'marca_id')) {
                    $table->unsignedBigInteger('marca_id')->nullable()->after('deposito_id');
                    $table->foreign('marca_id')->references('id')->on('marca');
                }
                if (!Schema::hasColumn('pedidos_detalles', 'modelo_id')) {
                    $table->unsignedBigInteger('modelo_id')->nullable()->after('marca_id');
                    $table->foreign('modelo_id')->references('id')->on('modelo');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pedidos_detalles')) {
            Schema::table('pedidos_detalles', function (Blueprint $table) {
                if (Schema::hasColumn('pedidos_detalles', 'modelo_id')) {
                    $table->dropForeign(['modelo_id']);
                    $table->dropColumn('modelo_id');
                }
                if (Schema::hasColumn('pedidos_detalles', 'marca_id')) {
                    $table->dropForeign(['marca_id']);
                    $table->dropColumn('marca_id');
                }
            });
        }
    }
};
